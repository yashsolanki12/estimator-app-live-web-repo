<?php

namespace App\Http\Middleware;

use App\Lib\AuthRedirection;
use App\Models\ShopifySession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shopify\Auth\OAuth;
use Shopify\Context;
use Shopify\Utils;

class EnsureShopifySession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // First, check if user is already authenticated - if so, allow through
        if (Auth::check()) {
            return $next($request);
        }

        // Get shop from 'shop' query param, or decode from 'host' (base64 encoded)
        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        // Try to decode shop from the 'host' param (Shopify passes it as base64)
        if (!$shop && $request->query('host')) {
            $decoded = base64_decode($request->query('host'));
            // host is like "admin.shopify.com/store/my-store" — extract shop from it
            if (preg_match('/\/store\/([^\/]+)/', $decoded, $matches)) {
                $shop = $matches[1] . '.myshopify.com';
            }
        }

        $isExitingIframe = preg_match("/^ExitIframe/i", $request->path());

        // In local environment, allow access without shop parameter
        if (config('app.env') === 'local' && !$shop && !$isExitingIframe) {
            return $next($request);
        }

        // If no shop parameter and not exiting iframe, we need to redirect
        if (!$shop && !$isExitingIframe) {
            return AuthRedirection::redirect($request);
        }

        // Check if shop has a valid access token stored in DB
        $shopRecord = $shop ? ShopifySession::where('shop', $shop)->whereNotNull('access_token')->first() : null;

        if (!$shopRecord && !$isExitingIframe) {
            // Shop not installed or token was cleared - start OAuth again
            Auth::logout();
            return AuthRedirection::redirect($request);
        }

        if ($shopRecord) {
            // Try to load active session from cookie first
            $sessionId = $request->cookie(OAuth::SESSION_ID_COOKIE_NAME);
            $sessionLoaded = false;

            if ($sessionId && Context::$SESSION_STORAGE) {
                $session = Context::$SESSION_STORAGE->loadSession($sessionId);
                if ($session) {
                    $request->attributes->set('shopifySession', $session);
                    $sessionLoaded = true;
                }
            }
            // Keep Laravel auth aligned with the actual Shopify session state.
            $user = User::firstOrCreate(
                ['name' => $shop],
                [
                    'email' => "shop@$shop",
                    'password' => bcrypt(uniqid()),
                ]
            );
            Auth::login($user);

           
            return $next($request);
        }

        Auth::logout();
        return $next($request);
    }
}
