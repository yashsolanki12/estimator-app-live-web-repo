<?php

declare(strict_types=1);

namespace App\Lib\Handlers;

use Illuminate\Support\Facades\Log;
use Shopify\Webhooks\Handler;

class AppUninstalled implements Handler
{
    public function handle(string $topic, string $shop, array $body): void
    {
        Log::debug("App was uninstalled from $shop - clearing access token");
        
        // Keep the shop row but clear every token for this shop so reinstall can repopulate it.
        \App\Models\ShopifySession::where('shop', $shop)->update(['access_token' => null]);
        
        // Clear table entry when app uninstalled
        $userStore = \App\Models\User::where('name', $shop)->first();
        if ($userStore) {
            \App\Models\RecurringApplicationPlans::where('user_store_id', $userStore->id)->delete();
        }

        Log::info("[uninstallCleanup] Access token nulled for shop: $shop");
    }
}
