@extends('layouts.app')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/media.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>


@section('content')
    <!-- You are: (shop domain name) -->
    {{-- <p>You are: {{ $shopDomain ?? Auth::user()->name }}</p> --}}
    <div class="container mb-4 mb-lg-5">
    	<div class="row">
    		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
    			<div class="card border-0 mb-1 mt-4 mt-lg-5">
				  <div class="card-body p-3">
				    <h1 class="card-title card-main-title">Welcome to Delivery Estimator</h1>
				    <p class="card-text mt-2">Delivery Estimator allows you to show a handy timer on your product pages. This is a great way to create urgency and increase conversions. It is also helpful to customers who seek this information and may otherwise not purchase without it.
				  </div>
				</div>
    		</div>
    	</div>
		@php
	        $appEmbedStatusValue = $appEmbedStatus['status'] ?? ($appEmbedStatus['enabled'] ?? false ? 'enabled' : 'disabled');
	        $appEmbedEnabled = $appEmbedStatus['enabled'] ?? false;
	        $themeEditorUrl = $appEmbedStatus['theme_editor_url'] ?? null;
	        $appEmbedError = $appEmbedStatus['error'] ?? null;
	    @endphp
	    @if($appEmbedStatusValue !== 'enabled')
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3 border-warning">
				  <div class="card-body">
				  	<div class="card border-0 mb-1 mt-0">
					  <div class="card-body p-0">
					    <h5 class="card-title card-title-bg">App Embed</h5>
					  </div>
					</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				    		@if($appEmbedStatusValue === 'disabled')
				    			<p class="card-text mb-2">The app embed is currently disabled in your active theme.</p>
				    			<p class="card-text mb-0">Enable it in the Shopify theme editor so the app can load on storefront pages.</p>
				    		@else
				    			<p class="card-text mb-2">{{ $appEmbedError ?: 'Cannot detect the app embed status because the token or theme could not be read.' }}</p>
				    		@endif
				    		@if($appEmbedStatusValue === 'unavailable' && $appEmbedError)
				    			<p class="card-text mb-0 mt-2"><small class="text-muted">{{ $appEmbedError }}</small></p>
				    		@endif
				  		</div>
				  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
				            @if($themeEditorUrl)
				            	<a class="text-decoration-none" href="{{ $themeEditorUrl }}" target="_top">
				            		<button type="button" class="btn btn-outline-main">Open Theme Editor</button>
				            	</a>
				            @endif
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div>
	    @endif
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3">
				  <div class="card-body">
				  	<div class="card border-0 mb-1 mt-0">
				  <div class="card-body p-0">
				    <h5 class="card-title card-title-bg">Default Estimator</h5>
				  </div>
				</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				  			 <p class="card-text mb-2">Manage the estimator that is shown by default across all product pages. You can also hide this on specific products.</p>
				    		<p class="card-text">Setup and manage the default estimator.</p>
				  		</div>
				  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
			    			<button type="button" onclick="goto('/timer')" class="btn btn-outline-main">Estimator</button>
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3">
				  <div class="card-body">
				  	<div class="card border-0 mb-1 mt-0">
					  <div class="card-body p-0">
					    <h5 class="card-title card-title-bg">Usage</h5>
					  </div>
					</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				  			<p class="card-text mb-2">Current months usage</p>
				    		<p class="card-text"><b>{{$total}}</b> impressions in this current month cycle.</p>
				  		</div>
				  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
                   			<button type="button" onclick="goto('/usage_report')" class="btn btn-outline-main">Usage Report</button>
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3">
				  <div class="card-body">
				  	<div class="card border-0 mb-1 mt-0">
					  <div class="card-body p-0">
					    <h5 class="card-title card-title-bg">Pricing Plan</h5>
					  </div>
					</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				    		<p class="card-text mb-0">Your Current Plan is <b>@if(isset($currentPlan->short_description)) {{ $currentPlan->name }} @endif </b></p>
				    		<p class="card-text mb-0">@if(isset($currentPlan->short_description)) {{ $currentPlan->short_description }} @endif</p>
				    		@if($limitExceeded == 1)
			    				<p class="card-text mb-0"><b>Your @if(isset($currentPlan->name)) {{ $currentPlan->name }} @endif plan limit reached out, please upgrade your plan.</b></p>
			    			@endif
				  		</div>
				  			<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
                   			<button type="button" onclick="goto('/subscription_plans')" class="btn btn-outline-main">Plan Upgrade</button>
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div>
	    {{-- <hr>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card border-0 mb-1 mt-0">
				  <div class="card-body p-0">
				    <h5 class="card-title card-title-bg">Knowledge Base</h5>
				  </div>
				</div>
	    	</div>
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card w-100 mb-2 mt-2">
				  <div class="card-body">
				  	<div class="card border-0 mb-1 mt-0">
				  <div class="card-body p-0">
				    <h5 class="card-title card-title-bg">Knowledge Base</h5>
				  </div>
				</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				  			<p class="card-text mb-2">Setup and troubleshooting guides</p>
				    		<p class="card-text">Most merchants queries regarding setup or troubleshooting Delivery Estimator can be resolved by checking the Knowledge Base.</p>
				  		</div>
				  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
			    			<a href="#" target="_blank" type="button" class="text-decoration-none">
		                      <button type="button" class="btn btn-outline-main">View</button>
		                    </a>
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div> --}}
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="d-flex align-items-center w-100 h-100">
	    		<div class="card card_box w-100 mb-1 mt-3 mt-lg-3">
				  <div class="card-body">
				  	<div class="card  border-0 mb-1 mt-0">
				  <div class="card-body p-0">
				    <h5 class="card-title card-title-bg">Contact Us</h5>
				  </div>
				</div>
				  	<div class="row align-items-center">
				  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
				    		If you still are having issues with Delivery Estimator and have checked the Knowledge Base, you may request support.
				  		</div>
				  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
		                    <button type="button" onclick="goto('/contact_us')" class="btn btn-outline-main">Contact Us</button>
				  		</div>
				  	</div>
				  </div>
				</div>
				</div>
	    	</div>
	    </div>
    </div>
