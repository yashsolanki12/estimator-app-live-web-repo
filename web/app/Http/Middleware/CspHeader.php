<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Shopify\Context;
use Shopify\Utils;

class CspHeader
{
    public function handle(Request $request, Closure $next)
    {
        $shopParam = $request->query('shop');
        $shop = $shopParam && is_string($shopParam) && strlen($shopParam) > 0 
            ? Utils::sanitizeShopDomain($shopParam) 
            : null;

        if (Context::$IS_EMBEDDED_APP) {
            $domainHost = $shop ? "https://$shop" : "*.myshopify.com";
            $allowedDomains = "$domainHost https://admin.shopify.com";
        } else {
            $allowedDomains = "'none'";
        }

        $response = $next($request);

        $currentHeader = $response->headers->get('Content-Security-Policy');
        if ($currentHeader) {
            $values = preg_split("/;\s*/", $currentHeader);

            $found = false;
            foreach ($values as $index => $value) {
                if (mb_strpos($value, "frame-ancestors") === 0) {
                    $values[$index] = preg_replace("/^(frame-ancestors)/", "$1 $allowedDomains", $value);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $values[] = "frame-ancestors $allowedDomains";
            }

            $headerValue = implode("; ", $values);
        } else {
            $headerValue = "frame-ancestors $allowedDomains;";
        }

        $response->headers->set('Content-Security-Policy', $headerValue);

        return $response;
    }
}