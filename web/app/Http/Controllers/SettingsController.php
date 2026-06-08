<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Repositories\SettingsRepository;
use App\Models\Timezones;
use App\Models\TimerConfigurationDetails;
use App\Models\TimerDisableDates;
use App\Models\TimerDispatchDisableDates;
use App\Models\Translations;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\RecurringApplicationPlans;
use Illuminate\Support\Facades\Redirect;
use App\Models\EstimatorPlan;

class SettingsController extends Controller
{

	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SettingsRepository $settings) {
        $this->settings = $settings;
    }

    /**
     * Dashboard.
     *
     */
    public function dashboard(Request $request) {
        try {
            $userStoreId = Auth::user()->id;
            $user = User::where('id',$userStoreId)->first();
            if (isset($user->created_at) && isset($user->updated_at) && $user->created_at != $user->updated_at) {
                // reset settings while reinstall
                $this->settings->resetSettings($userStoreId);
                $user->updated_at = $user->created_at;
                $user->save();
            }
            // create default settings
            $this->settings->createDefaultSettings($userStoreId);
            
            $plan = RecurringApplicationPlans::where('user_store_id',$userStoreId)->where('status','active')->first();
            $currentPlan = null;
            if (isset($plan->name)) {
                $currentPlan = EstimatorPlan::where('name',$plan->name)->get()->first();
            }
            $data = $this->settings->totalViewCountPerStore($userStoreId);
            $total = 0;
            if (isset($data[0]['total']) && $data[0]['total']) {
                $total = $data[0]['total'];
            }
            $limitExceeded = 0;
            if (isset($currentPlan->limit) && $currentPlan->limit && $currentPlan->limit <= $total) {
                $limitExceeded = 1;
            }
            $appEmbedStatus = $this->settings->getAppEmbedStatus($userStoreId);
            return view('dashboard')->with([
                'total' => $total,
                'currentPlan' => $currentPlan,
                'limitExceeded' => $limitExceeded,
                'appEmbedStatus' => $appEmbedStatus,
            ]);
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Redirect Recurring Plans Page.
     *
     */
    public function redirectRecurringPlans(Request $request) {
        $requestData = $request->all();
        if (isset($requestData['url'])) {
            return view('redirect_page')->with(['confirmation_url' => $requestData['url']]);
        }
        return redirect(route('home'));
    }   

    /**
     * Timer Configuration.
     *
     */
    public function getTimerConfiguration(Request $request) {
        try 
        {
            $timezones = Timezones::select('id', 'value', 'label')->get()->toArray();
            $timerDetails = TimerConfigurationDetails::where('user_store_id',Auth::user()->id)->first();

            $disableDates = TimerDisableDates::where('user_store_id',Auth::user()->id)->get()->toArray();
            $disableDispatchDates = TimerDispatchDisableDates::where('user_store_id',Auth::user()->id)->get()->toArray();
            $collection = $this->settings->getShopifyCollection();
            
            $settingsData = [];
            $userData = User::where('id',Auth::user()->id)->first();
            if (isset($userData->name)) {
                $requestData['shop'] = $userData->name;

                $settingsData = $this->settings->getAdminSettings($requestData);
                if (!$settingsData) {
                    $settingsData = [];
                }
            }
            
            return view('timer')->with(['timezones' => $timezones, 'timerDetails' => $timerDetails, 'disableDates' => $disableDates, 'disableDispatchDates' => $disableDispatchDates, 'collection' => $collection, 'settingsData' => $settingsData]);            
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Chnage Status.
     *
     */
    public function changeStatus(Request $request) {
        $requestData = $request->all();
        $data = $this->settings->changeStatus($requestData);
        if ($data) {
            return json_encode(['status' => true]);
        }
        return json_encode(['status' => false]);
    }
    
    /**
     * Save Timer Configuration.
     *
     */
    public function saveTimerConfiguration(Request $request) {
        $requestData = $request->all();
        $data = $this->settings->saveTimerConfiguration($requestData);
        if ($data) {
            return json_encode(['status' => true]);
        }
        return json_encode(['status' => false]);
    }

    /**
     * Add Disabled Date
     *
     * @return mix
     */
    public function addDisabledDate(Request $request) {
        try 
        {
            $requestData = $request->all();
            $data = $this->settings->addDisabledDate($requestData);
            if ($data) {
                $disableDates = TimerDisableDates::where('user_store_id',$requestData['user_store_id'])->get()->toArray();
                $body = '';
                foreach ($disableDates as $dValue) {
                    $body .= '<tr>
                                <td>'.$dValue['date'].'</td>
                                <td class="trash_col"><span class="delete_dates" data-id="'.$dValue['id'].'"><i class="fa fa-trash-alt"></i></span></td>
                            </tr>';
                }
                return json_encode(['status' => true, 'body' => $body]);
            }
            return json_encode(['status' => false]);
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

     /**
     * Delete Disabled Date
     *
     * @return mix
     */
    public function deleteDisabledDate(Request $request) {
        try 
        {
            $requestData = $request->all();
            $data = $this->settings->deleteDisabledDate($requestData);
            if ($data) {
                $disableDates = TimerDisableDates::where('user_store_id',$requestData['user_store_id'])->get()->toArray();
                $body = '';
                foreach ($disableDates as $dValue) {
                    $body .= '<tr>
                                <td>'.$dValue['date'].'</td>
                                <td class="trash_col"><span class="delete_dates" data-id="'.$dValue['id'].'"><i class="fa fa-trash-alt"></i></span></td>
                            </tr>';
                }
                return json_encode(['status' => true, 'body' => $body]);
            }
            return json_encode(['status' => false]);
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

    /**
     * Add Disabled Date
     *
     * @return mix
     */
    public function addDispatchDisabledDate(Request $request) {
        try 
        {
            $requestData = $request->all();
            $data = $this->settings->addDispatchDisabledDate($requestData);
            if ($data) {
                $disableDispatchDates = TimerDispatchDisableDates::where('user_store_id',Auth::user()->id)->get()->toArray();
                $body = '';
                foreach ($disableDispatchDates as $dValue) {
                    $body .= '<tr>
                                <td>'.$dValue['date'].'</td>
                                <td class="trash_col"><span class="delete_dispacth_dates" data-id="'.$dValue['id'].'"><i class="fa fa-trash-alt"></i></span></td>
                            </tr>';
                }
                return json_encode(['status' => true, 'body' => $body]);
            }
            return json_encode(['status' => false]);
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }

     /**
     * Delete Disabled Date
     *
     * @return mix
     */
    public function deleteDispatchDisabledDate(Request $request) {
        try 
        {
            $requestData = $request->all();
            $data = $this->settings->deleteDispatchDisabledDate($requestData);
            if ($data) {
                $disableDispatchDates = TimerDispatchDisableDates::where('user_store_id',Auth::user()->id)->get()->toArray();
                $body = '';
                foreach ($disableDispatchDates as $dValue) {
                    $body .= '<tr>
                                <td>'.$dValue['date'].'</td>
                                <td class="trash_col"><span class="delete_dispacth_dates" data-id="'.$dValue['id'].'"><i class="fa fa-trash-alt"></i></span></td>
                            </tr>';
                }
                return json_encode(['status' => true, 'body' => $body]);
            }
            return json_encode(['status' => false]); 
        } catch (Exception $e) {
            Log::error($e);
            return view('errorpage')->with(['message' => $e->getMessage()]);           
        }
    }
    
    /**
     * Settings.
     *
     */
    public function settings(Request $request) {
        if (!Auth::check()) {
            return false;  
        }
        $translationsDetails = Translations::where('user_store_id',Auth::user()->id)->first();
        return view('settings')->with(['translationsDetails' => $translationsDetails]);
    }

    /**
     * Save Translations Settings
     *
     * @return mix
     */
    public function saveTranslationsSettings(Request $request) {
        $requestData = $request->all();
        $requestData['user_store_id'] = Auth::id();
        $data = $this->settings->saveTranslationsSettings($requestData);
       
        if ($data) {
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false], 500);
    }

    /**
     * Get All Settings
     *
     * @return mix
     */
    public function getAllSettings(Request $request) {
        $requestData = $request->all();
        $data = $this->settings->getAllSettings($requestData);
        if ($data) {
            return (['status' => true, 'settings' => $data]);
        }
        return (['status' => false]);
    }

    /**
     * Save View Logs
     *
     * @return mix
     */
    public function storeViewLogs(Request $request) {
        $requestData = $request->all();
        $data = $this->settings->storeViewLogs($requestData);
        if ($data) {
            return (['status' => true, 'data' => $data]);
        }
        return (['status' => false]);
    }

    /**
     *  Total View Count
     *
     * @return mix
     */
    public function  totalViewCount() {
        $data = $this->settings->totalViewCount();
        if ($data) {
            return (['status' => true, 'data' => $data]);
        }
        return (['status' => false]);
    }

    /**
     *  Total View Count Per Store
     *
     * @return mix
     */
    public function  totalViewCountPerStore($id) {
        $data = $this->settings->totalViewCountPerStore($id);
        if ($data) {
            return (['status' => true, 'data' => $data]);
        }
        return (['status' => false]);
    }
    
    /**
     * Contact us form.
     *
     */
    public function contactus(Request $request) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'website' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'max:65000'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $requestData = $request->all();
            $data = $this->settings->contactus($requestData);
            if ($data) {
                return (['status' => true]);
            }
            return (['status' => false]);
        }
        return view('contactus');
    }

    /**
     * App Uninstalled Webhook.
     *
     */
    public function appUninstalled(Request $request) {   
        $data = $this->settings->appUninstalled();
        if ($data) {
            return (['status' => true]);
        }
        return (['status' => false]);
    }

    /**
     * Usage Report.
     *
     */
    public function usageReport() {
       return view('usage_report');
    }

    /**
     * Get Products.
     *
     */
    public function getProducts(Request $request) {
        $requestData = $request->all();
        Log::info('[getProducts] Request shop_name: ' . ($requestData['shop_name'] ?? 'missing'));
        
        if (!isset($requestData['shop_name'])) {
            Log::error('[getProducts] Missing shop_name in request');
            return response()->json(['error' => 'Missing shop_name'], 400);
        }
        
        try {
            $dataQuery = $this->settings->getProducts($requestData);
            Log::info('[getProducts] Query: ' . $dataQuery->toSql());

            return DataTables::eloquent($dataQuery)
                ->addColumn('action', function($row){
                    $productId = $row->product_id ?? '';
                    $productId = is_object($row) ? $row->product_id : (is_array($row) ? $row['product_id'] : $productId);
                    return  '<button type="button" data-product-id="'.base64_encode($productId).'" class="btn btn-outline-view-chart view_chart">View Chart</button>';
                })
                ->make(true);
        } catch (Exception $e) {
            Log::error('[getProducts] Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Impression Report.
     *
     */
    public function impressionsReport(Request $request, $product_id) {   
        $data = $this->settings->impressionsReport($product_id);
        return view('impressions_report')->with('data',$data);
    }

    /**
     * Change Preview Data.
     *
     */
    public function changePreviewData(Request $request) {   
        $requestData = $request->all();
        return $this->settings->changePreviewData($requestData);
    }

    /**
     * Subscription Plan.
     *
     */
    public function subscriptionPlans(Request $request) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestData = $request->all();
            $data = $this->settings->subscriptionPlans($requestData);

            if (isset($data['status']) && $data['status'] === true) {
                return (['status' => true, 'confirmation_url' => $data['confirmation_url']]);
            }

            return ([
                'status' => false,
                // 'data' => $data,
                'message' => $data['message'] ?? 'Unable to create the subscription charge.',
            ]);
        }
        $userStoreId = Auth::user()->id;
        $plan = RecurringApplicationPlans::where('user_store_id',$userStoreId)->where('status','active')->first();
        $currentPlan = [];
        $plans = [];
        if ($plan && isset($plan->name)) {
            $currentPlan = EstimatorPlan::where('name',$plan->name)->get()->first();
            $plans = EstimatorPlan::where('name', '!=', $plan->name)->get()->toArray();
        } else {
               // If no active plan, show all plans
               $plans = EstimatorPlan::all()->toArray();
           }
        $shopDomain = Auth::user()->name;
        $storeHandle = str_replace('.myshopify.com', '', $shopDomain);
        $appName = env('name', 'ecs-delivery-estimator');
        $billingUrl = "https://admin.shopify.com/store/{$storeHandle}/charges/{$appName}/pricing_plans";

        return view('subscription_plan')->with(['plans' => $plans, 'currentPlan' => $currentPlan, 'billingUrl' => $billingUrl]);
    }

    /**
     * Approve Subscription Plan.
     *
     */
    public function updateSubscription(Request $request) {
        $data = $this->settings->updateSubscription($request->all());
        return redirect(route('home'));
    }

    public function showError() {
        return view('showerrorpage')->with(['message' => \Session::get('message')]);
    }

    public function customersDataRequest() {
        return \Redirect::route('home');
    }

    public function customersRedact() {
        return \Redirect::route('home');
    }

    public function shopRedact() {
        return \Redirect::route('home');
    }
}
