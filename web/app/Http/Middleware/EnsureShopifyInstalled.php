<?php

namespace App\Http\Middleware;

use App\Lib\AuthRedirection;
use App\Models\ShopifySession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shopify\Utils;

class EnsureShopifyInstalled
{
    /**
     * Checks if the shop in the query arguments is currently installed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is already authenticated (session established by EnsureShopifySession), let through
        // if (Auth::check()) {
        //     return $next($request);
        // }

        // Get shop from 'shop' query param
        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        // Try to decode shop from the 'host' param (Shopify passes it as base64)
        if (!$shop && $request->query('host')) {
            $decoded = base64_decode($request->query('host'));
            // host is like "admin.shopify.com/store/my-store"
            if (preg_match('/\/store\/([^\/]+)/', $decoded, $matches)) {
                $shop = $matches[1] . '.myshopify.com';
            }
        }

        // If the shop is missing from the request (e.g. form submissions/AJAX) but user is authenticated,
        // we can retrieve it from the session and inject it into the request.
        if (!$shop && Auth::check()) {
            $shop = Auth::user()->name;
            $request->query->set('shop', $shop);
        }

        $isEmbedded = $request->query('embedded') === '1';
        $isExitingIframe = preg_match("/^ExitIframe/i", $request->path());

        // We must check if the app is actually installed (has an access token).
        // If not, we must force OAuth, even if Auth::check() is true (stale session).
        $appInstalled = $shop && ShopifySession::where('shop', $shop)->whereNotNull('access_token')->exists();

        if (!$appInstalled && !$isExitingIframe) {
            Auth::logout();
            // If it's an AJAX/JSON request, returning a redirect causes CORS. Return 401 instead.
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return AuthRedirection::redirect($request);
        }

        // If it's a top-level navigation request from Shopify Admin (no embedded=1),
        // we force OAuth to ensure a fresh session. We ONLY do this on GET requests 
        // to avoid breaking POST/AJAX form submissions which don't have embedded=1.
        if ($request->isMethod('GET') && $shop && !$isEmbedded && !$isExitingIframe) {
            Auth::logout();
            return AuthRedirection::redirect($request);
        }

        // If user is already authenticated (session established by EnsureShopifySession), let through
        if (Auth::check()) {
            return $next($request);
        }
        //  $appInstalled = $shop && ShopifySession::where('shop', $shop)->whereNotNull('access_token')->exists();

        return ($appInstalled || $isExitingIframe) ? $next($request) : AuthRedirection::redirect($request);
    }
}
