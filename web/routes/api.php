<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shopify\Clients\HttpHeaders;
use Shopify\Webhooks\Registry;
use Shopify\Exception\InvalidWebhookException;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return "Hello API";
});

// Route::post('/webhooks', function (Request $request) {
//     try {
//         $topic = $request->header('X-Shopify-Topic', '');

//         $response = Registry::process($request->header(), $request->getContent());
//         if (!$response->isSuccess()) {
//             Log::error("Failed to process '$topic' webhook: {$response->getErrorMessage()}");
//             return response()->json(['message' => "Failed to process '$topic' webhook"], 500);
//         }
//     } catch (InvalidWebhookException $e) {
//         Log::error("Got invalid webhook request for topic '$topic': {$e->getMessage()}");
//         return response()->json(['message' => "Got invalid webhook request for topic '$topic'"], 401);
//     } catch (\Exception $e) {
//         Log::error("Got an exception when handling '$topic' webhook: {$e->getMessage()}");
//         return response()->json(['message' => "Got an exception when handling '$topic' webhook"], 500);
//     }
// });

Route::post('/webhooks', function (Request $request) {
    Log::info("[Webhook] Received request on /api/webhooks");
    
    $topic = $request->header('X-Shopify-Topic', '');
    $hmacHeader = $request->header('X-Shopify-Hmac-Sha256', '');
    $shopHeader = $request->header('X-Shopify-Shop-Domain', '');

    Log::info("[Webhook] Topic: $topic, Shop: $shopHeader");

    // 1. Get and clean the secret (mimicking Node.js logic)
    $rawSecret = config('services.shopify.api_secret') ?: '';
    $cleanSecret = trim(str_replace(['"', "'"], '', $rawSecret));

    if (empty($cleanSecret)) {
        Log::error("[Webhook] SHOPIFY_API_SECRET is missing!");
        return response()->json(['success' => false, 'message' => 'Missing Secret'], 500);
    }

    $body = file_get_contents('php://input');
    if (empty($body)) {
        Log::error("[Webhook] Body is empty from php://input.");
        return response()->json(['success' => false, 'message' => 'Empty Body'], 400);
    }

    $bodyMd5 = md5($body);
    Log::info("[Webhook] Body MD5: " . $bodyMd5);

    // 2. Try variants (mimicking Node.js logic)
    $variants = [
        $cleanSecret,
        str_replace('shpss_', '', $cleanSecret)
    ];

    $verified = false;
    $bodySnippet = substr($body, 0, 100);
    Log::info("[Webhook] Body Snippet (100 chars): " . $bodySnippet);
    Log::info("[Webhook] Secret Variant 0 (Masked): " . substr($variants[0], 0, 5) . "..." . substr($variants[0], -4));

    foreach ($variants as $index => $secret) {
        $calculatedHmac = base64_encode(hash_hmac('sha256', $body, $secret, true));
        if (hash_equals($hmacHeader, $calculatedHmac)) {
            $verified = true;
            break;
        }
    }

    if (!$verified) {
        Log::error("[Webhook] HMAC MISMATCH!");
        Log::error("[Webhook] Received Header: $hmacHeader");
        // We log the calculated one for the first variant for debugging
        $debugHmac = base64_encode(hash_hmac('sha256', $body, $variants[0], true));
        Log::error("[Webhook] Calculated (Variant 0): $debugHmac");
        
        return response()->json(['success' => false, 'message' => 'HMAC validation failed'], 401);
    }

    Log::info("[Webhook] ✅ HMAC Verified Successfully!");

    try {
        $payload = json_decode($body, true);
        $shop = $shopHeader ?: ($payload['myshopify_domain'] ?? ($payload['shop'] ?? ''));

        if ($topic === 'app/uninstalled') {
            Log::info("[Webhook] Processing uninstall for: $shop");
            
            // Call the handler logic
            $handler = new \App\Lib\Handlers\AppUninstalled();
            $handler->handle($topic, $shop, $payload);
            
            return response()->json(['success' => true, 'message' => 'Uninstall Processed']);
        }
    } catch (\Exception $e) {
        Log::error("[Webhook] Parse or Handler error: " . $e->getMessage());
    }

    return response()->json(['success' => true, 'message' => 'Received']);
});
