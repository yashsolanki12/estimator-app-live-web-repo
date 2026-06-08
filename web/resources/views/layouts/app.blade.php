<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Shopify App') }}</title>

    <!-- Use the below unpkg script for timer blade file calendar not shown and for distribution check comment that both script temporary then uncomment 
         again after distribution point checked.
      -->
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script src="https://unpkg.com/@shopify/app-bridge-utils@3"></script>
   <meta name="shopify-api-key" content="{{ config('services.shopify.api_key') }}" />
   <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>

    @yield('styles')
</head>

<body>
    <ui-nav-menu>
        <a href="/" rel="home">Dashboard</a>
        <a href="/timer">Estimator</a>
        <a href="/settings">Settings</a>
        <a href="/contact_us">Contact Us</a>
        <a href="/usage_report">Usage Report</a>
        <a href="/subscription_plans">Plans</a>
    </ui-nav-menu>

    <div class="app-wrapper">
        @yield('content')
        <?php
            $redirectUri = isset($_GET['redirectUri']) ? urldecode($_GET['redirectUri']) : '';
        ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var redirectUri = "<?= htmlspecialchars($redirectUri, ENT_QUOTES, 'UTF-8') ?>";
            
            if (redirectUri) {
                window.open(redirectUri, '_top');
            }
        });
    </script>
    @yield('scripts')
    <script>
        if (window.jQuery) {
            window.jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                var shopOrigin = "{{ Auth::check() ? Auth::user()->name : request()->query('shop') }}";
                if (shopOrigin && options.url.indexOf('shop=') === -1) {
                    var separator = options.url.indexOf('?') !== -1 ? '&' : '?';
                    options.url = options.url + separator + 'shop=' + encodeURIComponent(shopOrigin);
                }
            });
        }
        
    </script>
</body>

</html>
