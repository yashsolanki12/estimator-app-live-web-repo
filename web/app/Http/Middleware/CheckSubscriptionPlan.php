<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RecurringApplicationPlans;
use Illuminate\Support\Facades\Auth;
use View;
use App\Repositories\SettingsRepository;
use App\Http\Controllers\SettingsController;

class CheckSubscriptionPlan
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SettingsRepository $settings, SettingsController $settingsC) {
        $this->settings = $settings;
        $this->settingsC = $settingsC;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::check() && Auth::user()->id) {
            $userStoreId = Auth::user()->id;
            $checkPlans = RecurringApplicationPlans::where('user_store_id',$userStoreId)->first();
            
            // Try to sync from API if no active plan in DB
            if (!isset($checkPlans->charge_id) || !$checkPlans->charge_id || $checkPlans->status !== 'active') {
                $shopDomain = Auth::user()->name;
                $token = \App\Models\ShopifySession::where('shop', $shopDomain)->whereNotNull('access_token')->orderBy('id', 'desc')->value('access_token');
                
                if ($token) {
                    $apiVersion = config('services.shopify.api_version', '2023-10');
                    $url = "https://{$shopDomain}/admin/api/{$apiVersion}/recurring_application_charges.json";
                    $headers = [
                        'Content-Type: application/json',
                        "X-Shopify-Access-Token: {$token}",
                    ];
                    $response = \App\Services\Api::callCURL($url, $headers, [], 'GET');
                    
                    if (isset($response['response'])) {
                        $data = json_decode($response['response'], true);
                        if (isset($data['recurring_application_charges'])) {
                            foreach ($data['recurring_application_charges'] as $charge) {
                                if ($charge['status'] === 'active') {
                                    // Found an active charge on Shopify, save it!
                                    if (!$checkPlans) {
                                        $checkPlans = new RecurringApplicationPlans();
                                        $checkPlans->user_store_id = $userStoreId;
                                    }
                                    $checkPlans->charge_id = $charge['id'];
                                    $checkPlans->name = $charge['name'];
                                    
                                    $estimatorPlan = \App\Models\EstimatorPlan::where('name', $charge['name'])->first();
                                    if ($estimatorPlan) {
                                        $checkPlans->limit = $estimatorPlan->limit;
                                        $checkPlans->price = $estimatorPlan->price;
                                    }
                                    
                                    $checkPlans->status = 'active';
                                    $checkPlans->save();
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if (!isset($checkPlans->charge_id) || !$checkPlans->charge_id || $checkPlans->status !== 'active') {
                return redirect(route('subscriptionPlans', $request->query()));
            }

            $rACPlans = RecurringApplicationPlans::where('user_store_id',$userStoreId)->first();
            if (isset($rACPlans->charge_id) && $rACPlans->charge_id) {
                $planData = $this->settings->getRecurringPlan($userStoreId,$rACPlans->charge_id);
                if (isset($planData['recurring_application_charge']['status']) && in_array($planData['recurring_application_charge']['status'], ['declined','expired','cancelled'])) {
                    // delete recurring plan
                    RecurringApplicationPlans::where('user_store_id',$userStoreId)->delete();
                    
                    $shopDomain = Auth::user()->name;
                    $storeHandle = str_replace('.myshopify.com', '', $shopDomain);
                    $appName = env('name', 'ecs-delivery-estimator');
                    
                    return redirect(route('subscriptionPlans', $request->query()));
                }
            }
        }
        return $next($request);
    }
}