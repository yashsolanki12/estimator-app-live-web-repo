<?php

namespace App\Http\Middleware;

use App\Lib\AuthRedirection;
use App\Models\ShopifySession;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Shopify\Auth\AccessTokenOnlineUserInfo;
use Shopify\Auth\OAuth;
use Shopify\Auth\Session;
use Shopify\Context;
use Shopify\Utils;

class EnsureShopifySession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        $shop = $request->query('shop') ? Utils::sanitizeShopDomain($request->query('shop')) : null;

        if (!$shop && $request->query('host')) {
            $decoded = base64_decode($request->query('host'));
            if (preg_match('/\/store\/([^\/]+)/', $decoded, $matches)) {
                $shop = $matches[1] . '.myshopify.com';
            }
        }

        $isExitingIframe = preg_match("/^ExitIframe/i", $request->path());

        if (config('app.env') === 'local' && !$shop && !$isExitingIframe) {
            return $next($request);
        }

        if (!$shop && !$isExitingIframe) {
            return AuthRedirection::redirect($request);
        }

        $shopRecord = null;

        if ($shop) {
            $shopRecord = ShopifySession::where('shop', $shop)
                ->where('is_online', 0)
                ->whereNotNull('access_token')
                ->orderBy('id', 'desc')
                ->first();

            if (!$shopRecord) {
                $shopRecord = ShopifySession::where('shop', $shop)
                    ->where('is_online', 1)
                    ->whereNotNull('access_token')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        if (!$shopRecord && !$isExitingIframe) {
            Auth::logout();
            return AuthRedirection::redirect($request);
        }

        if ($shopRecord) {
            $session = new Session(
                $shopRecord->session_id,
                $shopRecord->shop,
                (bool)$shopRecord->is_online,
                $shopRecord->state
            );

            if ($shopRecord->expires_at) {
                $session->setExpires(new \DateTime($shopRecord->expires_at));
            }
            if ($shopRecord->access_token) {
                $session->setAccessToken($shopRecord->access_token);
            }
            if ($shopRecord->scope) {
                $session->setScope($shopRecord->scope);
            }
            if ($shopRecord->user_id) {
                $onlineAccessInfo = new AccessTokenOnlineUserInfo(
                    (int)$shopRecord->user_id,
                    $shopRecord->user_first_name,
                    $shopRecord->user_last_name,
                    $shopRecord->user_email,
                    (bool)$shopRecord->user_email_verified,
                    (bool)$shopRecord->account_owner,
                    $shopRecord->locale,
                    (bool)$shopRecord->collaborator
                );
                $session->setOnlineAccessInfo($onlineAccessInfo);
            }

            $sessionId = $request->cookie(OAuth::SESSION_ID_COOKIE_NAME);
            if ($sessionId && Context::$SESSION_STORAGE) {
                $cookieSession = Context::$SESSION_STORAGE->loadSession($sessionId);
                if ($cookieSession && $cookieSession->getAccessToken()) {
                    $session = $cookieSession;
                }
            }

            $request->attributes->set('shopifySession', $session);

            $user = User::firstOrCreate(
                ['name' => $shopRecord->shop],
                [
                    'email' => "shop@{$shopRecord->shop}",
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
