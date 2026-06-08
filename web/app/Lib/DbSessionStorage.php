<?php

declare(strict_types=1);

namespace App\Lib;

use Exception;
use Shopify\Auth\AccessTokenOnlineUserInfo;
use Shopify\Auth\Session;
use Shopify\Auth\SessionStorage;
use Illuminate\Support\Facades\Log;

class DbSessionStorage implements SessionStorage
{
    public function loadSession(string $sessionId): ?Session
    {
        Log::info('Loading Shopify session', ['session_id' => $sessionId]);
        $dbSession = \App\Models\ShopifySession::where('session_id', $sessionId)->first();

        if ($dbSession) {
            $session = new Session(
                $dbSession->session_id,
                $dbSession->shop,
                $dbSession->is_online == 1,
                $dbSession->state
            );
            if ($dbSession->expires_at) {
                $session->setExpires(new \DateTime($dbSession->expires_at));
            }
            if ($dbSession->access_token) {
                $session->setAccessToken($dbSession->access_token);
            }
            if ($dbSession->scope) {
                $session->setScope($dbSession->scope);
            }
            if ($dbSession->user_id) {
                $onlineAccessInfo = new AccessTokenOnlineUserInfo(
                    (int)$dbSession->user_id,
                    $dbSession->user_first_name,
                    $dbSession->user_last_name,
                    $dbSession->user_email,
                    $dbSession->user_email_verified == 1,
                    $dbSession->account_owner == 1,
                    $dbSession->locale,
                    $dbSession->collaborator == 1
                );
                $session->setOnlineAccessInfo($onlineAccessInfo);
            }
            return $session;
        }
        return null;
    }

    public function storeSession(Session $session): bool
    {
        Log::info('Attempting to store Shopify session', [
            'session_id' => $session->getId(),
            'shop' => $session->getShop(),
            'is_online' => $session->isOnline(),
            'has_access_token' => (bool) $session->getAccessToken(),
        ]);
        
        // Try to find existing session by session_id first
        $dbSession = \App\Models\ShopifySession::where('session_id', $session->getId())->first();
        if (!$dbSession) {
            // Only reuse shop record if it has a valid access token
            $dbSession = \App\Models\ShopifySession::where('shop', $session->getShop())
                ->whereNotNull('access_token')
                ->orderBy('id', 'desc')
                ->first();
        }
        if (!$dbSession) {
            $dbSession = new \App\Models\ShopifySession();
        }
        $dbSession->session_id = $session->getId();
        $dbSession->shop = $session->getShop();
        $dbSession->state = $session->getState();
        $dbSession->is_online = $session->isOnline();
        $dbSession->access_token = $session->getAccessToken();
        $dbSession->expires_at = $session->getExpires() ? $session->getExpires()->format('Y-m-d H:i:s') : null;
        $dbSession->scope = $session->getScope();

        if ($session->getOnlineAccessInfo()) {
            $dbSession->user_id = (string)$session->getOnlineAccessInfo()->getId();
            $dbSession->user_first_name = $session->getOnlineAccessInfo()->getFirstName();
            $dbSession->user_last_name = $session->getOnlineAccessInfo()->getLastName();
            $dbSession->user_email = $session->getOnlineAccessInfo()->getEmail();
            $dbSession->user_email_verified = $session->getOnlineAccessInfo()->isEmailVerified();
            $dbSession->account_owner = $session->getOnlineAccessInfo()->isAccountOwner();
            $dbSession->locale = $session->getOnlineAccessInfo()->getLocale();
            $dbSession->collaborator = $session->getOnlineAccessInfo()->isCollaborator();
        }
        try {
            $saved = $dbSession->save();
            Log::info('Shopify session stored', [
                'session_id' => $dbSession->session_id,
                'shop' => $dbSession->shop,
                'saved' => $saved,
            ]);
            return $saved;
        } catch (Exception $err) {
            Log::error("Failed to save session to database: " . $err->getMessage());
            return false;
        }
    }

    public function deleteSession(string $sessionId): bool
    {
        return \App\Models\ShopifySession::where('session_id', $sessionId)->update(['access_token' => null]) >= 0;
    }
}
