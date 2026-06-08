@extends('layout')

@section('content')
    @include('footer')
@endsection

@section('scripts')
@parent
<script type="text/javascript">
	  var AppBridge = window['app-bridge'];
	  var createApp = AppBridge.createApp || AppBridge.default || AppBridge;
	  var actions = AppBridge.actions;
	  var app = createApp({
		apiKey: "{{env('SHOPIFY_API_KEY', '4a8e478d7e7798129bcdbcd36aeccb4a')}}",

	    // apiKey: "{{config('services.shopify.api_key', '4a8e478d7e7798129bcdbcd36aeccb4a')}}",
	    // host: "{{request()->query('host')}}",
		shopOrigin: "{{Auth::user()->name}}"

	  });
      var ContextualSaveBar = actions.ContextualSaveBar;
	  var TitleBar = actions.TitleBar;
	  var Button = actions.Button;
	  var Toast = actions.Toast;
	  var Modal = actions.Modal;
	  var ResourcePicker = actions.ResourcePicker;
	  var Redirect = actions.Redirect;
	  var redirect = Redirect.create(app);
	 
	  var url = "{{$confirmation_url}}";

	$(document).ready( function () {
		if (url) {
            window.open(url, "_top");
		}
	});
</script>
@endsection