<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use App\Lib\Handlers\AppUninstalled;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());

        $response = Registry::process($request->header(), $request->getContent());

        if ($response->isSuccess()) {
            return response()->json(['message' => 'Webhook processed'], 200);
        } else {
            return response()->json(['error' => $response->getErrorMessage()], 500);
        }
    }
}
