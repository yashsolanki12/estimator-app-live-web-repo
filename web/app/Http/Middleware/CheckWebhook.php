<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckWebhook
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info("Webhook called!");
        Log::info($_SERVER);
        $apiSecret = config('services.shopify.api_secret') ?: env('SHOPIFY_API_SECRET');
        $cleanSecret = trim(str_replace(['"', "'"], '', $apiSecret));
        
        if (!$cleanSecret) {
            Log::error("Missing Shopify API secret for webhook verification");
            abort(401, 'Missing API secret');
        }
        
        if (isset($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'])) {
            $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
            $data = file_get_contents('php://input');

            $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $cleanSecret, true));
            $verified = hash_equals($hmac_header, $calculated_hmac);
            
            // Try unprefixed secret variant (shpss_ -> shpat_)
            if (!$verified) {
                $altSecret = str_replace('shpss_', 'shpat_', $cleanSecret);
                $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $altSecret, true));
                $verified = hash_equals($hmac_header, $calculated_hmac);
            }
            
            if (!$verified) {
                Log::error("Webhook HMAC verification failed");
                abort(401, 'Invalid webhook signature');
            }
        } else {
           abort(401, 'Missing webhook signature');
        }        
        return $next($request);
    }
}