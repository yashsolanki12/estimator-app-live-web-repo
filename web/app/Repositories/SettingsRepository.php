<?php 
namespace App\Repositories;

use Illuminate\Http\Response;
use App\Models\TimerConfigurationDetails;
use App\Models\TimerDisableDates;
use App\Models\TimerDispatchDisableDates;
use App\Models\Translations;
use App\Models\Timezones;
use App\Models\User;
use App\Models\StoreViewLogs;
use App\Models\SupportFormDetails;
use App\Models\RecurringApplicationPlans;
use App\Models\ShopifySession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\EstimatorPlan;
use App\Services\Api;

class SettingsRepository {

    private function getShopifyAccessToken($user)
    {
        if (!$user || !$user->name) {
            return null;
        }
        
        $session = ShopifySession::where('shop', $user->name)
            ->where('is_online', 0)
            ->whereNotNull('access_token')
            ->orderBy('id', 'desc')
            ->first();

        if ($session && $session->access_token) {
            return $session->access_token;
        }

        $session = ShopifySession::where('shop', $user->name)
            ->where('is_online', 1)
            ->whereNotNull('access_token')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($session && $session->access_token) {
            return $session->access_token;
        }

        return null;
    }

    /**
     * Get the current theme app embed status for this shop.
     *
     * This inspects the published theme's settings_data.json and looks for any
     * app embed records that belong to this app. If the record exists and is not
     * disabled, the app embed is considered enabled.
     */
    public function getAppEmbedStatus($userStoreId)
    {
        try {
            $user = User::where('id', $userStoreId)->first();
            if (!$user || !$user->name) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => null,
                    'error' => 'Shop not found.',
                ];
            }