@include('footer')
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

	<script type="text/javascript">
	  var AppBridge = window['app-bridge'];
	  var createApp = AppBridge.createApp;
	  var actions = AppBridge.actions;
	  var app = createApp({
	    apiKey: "{{config('services.shopify.api_key')}}",
	    host: "{{request()->query('host')}}"
	  });

	  var ContextualSaveBar = actions.ContextualSaveBar;
	  var TitleBar = actions.TitleBar;
	  var Button = actions.Button;
	  var Toast = actions.Toast;
	  var Modal = actions.Modal;
	  var ResourcePicker = actions.ResourcePicker;
	  var Redirect = actions.Redirect;
	  var redirect = Redirect.create(app);

	  var contactusbutton = Button.create(app, { label: 'Contact Us' });
	  var settingsbutton = Button.create(app, { label: 'Settings' });
	  var managebutton = Button.create(app, { label: 'Estimator' });
	  var dashboardbutton = Button.create(app, { label: 'Dashboard' });
	  var usagereportbutton = Button.create(app, { label: 'Usage Report' });

	  contactusbutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/contact_us'}));
	  });

	  settingsbutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/settings'}));
	  });

	  managebutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/timer'}));
	  });

	  usagereportbutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/usage_report'}));
	  });

	  var titleBarOptions = {
	    title: 'Dashboard',
	    buttons: {
	      primary: dashboardbutton,
	      secondary: [managebutton,settingsbutton,usagereportbutton,contactusbutton],
	    },
	  };
	  var myTitleBar = TitleBar.create(app, titleBarOptions);
	</script>

	<script type="text/javascript">
		
	  function goto(url){
	     app.dispatch(Redirect.toApp({path: url}));
	  }
	</script>
@endsection