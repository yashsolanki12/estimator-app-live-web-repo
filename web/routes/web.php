<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopifySessionController;
use App\Http\Controllers\WebhookController;
use App\Lib\AuthRedirection;
use App\Lib\EnsureBilling;
use App\Models\ShopifySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\InvalidWebhookException;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Http\Controllers\SettingsController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| are loaded by RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| If you are adding routes outside of the /api path, remember to also add a
| proxy rule for them in web/frontend/vite.config.js
|
*/
Route::group(['middleware' => ['shopify.installed', 'shopify.auth']], function () {
    Route::group(['middleware' => 'checksubscriptionplan'], function () {
        Route::get('/', [SettingsController::class, 'dashboard'])->name('home');
        Route::get('timer', [SettingsController::class, 'getTimerConfiguration'])->name('getTimerConfiguration');
        Route::get('settings', [SettingsController::class, 'settings'])->name('settings');
        Route::get('usage_report', [SettingsController::class, 'usageReport'])->name('usageReport');
        Route::get('impression_report/{product_id}', [SettingsController::class, 'impressionsReport'])->name('impressionsReport');
        Route::match(array('GET', 'POST'), 'contact_us', [SettingsController::class, 'contactus'])->name('contactus');
    });

    Route::post('timer', [SettingsController::class, 'saveTimerConfiguration'])->name('saveTimerConfiguration');
    Route::post('timer/change_status', [SettingsController::class, 'changeStatus'])->name('changeStatus');
    Route::post('timer/add_disabled_date', [SettingsController::class, 'addDisabledDate'])->name('addDisabledDate');
    Route::post('timer/delete_disabled_date', [SettingsController::class, 'deleteDisabledDate'])->name('deleteDisabledDate');
    Route::post('timer/add_dispatch_disabled_date', [SettingsController::class, 'addDispatchDisabledDate'])->name('addDispatchDisabledDate');
    Route::post('timer/delete_dispatch_disabled_date', [SettingsController::class, 'deleteDispatchDisabledDate'])->name('deleteDispatchDisabledDate');
    Route::post('settings', [SettingsController::class, 'saveTranslationsSettings'])->name('saveTranslationsSettings');

    Route::get('total_view_count', [SettingsController::class, 'totalViewCount']);
    Route::get('total_view_count/{id}', [SettingsController::class, 'totalViewCountPerStore']);
    Route::post('change_preview_data', [SettingsController::class, 'changePreviewData'])->name('changePreviewData');
    Route::get('redirect_recurring_plans', [SettingsController::class, 'redirectRecurringPlans'])->name('redirectRecurringPlans');
    Route::match(array('GET', 'POST'), 'subscription_plans', [SettingsController::class, 'subscriptionPlans'])->name('subscriptionPlans');
    Route::get('update_subscription', [SettingsController::class, 'updateSubscription'])->name('updateSubscription');
    
    Route::post('get_products', [SettingsController::class, 'getProducts'])->name('getProducts');
}); 

Route::get('get_settings', [SettingsController::class, 'getAllSettings'])->name('getAllSettings');
Route::post('store_view_logs', [SettingsController::class, 'storeViewLogs']);
Route::get('error_page', [SettingsController::class, 'showError'])->name('showError');

// webhook routes
Route::group(['middleware' => 'checkwebhook'], function () {
    Route::post('customers/data_request', [SettingsController::class, 'customersDataRequest']);
    Route::post('customers/redact', [SettingsController::class, 'customersRedact']);
    Route::post('shop/redact', [SettingsController::class, 'shopRedact']);
});


Route::get('/ExitIframe', function (Request $request) {
    $apiKey = config('services.shopify.api_key');
    $redirectUri = $request->query('redirectUri', '');
    $decodedUri = urldecode($redirectUri);
    $safeUri = htmlspecialchars($decodedUri, ENT_QUOTES, 'UTF-8');
    
    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting...</title>
    <meta name="shopify-api-key" content="{$apiKey}" />
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
</head>
<body>
    <p>Redirecting to Shopify login...</p>
   
    <script>
        var redirectUri = "{$safeUri}";
        if (window.top === window.self) {
            window.location.assign(redirectUri);
        } else {
            open(redirectUri, "_top");
        }
    </script>
</body>
</html>
HTML;
    return response($html);
});

Route::get('/api/auth', function (Request $request) {
    $shopParam = $request->query('shop');
    if (!$shopParam) {
        abort(400, 'Missing required "shop" parameter. Access this app through Shopify Admin.');
    }
    return AuthRedirection::redirect($request);
});

Route::get('/api/auth/callback', function (Request $request) {
    $shop = $request->query('shop');
    Log::info('Shopify auth callback received', [
        'shop' => $shop,
        'host' => $request->query('host'),
        'has_cookie' => !empty($request->cookie()),
    ]);

    try {
        $session = OAuth::callback(
            $request->cookie(),
            $request->query(),
            ['App\Lib\CookieHandler', 'saveShopifyCookie'],
        ); 
        Log::info('Shopify OAuth callback succeeded', [
            'shop' => $session->getShop(),
            'session_id' => $session->getId(),
            'is_online' => $session->isOnline(),
            'has_token' => (bool) $session->getAccessToken(),
        ]);

        // 3. REGISTER WEBHOOKS (Crucial for Uninstall tracking)
        \Shopify\Webhooks\Registry::register(
            '/webhooks',
            \Shopify\Webhooks\Topics::APP_UNINSTALLED,
            $session->getShop(),
            $session->getAccessToken()
        );

    } catch (\Exception $e) {
        Log::error("Auth Callback Error: " . $e->getMessage());
        // If we have a shop, let's at least show the error clearly
        return response()->json(['error' => 'Auth Failed', 'message' => $e->getMessage(), 'shop' => $shop], 500);
    }

    $host = $request->query('host');
    $redirectUrl = Utils::getEmbeddedAppUrl($host);
    Log::info('Redirecting after auth callback', ['redirect_url' => $redirectUrl]);
    return redirect($redirectUrl);
});

Route::get('/api/clean-db', function () {
    \App\Models\ShopifySession::truncate();
    return "Database wiped clean. Please reinstall the app.";
});

// Shared database routes - access and shopify_session tables
Route::get('/api/shopify-sessions', [ShopifySessionController::class, 'index']);
Route::get('/api/shopify-sessions/{id}', [ShopifySessionController::class, 'show']);

// routes/api.php
Route::post('/webhooks', [App\Http\Controllers\WebhookController::class, 'handle']);

Route::fallback(function (Request $request) {
    return redirect('/');
});