            $token = $this->getShopifyAccessToken($user);
            if (!$token) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                    'error' => 'No Shopify access token was found.',
                ];
            }

            $apiVersion = config('services.shopify.api_version', '2024-01');
            $themeUrl = "https://{$user->name}/admin/api/{$apiVersion}/themes.json?role=main";
            $headers = [
                'Content-Type: application/json',
                "X-Shopify-Access-Token: {$token}",
            ];

            $themesResponse = Api::callCURL($themeUrl, $headers, [], 'GET');
            if (($themesResponse['error_number'] ?? 0) || !($themesResponse['response'] ?? null)) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                    'error' => 'Unable to read Shopify themes.',
                ];
            }

            $themes = json_decode($themesResponse['response'], true);
            $theme = null;

            if (!empty($themes['themes'])) {
                foreach ($themes['themes'] as $candidate) {
                    if (($candidate['role'] ?? null) === 'main' || ($candidate['published'] ?? false)) {
                        $theme = $candidate;
                        break;
                    }
                }
            }

            if (!$theme || !isset($theme['id'])) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                    'error' => 'No published theme was found.',
                ];
            }

            $assetUrl = "https://{$user->name}/admin/api/{$apiVersion}/themes/{$theme['id']}/assets.json?asset[key]=config/settings_data.json";
            $assetResponse = Api::callCURL($assetUrl, $headers, [], 'GET');
            if (($assetResponse['error_number'] ?? 0) || !($assetResponse['response'] ?? null)) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                    'error' => 'Unable to read theme settings.',
                ];
            }

            $assetData = json_decode($assetResponse['response'], true);
            $settingsJson = $assetData['asset']['value'] ?? null;
            if (!$settingsJson) {
                return [
                    'status' => 'unavailable',
                    'enabled' => false,
                    'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                    'error' => 'Theme settings file is empty.',
                ];
            }

            $settingsData = json_decode($settingsJson, true);
            $status = $this->detectAppEmbedState($settingsData);

            return [
                'status' => $status,
                'enabled' => $status === 'enabled',
                'theme_editor_url' => 'https://' . $user->name . '/admin/themes/current/editor?context=apps',
                'theme_id' => $theme['id'],
            ];
        } catch (\Throwable $e) {
            Log::error('[SettingsRepository::getAppEmbedStatus] ' . $e->getMessage());
            return [
                'status' => 'unavailable',
                'enabled' => false,
                'theme_editor_url' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function detectAppEmbedState($value)
    {
        if (!is_array($value)) {
            return 'disabled';
        }

        $foundMatchingEmbed = false;

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                if (isset($item['type']) && is_string($item['type'])) {
                    $type = $item['type'];
                    $isAppEmbed = str_contains($type, '/blocks/app-embed/') || str_contains($type, '/blocks/app_embed/');

                    if ($isAppEmbed) {
                        $foundMatchingEmbed = true;
                        if (!($item['disabled'] ?? false)) {
                            return 'enabled';
                        }
                    }
                }

                $nestedState = $this->detectAppEmbedState($item);
                if ($nestedState === 'enabled') {
                    return 'enabled';
                }
                if ($nestedState === 'disabled') {
                    $foundMatchingEmbed = true;
                }
            }
        }

        return $foundMatchingEmbed ? 'disabled' : 'disabled';
    }

    /**
     * Create Default Settings
     *
     */
    public function createDefaultSettings ($userStoreId) {
        $timerDetails = TimerConfigurationDetails::where('user_store_id',$userStoreId)->first();
        if (!$timerDetails) {
            $tcDetails = new TimerConfigurationDetails();
            $tcDetails->user_store_id = Auth::user()->id;
            $tcDetails->timer_visibility = '1,2,3,4,5,6,7';
            $tcDetails->timezone = "+00.00";
            $tcDetails->enable_dispatch_days = 1;
            $tcDetails->dispatch_days = '1,2,3,4,5,6,7';
            $tcDetails->active_delivery_days = '1,2,3,4,5,6,7';
            $tcDetails->enable_tbtimer = 1;
            $tcDetails->enable_second = 1;
            $tcDetails->show_timer_past_cutoff = 1;
            $tcDetails->text_position = 0;
            $tcDetails->cutoff_hour = 0;
            $tcDetails->text_align = 'left';
            $tcDetails->custom_message = 'Order within the next [countdown] for delivery by [deliverydate].';
            $tcDetails->save();
        }
    }

    /**
     * Change Status.
     *
     */
    public function changeStatus($requestData) {
        try 
        {
            $tcDetails = TimerConfigurationDetails::where('user_store_id',$requestData['user_store_id'])->first();
            if (!$tcDetails) {
                $tcDetails = new TimerConfigurationDetails(); 
            }
            $tcDetails->user_store_id = $requestData['user_store_id'];
            $tcDetails->status = $requestData['status'];
            if ($tcDetails->save()) {
                return true;
            }
            return false;
        } catch (\Throwable $e) {
            Log::error($e);
            return false;
        }
    }

    /**
     * Save Timer Configuration.
     *
     */
    public function saveTimerConfiguration($requestData) {
        try 
        {
            $tcDetails = TimerConfigurationDetails::where('user_store_id',$requestData['user_store_id'])->first();
            if (!$tcDetails) {
                $tcDetails = new TimerConfigurationDetails(); 
            }
            $tcDetails->timezone = $tcDetails->cutoff_hour = $tcDetails->cutoff_minutes = $tcDetails->countdown_format = $tcDetails->delivery_lead_time = $tcDetails->delivery_range_days = $tcDetails->delivery_handling_time = $tcDetails->delivery_date_format = $tcDetails->custom_message = $tcDetails->text_font_size = $tcDetails->text_align = $tcDetails->text_background_color = $tcDetails->text_font_color = $tcDetails->text_countdown_color = $tcDetails->text_deliverydate_color = $tcDetails->text_border_size = $tcDetails->text_border_color = $tcDetails->text_border_radius = $tcDetails->text_margin_top = $tcDetails->text_margin_bottom = $tcDetails->text_margin_left = $tcDetails->text_margin_right = $tcDetails->visual_icon_color = $tcDetails->visual_accent_color = $tcDetails->visual_font_color = $tcDetails->visual_text_color = $tcDetails->visual_background_color =  $tcDetails->visual_margin_top = $tcDetails->visual_margin_bottom = $tcDetails->visual_margin_left = $tcDetails->visual_margin_right = $tcDetails->hide_on_collection = $tcDetails->product_tags = Null;

            $tcDetails->user_store_id = $requestData['user_store_id'];
            if (isset($requestData['timer_visibility']) && $requestData['timer_visibility']) {
                $timerVisibility = $requestData['timer_visibility'];
                if (is_array($timerVisibility)) {
                    $tcDetails->timer_visibility = implode(',', $timerVisibility);
                } else {
                    $tcDetails->timer_visibility = $timerVisibility;
                }
            } else {
                $tcDetails->timer_visibility = Null;
            }

            if (isset($requestData['timezone'])) {
                $tcDetails->timezone = $requestData['timezone'];
            }

            if (isset($requestData['cutoff_hour'])) {
                $tcDetails->cutoff_hour = $requestData['cutoff_hour'];
            }

            if (isset($requestData['cutoff_minutes'])) {
                $tcDetails->cutoff_minutes = $requestData['cutoff_minutes'];
            } else {
                $tcDetails->cutoff_minutes = 0;
            }

            if (isset($requestData['countdown_format'])) {
                $tcDetails->countdown_format = $requestData['countdown_format'];
            }

            if (isset($requestData['enable_second']) && $requestData['enable_second']) {
                $tcDetails->enable_second = $requestData['enable_second'];
            } else {
                $tcDetails->enable_second = 0;
            }

            if (isset($requestData['show_timer_past_cutoff']) && $requestData['show_timer_past_cutoff']) {
                $tcDetails->show_timer_past_cutoff = $requestData['show_timer_past_cutoff'];
            } else {
                $tcDetails->show_timer_past_cutoff = 0;
            }

            if (isset($requestData['hide_comma_separator']) && $requestData['hide_comma_separator']) {
                $tcDetails->hide_comma_separator = $requestData['hide_comma_separator'];
            } else {
                $tcDetails->hide_comma_separator = 0;
            }

            if (isset($requestData['delivery_lead_time'])) {
                $tcDetails->delivery_lead_time = $requestData['delivery_lead_time'];
            }

            if (isset($requestData['enable_delivery_add_range']) && $requestData['enable_delivery_add_range']) {
                $tcDetails->enable_delivery_add_range = $requestData['enable_delivery_add_range'];
            } else {
                $tcDetails->enable_delivery_add_range = 0;
            }

            if (isset($requestData['delivery_range_days'])) {
                $tcDetails->delivery_range_days = $requestData['delivery_range_days'];
            }

            if (isset($requestData['delivery_handling_time'])) {
                $tcDetails->delivery_handling_time = $requestData['delivery_handling_time'];
            }

            if (isset($requestData['enable_dispatch_days']) && $requestData['enable_dispatch_days']) {
                $tcDetails->enable_dispatch_days = $requestData['enable_dispatch_days'];
            } else {
                $tcDetails->enable_dispatch_days = 0;
            }

            if (isset($requestData['dispatch_days']) && $requestData['dispatch_days']) {
                $dispatchDays = $requestData['dispatch_days'];
                if (is_array($dispatchDays)) {
                    $tcDetails->dispatch_days = implode(',', $dispatchDays);
                } else {
                    $tcDetails->dispatch_days = $dispatchDays;
                }
            } else {
                $tcDetails->dispatch_days = Null;
            }

            if (isset($requestData['active_delivery_days']) && $requestData['active_delivery_days']) {
                $activeDeliveryDays = $requestData['active_delivery_days'];
                if (is_array($activeDeliveryDays)) {
                    $tcDetails->active_delivery_days = implode(',', $activeDeliveryDays);
                } else {
                    $tcDetails->active_delivery_days = $activeDeliveryDays;
                }
            } else {
                $tcDetails->active_delivery_days = Null;
            }

            if (isset($requestData['delivery_date_format'])) {
                $tcDetails->delivery_date_format = $requestData['delivery_date_format'];
            }

            if (isset($requestData['custom_message']) && $requestData['custom_message']) {
                $tcDetails->custom_message = $requestData['custom_message'];
            } else {
                $tcDetails->custom_message = 'Order within the next [countdown] for delivery by [deliverydate].';
            }
            if (isset($requestData['text_position']) && $requestData['text_position']) {
                $tcDetails->text_position = $requestData['text_position'];
            } else {
                $tcDetails->text_position = 0;
            }
            if (isset($requestData['text_font_size'])) {
                $tcDetails->text_font_size = $requestData['text_font_size'];
            }
            if (isset($requestData['text_align'])) {
                $tcDetails->text_align = $requestData['text_align'];
            }
            if (isset($requestData['text_background_color'])) {
                $tcDetails->text_background_color = $requestData['text_background_color'];
            }
            if (isset($requestData['text_font_color'])) {
                $tcDetails->text_font_color = $requestData['text_font_color'];
            }
            if (isset($requestData['text_countdown_color'])) {
                $tcDetails->text_countdown_color = $requestData['text_countdown_color'];
            }
            if (isset($requestData['text_deliverydate_color'])) {
                $tcDetails->text_deliverydate_color = $requestData['text_deliverydate_color'];
            }
            if (isset($requestData['text_border_size'])) {
                $tcDetails->text_border_size = $requestData['text_border_size'];
            }
            if (isset($requestData['text_border_color'])) {
                $tcDetails->text_border_color = $requestData['text_border_color'];
            }
            if (isset($requestData['text_border_radius'])) {
                $tcDetails->text_border_radius = $requestData['text_border_radius'];
            }
            if (isset($requestData['text_border_style'])) {
                $tcDetails->text_border_style = $requestData['text_border_style'];
            } else {
                $tcDetails->text_border_style = Null;
            }
            if (isset($requestData['text_margin_top'])) {
                $tcDetails->text_margin_top = $requestData['text_margin_top'];
            }
            if (isset($requestData['text_margin_bottom'])) {
                $tcDetails->text_margin_bottom = $requestData['text_margin_bottom'];
            }
            if (isset($requestData['text_margin_left'])) {
                $tcDetails->text_margin_left = $requestData['text_margin_left'];
            }
            if (isset($requestData['text_margin_right'])) {
                $tcDetails->text_margin_right = $requestData['text_margin_right'];
            }

            if (isset($requestData['visual_icon_color'])) {
                $tcDetails->visual_icon_color = $requestData['visual_icon_color'];
            }
            if (isset($requestData['visual_accent_color'])) {
                $tcDetails->visual_accent_color = $requestData['visual_accent_color'];
            }
            if (isset($requestData['visual_font_color'])) {
                $tcDetails->visual_font_color = $requestData['visual_font_color'];
            }
            if (isset($requestData['visual_text_color'])) {
                $tcDetails->visual_text_color = $requestData['visual_text_color'];
            }
            if (isset($requestData['visual_background_color'])) {
                $tcDetails->visual_background_color = $requestData['visual_background_color'];
            }
            if (isset($requestData['visual_margin_top'])) {
                $tcDetails->visual_margin_top = $requestData['visual_margin_top'];
            }
            if (isset($requestData['visual_margin_bottom'])) {
                $tcDetails->visual_margin_bottom = $requestData['visual_margin_bottom'];
            }
            if (isset($requestData['visual_margin_left'])) {
                $tcDetails->visual_margin_left = $requestData['visual_margin_left'];
            }
            if (isset($requestData['visual_margin_right'])) {
                $tcDetails->visual_margin_right = $requestData['visual_margin_right'];
            }

            if (isset($requestData['enable_tbtimer']) && $requestData['enable_tbtimer']) {
                $tcDetails->enable_tbtimer = $requestData['enable_tbtimer'];
            } else {
                $tcDetails->enable_tbtimer = 0;
            }

            if (isset($requestData['enable_vtimer']) && $requestData['enable_vtimer']) {
                $tcDetails->enable_vtimer = $requestData['enable_vtimer'];
            } else {
                $tcDetails->enable_vtimer = 0;
            }
           if (isset($requestData['hide_on_collection']) && $requestData['hide_on_collection']) {
                $hideOnCollection = $requestData['hide_on_collection'];
                if (is_array($hideOnCollection)) {
                    $tcDetails->hide_on_collection = implode(',', $hideOnCollection);
                } else {
                    $tcDetails->hide_on_collection = $hideOnCollection;
                }
            }

            if (isset($requestData['product_tags'])) {
                $tcDetails->product_tags = $requestData['product_tags'];
            }

            if ($tcDetails->save()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Add Disabled Date
     *
     * @return boolean
     */
    public function addDisabledDate($requestData) {
        try 
        {
            $tdDates = TimerDisableDates::where('user_store_id',$requestData['user_store_id'])->whereDate('date',$requestData['date'])->first();
            if (!$tdDates) {
                $tdDates = new TimerDisableDates();
                $tdDates->user_store_id = $requestData['user_store_id'];
                if (isset($requestData['date']) && $requestData['date']) {
                    $tdDates->date = $requestData['date'];
                }
                if ($tdDates->save()) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Delete Disabled Date
     *
     * @return boolean
     */
    public function deleteDisabledDate($requestData) {
        try 
        {
            return TimerDisableDates::where('user_store_id',$requestData['user_store_id'])->where('id',$requestData['id'])->delete();
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Add Dispatch Disabled Date
     *
     * @return boolean
     */
    public function addDispatchDisabledDate($requestData) {
        try 
        {
            $tdDates = TimerDispatchDisableDates::where('user_store_id',$requestData['user_store_id'])->whereDate('date',$requestData['date'])->first();
            if (!$tdDates) {
                $tdDates = new TimerDispatchDisableDates();
                $tdDates->user_store_id = $requestData['user_store_id'];
                if (isset($requestData['date']) && $requestData['date']) {
                    $tdDates->date = $requestData['date'];
                }
                if ($tdDates->save()) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Delete Dispatch Disabled Date
     *
     * @return boolean
     */
    public function deleteDispatchDisabledDate($requestData) {
        try 
        {
            return TimerDispatchDisableDates::where('user_store_id',$requestData['user_store_id'])->where('id',$requestData['id'])->delete();
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Get All Settings
     *
     * @return mix
     */
    /**
     * Get All Settings
     *
     * @return mix
     */
    public function getAllSettings($requestData) {
        try
        {
            Log::info("[getAllSettings] requestData=" . json_encode($requestData));

            $shop = $requestData['shop'] ?? 'MISSING';

            if (!isset($requestData['shop'])) {
                Log::warning("[getAllSettings] FAIL: missing shop param");
                return false;
            }

            $storeDetails = User::where('name',$requestData['shop'])->first();
            if (!isset($storeDetails->id)) {
                Log::warning("[getAllSettings] FAIL: store not found for shop=" . $requestData['shop']);
                return false;
            }
            Log::info("[getAllSettings] store id={$storeDetails->id}");

            $tcDetails = TimerConfigurationDetails::where('user_store_id',$storeDetails->id)->where('status',1)->first();
            if (!$tcDetails) {
                Log::warning("[getAllSettings] FAIL: no TimerConfigurationDetails with status=1");
                return false;
            }
            Log::info("[getAllSettings] timer found, enable_tbtimer=" . ($tcDetails->enable_tbtimer ?? 'null') . " enable_vtimer=" . ($tcDetails->enable_vtimer ?? 'null'));

            $plan = RecurringApplicationPlans::where('user_store_id',$storeDetails->id)->where('status','active')->first();
            // PLAN CHECK COMMENTED OUT - enable later when billing is integrated
            if (!$plan) {
                Log::warning("[getAllSettings] FAIL: no active subscription plan");
                return false;
            }

            $planType = config('api.plan_type');
            // totalViewCountPerStore returns current month's count only
            $totalViewData = $this->totalViewCountPerStore($storeDetails->id);
            $totalView = 0;
            if (is_array($totalViewData) && isset($totalViewData[0]['total']) && $totalViewData[0]['total']) {
                $totalView = (int) $totalViewData[0]['total'];
            }
            
            // Get limit from recurring plan, or fallback to estimator plan defaults if missing
            $planLimit = isset($plan->limit) ? (int) $plan->limit : 0;
            if ($planLimit === 0 && isset($plan->name)) {
                $estimatorPlan = \App\Models\EstimatorPlan::where('name', $plan->name)->first();
                if ($estimatorPlan && isset($estimatorPlan->limit)) {
                    $planLimit = (int) $estimatorPlan->limit;
                }
            }
            
            Log::info("[getAllSettings] plan='{$plan->name}' limit={$planLimit} views_this_month={$totalView}");
            if ($planLimit > 0 && $totalView >= $planLimit) {
                Log::warning("[getAllSettings] BLOCKED: plan limit exceeded. limit={$planLimit}, totalView={$totalView}");
                return ['status' => false, 'reason' => 'limit_exceeded', 'message' => 'Your ' . $plan->name . ' plan limit reached out, please upgrade your plan.'];
            }
            if ((isset($tcDetails->timer_visibility) && !in_array(date('N'), explode(',', $tcDetails->timer_visibility))) || !$tcDetails->timer_visibility) {
                Log::warning("[getAllSettings] BLOCKED: today's day not in timer_visibility. visibility=" . ($tcDetails->timer_visibility ?? 'null') . " today=" . date('N'));
            }

            $tdDates = TimerDisableDates::where('user_store_id',$storeDetails->id)->whereDate('date',date('Y-m-d'))->first();
            if ($tdDates) {
                Log::warning("[getAllSettings] BLOCKED: today is in TimerDisableDates");
            }

            if (isset($tcDetails->enable_dispatch_days) && $tcDetails->enable_dispatch_days == 1) {
                if (!isset($tcDetails->dispatch_days) || (isset($tcDetails->dispatch_days) && !$tcDetails->dispatch_days)) {
                    Log::warning("[getAllSettings] BLOCKED: dispatch days enabled but no dispatch_days set");
                }
            }

            if (!isset($tcDetails->active_delivery_days) || (isset($tcDetails->active_delivery_days) && !$tcDetails->active_delivery_days)) {
                Log::warning("[getAllSettings] BLOCKED: active_delivery_days is empty");
            }

            if (isset($tcDetails->hide_on_collection) && $tcDetails->hide_on_collection) {
                if (isset($requestData['product_id'])) {
                    $cId = explode(',', $tcDetails->hide_on_collection);
                    $pIds = [];
                    foreach ($cId as $cVal) {
                        $productData = $this->getShopifyProductOfCollection($cVal, $storeDetails->id);
                        foreach ($productData as $pVal) {
                            if ($pVal['id']) {
                                $pIds[] = $pVal['id'];
                            }
                        }
                    }
                    if ($pIds && in_array($requestData['product_id'], $pIds)) {
                        Log::warning("[getAllSettings] BLOCKED: product in hide_on_collection. product_id=" . $requestData['product_id']);
                         return false;
                    }
                }
            }
            if (isset($tcDetails->product_tags) && $tcDetails->product_tags) {
                if (isset($requestData['product_id'])) {
                    $tags = [];
                    $tagData = json_decode($tcDetails->product_tags,true);
                    foreach ($tagData as $pVal) {
                        $tags[] = $pVal['value'];
                    }
                    if ($tags) {
                        $productData = $this->getShopifyProduct($requestData['product_id'], $storeDetails->id);
                        if (isset($productData['tags']) && $productData['tags']) {
                            $pTags = explode(',', $productData['tags']);
                            foreach ($pTags as $value) {
                                $productTags[] = trim($value);
                            }
                            $result=array_intersect($tags,$productTags);
                            if ($result) {
                                Log::warning("[getAllSettings] BLOCKED: product matches excluded tags. tags=" . implode(',', $result));
                                 return false;
                            }
                        }
                    }
                }
            }

            $data = $this->settingCalculation($requestData, $tcDetails);
            return $data;
        } catch (\Exception $e) {
            Log::error("[getAllSettings] " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Settings Calculation
     *
     * @return mix
     */
    public function settingCalculation($requestData, $tcDetails) {
        try
        {
            $data = $tcDetails->toArray();

            $dispatchDays = array();
            if (isset($requestData['enable_dispatch_days']) && $requestData['enable_dispatch_days'] == 1 && isset($requestData['dispatch_days_val']) && $requestData['dispatch_days_val']) {
                $dispatchDays = $requestData['dispatch_days_val'];
            } else if (isset($tcDetails->enable_dispatch_days) && $tcDetails->enable_dispatch_days == 1 && isset($tcDetails->dispatch_days) && $tcDetails->dispatch_days) {
                $dispatchDays = explode(',', $tcDetails->dispatch_days);
            }
            $timezone  = "+00.00"; // UTC
            if (isset($requestData['timezone']) && $requestData['timezone']) {
                $timezone = $requestData['timezone'];
            } else if (isset($tcDetails->timezone) && $tcDetails->timezone) {
                $timezone  = $tcDetails->timezone;
            }
            $tFormat = "+00:00";
            $tData = Timezones::where('value',$timezone)->first();
            if (isset($tData->gmt_value) && $tData->gmt_value) {
                $tFormat = $tData->gmt_value;
            }
            $currentDateTime = gmdate("Y/m/j H:i:s", time() + 3600*($timezone+date("I"))); 

            $numberOfDays = 0;
            $currentDate  = date('Y-m-d',strtotime($currentDateTime));
            $cutoffHour = $cutoffMinutes = 0;
            if (isset($requestData['cutoff_hour']) && $requestData['cutoff_hour']) {
                $cutoffHour = $requestData['cutoff_hour'];
            } else if (isset($tcDetails->cutoff_hour) && $tcDetails->cutoff_hour) {
                $cutoffHour = $tcDetails->cutoff_hour;
            }

            if (isset($requestData['cutoff_minutes']) && $requestData['cutoff_minutes']) {
                $cutoffMinutes = $requestData['cutoff_minutes'];
            } else if (isset($tcDetails->cutoff_minutes) && $tcDetails->cutoff_minutes) {
                $cutoffMinutes = $tcDetails->cutoff_minutes;
            }

            $min = str_pad($cutoffMinutes, 2, "0", STR_PAD_LEFT);
            $time = $cutoffHour.':'.$min;
            $combinedDT = date('Y-m-d H:i:s', strtotime("$currentDate $time"));

            if (strtotime($currentDateTime) > strtotime($combinedDT)) {
                $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
            }
            $dateObj      = new DateTime($currentDate);
            $timeStamp    = $dateObj->getTimestamp();

            for ($i = 0; $i <= $numberOfDays; $i++) {
                $nextDay  = date('N', ($timeStamp));// get what day it is next day
                //Skip the day
                if($dispatchDays && !in_array(strtolower($nextDay), array_map('strtolower', $dispatchDays)) ) {
                    $i--;
                    $addDay   = 86400; // add 1 day to timestamp
                    $timeStamp = $timeStamp + $addDay;
                } else {
                    $tddDates = TimerDispatchDisableDates::where('user_store_id',$tcDetails->user_store_id)->whereDate('date',date('Y-m-d',($timeStamp)))->first();
                    if ($tddDates) {
                        $i--;
                        $addDay   = 86400; // add 1 day to timestamp
                        $timeStamp = $timeStamp + $addDay;
                    }
                }
            }
            $dateObj->setTimestamp($timeStamp);
            $cutoffDate = $dateObj->format('Y-m-d');

            $cutoffDateTime = $cutoffDate.' '.$cutoffHour.':'.$min.':00.000'.$tFormat;

            $deliveryDate = $cutoffDate;
            if (isset($requestData['del_l_time'])) {
                if ($requestData['del_l_time'] > 0) {
                    $deliveryDate = date('Y-m-d', strtotime($cutoffDate . ' +'.$requestData['del_l_time'].' day'));
                }
            } else if (isset($tcDetails->delivery_lead_time) && $tcDetails->delivery_lead_time) {
                $deliveryDate = date('Y-m-d', strtotime($cutoffDate . ' +'.$tcDetails->delivery_lead_time.' day'));
            }
            
            $activeDelDays = array();
            if (isset($requestData['active_delivery_days_val']) && $requestData['active_delivery_days_val']) {
                $activeDelDays = $requestData['active_delivery_days_val'];
            } else if (isset($tcDetails->active_delivery_days) && $tcDetails->active_delivery_days) {
                $activeDelDays = explode(',', $tcDetails->active_delivery_days);
            }
            $del_dateObj      = new DateTime($deliveryDate);
            $del_timeStamp    = $del_dateObj->getTimestamp();
            if ($activeDelDays) {
                for ($j = 0; $j <= 0; $j++) {
                    $nextDay  = date('N', ($del_timeStamp));// get what day it is next day
                    //Skip the day
                    if(!in_array(strtolower($nextDay), array_map('strtolower', $activeDelDays)) ) {
                        $j--;
                        $addDay   = 86400; // add 1 day to timestamp
                        $del_timeStamp = $del_timeStamp + $addDay;
                    }
                }
            }
            $del_dateObj->setTimestamp($del_timeStamp);
            $deliveryDate = $del_dateObj->format('Y-m-d');

            $dDateFormat = 'd/m';
            if (isset($requestData['format']) && $requestData['format']) {
                $dDateFormat =  $requestData['format'];
            } else if (isset($tcDetails->delivery_date_format) && $tcDetails->delivery_date_format) {
                $dDateFormat =  $tcDetails->delivery_date_format;
            }

            $data['text_delivery_date'] = date($dDateFormat,strtotime($deliveryDate));
            $data['visual_delivery_date_text'] = date('d M.',strtotime($deliveryDate));

            if (isset($requestData['enable_delivery_add_range']) && isset($requestData['del_add_range']) ) {
                if ($requestData['enable_delivery_add_range'] ==1 && $requestData['del_add_range'] > 0) {
                    $delRangDate = date('Y-m-d', strtotime($deliveryDate . ' +'.$requestData['del_add_range'].' day'));
                }
            } else if (isset($tcDetails->enable_delivery_add_range) && $tcDetails->enable_delivery_add_range && isset($tcDetails->delivery_range_days) && $tcDetails->delivery_range_days > 0) {
                $delRangDate = date('Y-m-d', strtotime($deliveryDate . ' +'.$tcDetails->delivery_range_days.' day'));
            }

            if (isset($delRangDate)) {
                $data['text_delivery_date'] = date($dDateFormat,strtotime($deliveryDate)).' - '.date($dDateFormat,strtotime($delRangDate));
                if (date('M',strtotime($deliveryDate)) != date('M',strtotime($delRangDate))) {
                    $data['visual_delivery_date_text'] = date('d',strtotime($deliveryDate)).' '.date('M. ',strtotime($deliveryDate)).'-'.date(' d',strtotime($delRangDate)).' '.date('M.',strtotime($delRangDate));
                } else {
                    $data['visual_delivery_date_text'] = date('d',strtotime($deliveryDate)).'-'.date('d',strtotime($delRangDate)).' '.date('M.',strtotime($delRangDate));
                }
            }
            $data['visual_order_date_text'] = date('d M.');
            $data['visual_dispatches_date_text'] = date('d M.',strtotime($cutoffDate));
            if (isset($requestData['del_h_time'])) {
                if ($requestData['del_h_time'] > 0) {
                    $dispatchDate = date('Y-m-d', strtotime($cutoffDate . ' +'.$requestData['del_h_time'].' day'));
                }
            } else if (isset($tcDetails->delivery_handling_time) && $tcDetails->delivery_handling_time > 0) {
                $dispatchDate = date('Y-m-d', strtotime($cutoffDate . ' +'.$tcDetails->delivery_handling_time.' day'));
            }

            if (isset($dispatchDate)) {
                if (date('M',strtotime($cutoffDate)) != date('M',strtotime($dispatchDate))) {
                    $data['visual_dispatches_date_text'] = date('d',strtotime($cutoffDate)).' '.date('M. ',strtotime($cutoffDate)).'-'.date(' d',strtotime($dispatchDate)).' '.date('M.',strtotime($dispatchDate));
                } else {
                    $data['visual_dispatches_date_text'] = date('d',strtotime($cutoffDate)).'-'.date('d',strtotime($dispatchDate)).' '.date('M.',strtotime($dispatchDate));
                }
            }
            $data['cutofftime'] = $cutoffDateTime;

            $translationsDetails = Translations::where('user_store_id',$tcDetails->user_store_id)->first();
            $data['text_days'] = $data['text_hours'] = $data['text_minutes'] = $data['text_seconds'] = "";
            $data['visual_estimated_arrival'] = "Estimated arrival";
            $data['visual_order_placed'] = "Order placed";
            $data['visual_order_dispatches'] = "Order dispatches";
            $data['visual_delivered'] = "Delivered!";
            if ($translationsDetails) {
                if ($translationsDetails->text_days) {
                    $data['text_days'] = $translationsDetails->text_days;
                }
                if ($translationsDetails->text_minutes) {
                    $data['text_minutes'] = $translationsDetails->text_minutes;
                }
                if ($translationsDetails->text_hours) {
                    $data['text_hours'] = $translationsDetails->text_hours;
                }
                if ($translationsDetails->text_seconds) {
                    $data['text_seconds'] = $translationsDetails->text_seconds;
                }
                if ($translationsDetails->visual_estimated_arrival) {
                    $data['visual_estimated_arrival'] = $translationsDetails->visual_estimated_arrival;
                }
                if ($translationsDetails->visual_order_placed) {
                    $data['visual_order_placed'] = $translationsDetails->visual_order_placed;
                }
                if ($translationsDetails->visual_order_dispatches) {
                    $data['visual_order_dispatches'] = $translationsDetails->visual_order_dispatches;
                }
                if ($translationsDetails->visual_delivered) {
                    $data['visual_delivered'] = $translationsDetails->visual_delivered;
                }
            }
            return $data;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Get All Settings
     *
     * @return mix
     */
    public function getAdminSettings($requestData) {
        try 
        {
            if (!isset($requestData['shop'])) {
                return false;
            }
            $storeDetails = User::where('name',$requestData['shop'])->first();
            if (!isset($storeDetails->id)) {
                return false;
            }
            
            $tcDetails = TimerConfigurationDetails::where('user_store_id',$storeDetails->id)->first();
            if (!$tcDetails) {
                return false;
            }
            $data = $this->settingCalculation($requestData, $tcDetails);            
            return $data;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Save Translations Settings
     *
     * @return mix
     */
    public function saveTranslationsSettings($requestData) {
        try 
        {
            $userStoreId = $requestData['user_store_id'] ?? Auth::id();

            if (!$userStoreId) {
                return false;
            }

            $translationsDetails = Translations::where('user_store_id',$userStoreId)->first();
            if (!$translationsDetails) {
                $translationsDetails = new Translations(); 
            }
            $translationsDetails->user_store_id = $userStoreId;
            if (isset($requestData['text_days'])) {
                $translationsDetails->text_days = $requestData['text_days'];
            } else {
                $translationsDetails->text_days = Null;
            }
            if (isset($requestData['text_hours'])) {
                $translationsDetails->text_hours = $requestData['text_hours'];
            } else {
                $translationsDetails->text_hours = Null;
            }
            if (isset($requestData['text_minutes'])) {
                $translationsDetails->text_minutes = $requestData['text_minutes'];
            } else {
                $translationsDetails->text_minutes = Null;
            }
            if (isset($requestData['text_seconds'])) {
                $translationsDetails->text_seconds = $requestData['text_seconds'];
            } else {
                $translationsDetails->text_seconds = Null;
            }
            if (isset($requestData['visual_estimated_arrival'])) {
                $translationsDetails->visual_estimated_arrival = $requestData['visual_estimated_arrival'];
            } else {
                $translationsDetails->visual_estimated_arrival = Null;
            }
            if (isset($requestData['visual_order_placed'])) {
                $translationsDetails->visual_order_placed = $requestData['visual_order_placed'];
            } else {
                $translationsDetails->visual_order_placed = Null;
            }
            if (isset($requestData['visual_order_dispatches'])) {
                $translationsDetails->visual_order_dispatches = $requestData['visual_order_dispatches'];
            } else {
                $translationsDetails->visual_order_dispatches = Null;
            }
            if (isset($requestData['visual_delivered'])) {
                $translationsDetails->visual_delivered = $requestData['visual_delivered'];
            } else {
                $translationsDetails->visual_delivered = Null;
            }
            if ($translationsDetails->save()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Get Shopify Collection
     *
     * @return mix
     */
    public function getShopifyCollection() {
        try 
        {
            $token = $end_point = "";
            $user = User::where('id',Auth::user()->id)->first();
            
            $shop = $user->name;
            
            // Get access token using existing method
            $token = $this->getShopifyAccessToken($user);
            Log::info("[getShopifyCollection] Shop: $shop, Has token: " . ($token ? 'yes' : 'no'));
            
            $data = [];
            
            // Fetch custom collections
            $api_endpoint = "/admin/api/2025-04/custom_collections.json";
            $url = "https://" . $shop . $api_endpoint;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            $request_headers = [];
            if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
            $response = curl_exec($curl);
            $error_number = curl_errno($curl);
            curl_close($curl);
            if (!$error_number && isset($response)) {
                $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
                if (isset($response[1]) && $response[1]) {
                    $responseData = json_decode($response[1],true);
                    if (isset($responseData['custom_collections']) && $responseData['custom_collections']) {
                        $data = array_merge($data, $responseData['custom_collections']);
                    }
                }
            }
            
            // Fetch smart collections
            $api_endpoint = "/admin/api/2025-04/smart_collections.json";
            $url = "https://" . $shop . $api_endpoint;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            $request_headers = [];
            if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
            $response = curl_exec($curl);
            $error_number = curl_errno($curl);
            curl_close($curl);
            if (!$error_number && isset($response)) {
                $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
                if (isset($response[1]) && $response[1]) {
                    $responseData = json_decode($response[1],true);
                    if (isset($responseData['smart_collections']) && $responseData['smart_collections']) {
                        $data = array_merge($data, $responseData['smart_collections']);
                    }
                }
            }
            
            return $data;
        } catch (Exception $e) {
            Log::error($e);
            return [];           
        }
    }

    /**
     * Get Shopify Collection
     *
     * @return mix
     */
     public function getShopifyProductOfCollection($collectionId, $storeDetailsId) {
        try 
        {
            $token = $end_point = "";
         
            $user = User::where('id',$storeDetailsId)->first();
            $shop = $user->name;
            
            // Get access token using existing method
            $token = $this->getShopifyAccessToken($user);
            
            $api_endpoint = "/admin/api/2026-04/collections/".$collectionId."/products.json";
            $url = "https://" . $shop . $api_endpoint;
            // Configure cURL
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

            // Setup headers
            $request_headers[] = "";
            if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

            // Send request to Shopify and capture any errors
            $response = curl_exec($curl);
            $error_number = curl_errno($curl);
            $error_message = curl_error($curl);

            // Close cURL to be nice
            curl_close($curl);
            $data = [];
            if ($error_number) {
                return $data;
            } else {
                $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
                if (isset($response[1]) && $response[1]) {
                    $responseData = json_decode($response[1],true);
                    if (isset($responseData['products']) && $responseData['products']) {
                        $data = $responseData['products'];
                    }
                }
                return $data;
            }
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Get Shopify Product
     *
     * @return mix
     */
     public function getShopifyProduct($productId, $userStoreId) {
        try 
        {
            $token = $end_point = "";
         
            $user = User::where('id',$userStoreId)->first();
            $shop = $user->name;
            
            // Get access token using existing method
            $token = $this->getShopifyAccessToken($user);

            $api_endpoint = "/admin/api/2026-04/products/".$productId.".json";
            $url = "https://" . $shop . $api_endpoint;
            // Configure cURL
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, TRUE);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');

            // Setup headers
            $request_headers[] = "";
            if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

            // Send request to Shopify and capture any errors
            $response = curl_exec($curl);
            $error_number = curl_errno($curl);
            $error_message = curl_error($curl);

            // Close cURL to be nice
            curl_close($curl);
            $data = [];
            if ($error_number) {
                return $data;
            } else {
                $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
                if (isset($response[1]) && $response[1]) {
                    $responseData = json_decode($response[1],true);
                    if (isset($responseData['product']) && $responseData['product']) {
                        $data = $responseData['product'];
                    }
                }
                return $data;
            }
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Save View Logs
     *
     * @return boolean
     */
    public function storeViewLogs($requestData) {
        try 
        {
            $userData = User::where('name', $requestData['shop_name'])->first();
            if (!$userData) {
                return false;
            }
            if (isset($userData->id) && $userData->id) {
                $requestData['user_store_id'] = $userData->id;
            }
            if(!isset($requestData['user_store_id'])){
                return false;
            }
            $user = User::where('id',$requestData['user_store_id'])->first();
            if(!$user){
                return false;
            }
            if(!isset($requestData['product_id'])){
                $requestData['product_id'] = null;
            }
            if(!isset($requestData['page'])){
                $requestData['page'] = null;
            }
            $saveViewLogs = new StoreViewLogs();
            $saveViewLogs->user_store_id    =  $requestData['user_store_id'];
            $saveViewLogs->product_id       =  $requestData['product_id'];
            $saveViewLogs->product_name     =  $requestData['product_name'];
            $saveViewLogs->page             =  $requestData['page'];
            if($saveViewLogs->save()){
                return $saveViewLogs;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     *  Total View Count
     *
     * @return boolean
     */
    public function totalViewCount() {
        try 
        {
            $data = StoreViewLogs::select('user_store_id', DB::raw('count(*) as total'))->groupBy('user_store_id')->get();
            if($data){
                return $data;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     *  Total View Count Per Store
     *
     * @return boolean
     */
    public function totalViewCountPerStore($id) {
        try 
        {
            if(!isset($id)){
                return false;
            }
            $user = User::where('id',$id)->first();
            if(!$user){
                return false;
            }
            $data = StoreViewLogs::select(DB::raw('count(*) as total'),DB::raw("MONTHNAME(created_at) as month_name"))->whereMonth('created_at', date('m'))->whereYear('created_at',date('Y'))->groupBy(DB::raw('MONTHNAME(created_at)'))->where('user_store_id',$id)->get()->toArray();

            if($data){
                return $data;
            }
            return false;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Contact us form.
     *
     */
    public function contactus($requestData) {
        try 
        {
            $sDetails = new SupportFormDetails();
            $sDetails->user_store_id = $requestData['user_store_id'] ?? Auth::user()->id;
            $sDetails->name = $requestData['name'];
            $sDetails->email = $requestData['email'];
            $sDetails->website = $requestData['website'];
            $sDetails->message = $requestData['message'];
            if ($sDetails->save()) {
                $fromEmail = env('FROM_EMAIL');
                $subject = 'Contact Us Form';

                // send email to user (non-blocking: log failures but don't break the response)
                try {
                    $userBody = view('user_email')->with(['data' => $requestData])->render();
                    $this->sendEmail($requestData['email'], $fromEmail, $subject, $userBody);
                } catch (\Throwable $e) {
                    Log::error('Contact us: failed to send user email.', ['error' => $e->getMessage()]);
                }

                // send email to support team (non-blocking)
                try {
                    $supportTeamBody = view('support_team_email')->with(['data' => $requestData])->render();
                    $supportTeamEmail = env('SUPPORT_TEAM_EMAIL');
                    $this->sendEmail($supportTeamEmail, $fromEmail, $subject, $supportTeamBody);
                } catch (\Throwable $e) {
                    Log::error('Contact us: failed to send support team email.', ['error' => $e->getMessage()]);
                }

                return true;
            }
            return false;   
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }


    /**
     * Send Email.
     *
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $body
     * @return boolean
     */
    public function sendEmail($to, $from, $subject, $body) {
        $from = $from ?: config('mail.from.address');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Contact us email skipped because email configuration is missing or invalid.', [
                'to' => $to,
                'from' => $from,
            ]);

            return false;
        }

        try {
            Mail::html($body, function ($message) use ($to, $from, $subject) {
                $message->to($to)
                    ->from($from)
                    ->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Contact us email failed.', [
                'to' => $to,
                'from' => $from,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
  
    /**
     * Reset Settings While Reinstall.
     *
     */
    public function resetSettings($userStoreId) {   
        // delete configuration settings
        TimerConfigurationDetails::where('user_store_id',$userStoreId)->delete();
        // delete disable date
        TimerDisableDates::where('user_store_id',$userStoreId)->delete();
        // delete dispatch date
        TimerDispatchDisableDates::where('user_store_id',$userStoreId)->delete();
        // delete translation settings
        Translations::where('user_store_id',$userStoreId)->delete();
        Log::info('App Reinstall');
    }

    /**
     * App Uninstalled Webhook.
     *
     */
    public function appUninstalled() {   
        $userStoreId = Auth::user()->id;
        // delete configuration settings
        TimerConfigurationDetails::where('user_store_id',$userStoreId)->delete();
        // delete disable date
        TimerDisableDates::where('user_store_id',$userStoreId)->delete();
        // delete dispatch date
        TimerDispatchDisableDates::where('user_store_id',$userStoreId)->delete();
        // delete translation settings
        Translations::where('user_store_id',$userStoreId)->delete();
    }

    /**
     *  Get Products
     *
     * @return value
     */
     public function getProducts($requestData) {
        try 
        {
            Log::info('[SettingsRepository::getProducts] Looking for user with shop_name: ' . $requestData['shop_name']);
            
            $user = User::where('name',$requestData['shop_name'])->first();
            if (!isset($user->id)) {
                Log::warning('[SettingsRepository::getProducts] User not found for shop_name: ' . $requestData['shop_name']);
                return StoreViewLogs::select('product_id', 'product_name')->where('user_store_id', 0);
            }
            
            $userStoreId = $user->id;
            Log::info('[SettingsRepository::getProducts] Found user_store_id: ' . $userStoreId);
            
            $query = StoreViewLogs::select('product_id', DB::raw('MIN(product_name) as product_name'), DB::raw('count(*) as total'))
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at',date('Y'))
                ->groupBy('product_id')
                ->where('user_store_id',$userStoreId);
            
            Log::info('[SettingsRepository::getProducts] Query SQL: ' . $query->toSql());
            
        return $query;
        } catch (Exception $e) {
            Log::error('[SettingsRepository::getProducts] Exception: ' . $e->getMessage());
            return StoreViewLogs::select('product_id', 'product_name')->where('user_store_id', 0);
        }
    }

    /**
     *  Impression Report
     *
     * @return value
     */
    public function impressionsReport($product_id) {
        try 
        {
            $product_id = base64_decode($product_id);
            $userStoreId = Auth::user()->id;
            
            $data = StoreViewLogs::select(DB::raw('DATE_FORMAT(created_at,"%m/%d") as date'),DB::raw('count(*) as total'))->whereMonth('created_at', date('m'))->whereYear('created_at',date('Y'))->where('user_store_id',$userStoreId)->where('product_id',$product_id)->groupBy('date')->get()->toArray();

            if ($data) {
                $data = $data;
            }
            return $data;
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Change Preview Data.
     *
     */
    public function changePreviewData($requestData) {
        try 
        {
            $tcDetails = TimerConfigurationDetails::where('user_store_id',$requestData['user_store_id'])->first();
            if (!$tcDetails) {
                return false;
            }
            $data = $this->settingCalculation($requestData, $tcDetails);
            $data['hide_preview'] = false;
            if (isset($requestData['enable_dispatch_days'])) {
                if ($requestData['enable_dispatch_days'] == 1 && (!isset($requestData['dispatch_days_val']) || (isset($requestData['dispatch_days_val']) && !$requestData['dispatch_days_val']))) {
                    $data['hide_preview'] = true;
                }
            } else if (isset($tcDetails->enable_dispatch_days) && $tcDetails->enable_dispatch_days == 1) {
                if (!isset($tcDetails->dispatch_days) || (isset($tcDetails->dispatch_days) && !$tcDetails->dispatch_days)) {
                    $data['hide_preview'] = true;
                }
            }

            if (!isset($requestData['active_delivery_days_val']) || (isset($requestData['active_delivery_days_val']) && !$requestData['active_delivery_days_val'])) {
                    $data['hide_preview'] = true;
            } else if (!isset($tcDetails->active_delivery_days) || (isset($tcDetails->active_delivery_days) && !$tcDetails->active_delivery_days)) {
                $data['hide_preview'] = true;
            }
            return ['status' => true, 'data' => $data];
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Create Recurring Plan.
     *
     */
    public function createRecurringPlan($userStoreId) {
        $token = $end_point = "";
        $user = User::where('id',$userStoreId)->first();
        $token = $this->getShopifyAccessToken($user);
        
        $form_params = [
            'recurring_application_charge' => [
                'name' => 'Free',
                'price' => 0, 
                'return_url' => "https://" . $user->name."/admin/apps/".env('SHOPIFY_APP_NAME_URL')."/update_subscription",
                'test' => true,
                'capped_amount' => config('api.recurring_application_plan.capped_amount'), 
                'terms' => config('api.recurring_application_plan.terms'),
            ]
        ];

        $form_params = json_encode($form_params);
        $api_endpoint = "/admin/api/2022-07/recurring_application_charges.json";

        $url = "https://" . $user->name . $api_endpoint;
        $headers[] = 'Content-Type: application/json';
         if (!is_null($token)) {
            $headers[] = "X-Shopify-Access-Token: " . $token;
        }

        $method = "POST";
        $data = Api::callCURL($url, $headers, $form_params, $method);
        $responseData = [];
        if (isset($data['error_number']) && $data['error_number']) {
            return $responseData;
        } else {
            if (isset($data['response']) && $data['response']) {
                $responseData = json_decode($data['response'],true);
            }
        }

        if (isset($responseData['recurring_application_charge']['confirmation_url']) && $responseData['recurring_application_charge']['confirmation_url']) {
            return $responseData['recurring_application_charge']['confirmation_url'];
        }       
    }

    /**
     * Get Recurring Plan.
     *
     */
    public function getRecurringPlan($userStoreId, $plan_id) {
        $token = $end_point = "";
        $user = User::where('id',$userStoreId)->first();
        $token = $this->getShopifyAccessToken($user);

        $form_params = [];
        $api_endpoint = "/admin/api/2022-07/recurring_application_charges/".$plan_id.".json";
        // $api_endpoint = "/admin/api/2021-10/recurring_application_charges.json?since_id=4456513537";

        $url = "https://" . $user->name . $api_endpoint;
        $headers[] = 'Content-Type: application/json';
         if (!is_null($token)) {
            $headers[] = "X-Shopify-Access-Token: " . $token;
        }

        $method = "GET";
        $data = Api::callCURL($url, $headers, $form_params, $method);
        $responseData = [];
        if (isset($data['error_number']) && $data['error_number']) {
            return $responseData;
        } else {
            if (isset($data['response']) && $data['response']) {
                $responseData = json_decode($data['response'],true);
            }
        }
        return $responseData;
    }

    /**
     * Subscription Plan.
     *
     */
    public function subscriptionPlans($requestData) {
        $token = $end_point = "";
        $userStoreId = $requestData['user_store_id'];
        $user = User::where('id',$userStoreId)->first();
        if (!$user) {
            return ['status' => false, 'message' => 'Store user was not found.'];
        }

        $token = $this->getShopifyAccessToken($user);
        if (!$token) {
            return ['status' => false, 'message' => 'No valid offline Shopify access token was found. Reinstall the app after clearing old shopify_session rows for this shop.'];
        }

        $plans = EstimatorPlan::where('id',$requestData['subscription_plan_id'])->get()->first();
        if (!isset($plans->id)) {
            return ['status' => false, 'message' => 'Selected subscription plan was not found.'];
        }
        
        if ($plans->type == 'free') {
            $form_params = [
                'recurring_application_charge' => [
                    'name' => $plans->name,
                    'price' => 0, 
                    'return_url' => "https://" . $user->name."/admin/apps/".env('SHOPIFY_APP_NAME_URL')."/update_subscription",
                    'test' => true,
                    'capped_amount' => config('api.recurring_application_plan.capped_amount'), 
                    'terms' => config('api.recurring_application_plan.terms'),
                ]
            ];
        } else {
            $form_params = [
                'recurring_application_charge' => [
                    'name' => $plans->name,
                    'price' => $plans->price, 
                    'return_url' => "https://" . $user->name."/admin/apps/".env('SHOPIFY_APP_NAME_URL')."/update_subscription",
                    'test' => env('RECURRING_TEST_PLAN', true),
                ]
            ];
        }
        $form_params = json_encode($form_params);
        $api_endpoint = "/admin/api/2022-07/recurring_application_charges.json";

        $url = "https://" . $user->name . $api_endpoint;
        $headers[] = 'Content-Type: application/json';
         if (!is_null($token)) {
            $headers[] = "X-Shopify-Access-Token: " . $token;
        }

        $method = "POST";
        $data = Api::callCURL($url, $headers, $form_params, $method);

        $responseData = [];
        if (isset($data['error_number']) && $data['error_number']) {
            Log::error('Shopify recurring charge cURL failed.', $data);

            return ['status' => false, 'message' => $data['errors'] ?: 'Could not connect to Shopify billing API.'];
        } else {
            if (isset($data['response']) && $data['response']) {
                $responseData = json_decode($data['response'],true);
            }
        }

        if (isset($responseData['recurring_application_charge']['confirmation_url']) && $responseData['recurring_application_charge']['confirmation_url']) {
            return [
                'status' => true,
                'confirmation_url' => $responseData['recurring_application_charge']['confirmation_url'],
            ];
        }

        Log::error('Shopify recurring charge response did not include confirmation_url.', [
            'response_code' => $data['response_code'] ?? null,
            'response' => $responseData,
        ]);

        $message = $responseData['errors'] ?? $responseData['error'] ?? 'Shopify did not return a confirmation URL.';
        if (is_array($message)) {
            $message = json_encode($message);
        }

        return ['status' => false, 'message' => $message];
    }

    /**
     * Update Subscription Plan.
     *
     */
    public function updateSubscription($requestData) {
        $userStoreId = Auth::user()->id;
        if(isset($requestData['charge_id']) && $requestData['charge_id']) {
            $planData = $this->getRecurringPlan($userStoreId, $requestData['charge_id']);
            if (isset($planData['recurring_application_charge']) && $planData['recurring_application_charge']) {
                $checkExists = RecurringApplicationPlans::where('user_store_id',$userStoreId)->where('charge_id',$requestData['charge_id'])->get()->first();
                if ($checkExists) {
                    return true;
                }
                $rPC = new RecurringApplicationPlans();
                $rPC->user_store_id = $userStoreId;
                $rPC->charge_id = (string)$planData['recurring_application_charge']['id'];
                $rPC->name = $planData['recurring_application_charge']['name'];
                $rPC->api_client_id = (string)$planData['recurring_application_charge']['api_client_id'];
                $rPC->price = $planData['recurring_application_charge']['price'];
                $estPlans = EstimatorPlan::where('name',$planData['recurring_application_charge']['name'])->get()->first();
                $rPC->limit = $estPlans->limit;
                if (isset($planData['recurring_application_charge']['capped_amount'])) {
                    $rPC->capped_amount = $planData['recurring_application_charge']['capped_amount'];
                }
                $rPC->status = $planData['recurring_application_charge']['status'];
                if ($planData['recurring_application_charge']['test']) {
                    $rPC->test = $planData['recurring_application_charge']['test'];
                } else {
                    $rPC->test = 0;
                }
                $rPC->trial_days = $planData['recurring_application_charge']['trial_days'];
                $rPC->return_url = $planData['recurring_application_charge']['return_url'];
                $rPC->decorated_return_url = $planData['recurring_application_charge']['decorated_return_url'];
                if (isset($planData['recurring_application_charge']['confirmation_url'])) {
                    $rPC->confirmation_url = $planData['recurring_application_charge']['confirmation_url'];
                }
                if ($rPC->save()) {
                    RecurringApplicationPlans::where('user_store_id',$userStoreId)->where('charge_id','!=',$requestData['charge_id'])->delete();
                }
            }
        }
    }
}
