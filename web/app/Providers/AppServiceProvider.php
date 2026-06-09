<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shopify\Context;
use App\Lib\DbSessionStorage;
use Illuminate\Support\Facades\Log;

use App\Lib\Handlers\AppUninstalled;
use App\Lib\Handlers\Privacy\CustomersDataRequest;
use App\Lib\Handlers\Privacy\CustomersRedact;
use App\Lib\Handlers\Privacy\ShopRedact;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

use Shopify\ApiVersion;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;
use Illuminate\Support\Str;
use Shopify\Auth\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws \Shopify\Exception\MissingArgumentException
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $apiKey = config('services.shopify.api_key');
        $apiSecret = config('services.shopify.api_secret');
        $scopes = config('services.shopify.scopes');
        $host = config('services.shopify.host') ?: request()->getHost();


        // 1. Force MySQL Session Storage
        Context::$SESSION_STORAGE = new \App\Lib\DbSessionStorage();

        if ($apiKey && $apiSecret && $scopes && $host) {
            $host = str_replace(['https://', 'http://'], '', $host);
            
            Context::initialize(
                $apiKey,
                $apiSecret,
                $scopes,
                $host,
                Context::$SESSION_STORAGE,
                '2026-04',
                true,
                false,
                null,
                '',
                null,
                []
            );

            URL::forceRootUrl("https://$host");
            URL::forceScheme('https');

            Registry::addHandler(Topics::APP_UNINSTALLED, new AppUninstalled());

            Registry::addHandler('CUSTOMERS_DATA_REQUEST', new CustomersDataRequest());
            Registry::addHandler('CUSTOMERS_REDACT', new CustomersRedact());
            Registry::addHandler('SHOP_REDACT', new ShopRedact());
        }
    }
}
