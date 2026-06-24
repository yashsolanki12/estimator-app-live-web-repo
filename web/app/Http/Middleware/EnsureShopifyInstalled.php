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
        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        if (!$shop && $request->query('host')) {
            $decoded = base64_decode($request->query('host'));
            if (preg_match('/\/store\/([^\/]+)/', $decoded, $matches)) {
                $shop = $matches[1] . '.myshopify.com';
            }
        }

        if (!$shop && Auth::check()) {
            $shop = Auth::user()->name;
            $request->query->set('shop', $shop);
        }

        $isEmbedded = $request->query('embedded') === '1';
        $isExitingIframe = preg_match("/^ExitIframe/i", $request->path());

        $hasOfflineSession = $shop && ShopifySession::where('shop', $shop)
            ->where('is_online', 0)
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->exists();

        $hasOnlineSession = $shop && ShopifySession::where('shop', $shop)
            ->where('is_online', 1)
            ->whereNotNull('access_token')
            ->where('access_token', '!=', '')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        $appInstalled = $hasOfflineSession || $hasOnlineSession;

        if (!$appInstalled && !$isExitingIframe) {
            Auth::logout();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return AuthRedirection::redirect($request);
        }

        if ($request->isMethod('GET') && $shop && !$isEmbedded && !$isExitingIframe) {
            Auth::logout();
            return AuthRedirection::redirect($request);
        }

        return ($appInstalled || $isExitingIframe) ? $next($request) : AuthRedirection::redirect($request);
    }
}
