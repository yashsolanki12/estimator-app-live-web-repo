@extends('layout')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<!-- Perfect-DateTimePicker CSS -->
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/media.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sweetalert2.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>



    <div class="container mb-4 mb-lg-5">
		<form id="translationssettingform" class="main-translations-setting-form" method="POST" action="{{ route('saveTranslationsSettings')}}">
		@csrf
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<div class="card border-0 mb-1 mt-4 mt-lg-5">
				  <div class="card-body p-3">
				    <h1 class="card-title card-main-title">Translations</h1>
				    <p class="card-text">Override default translations with your own.</p>
				  </div>
				</div>
				<div class="d-flex align-items-center w-100">
		    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3">
					  	<div class="card-body">
					  		<h5 class="card-title card-title-bg">Text Based Timer</h5>
						  	<div class="row mt-0 mb-1">
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="text_days" class="form-label w-100 h6">Day(s)</label>
									<input type="text" class="form-control" id="text_days" maxlength="255" name="text_days" placeholder="days" @if(isset($translationsDetails->text_days)) value="{{$translationsDetails->text_days}}" @endif>
								</div>
							  	<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="text_hours" class="form-label w-100 h6">Hours</label>
									<input type="text" class="form-control" id="text_hours" maxlength="255" name="text_hours" placeholder="hours" @if(isset($translationsDetails->text_hours)) value="{{$translationsDetails->text_hours}}" @endif>
					  			</div>
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="text_minutes" class="form-label w-100 h6">Minutes</label>
									<input type="text" class="form-control" id="text_minutes" maxlength="255" name="text_minutes" placeholder="minutes" @if(isset($translationsDetails->text_minutes)) value="{{$translationsDetails->text_minutes}}" @endif>
					  			</div>
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="text_seconds" class="form-label w-100 h6">Seconds</label>
									<input type="text" class="form-control" id="text_seconds" maxlength="255" name="text_seconds" placeholder="seconds" @if(isset($translationsDetails->text_seconds)) value="{{$translationsDetails->text_seconds}}" @endif>
					  			</div>
					  		</div>
					  	</div>
					</div>
				</div>
				<div class="d-flex align-items-center w-100">
		    		<div class="card card_box w-100 mb-2 mt-3 mt-lg-3">
					  	<div class="card-body">
					  		<h5 class="card-title card-title-bg">Visual Timer</h5>
						  	<div class="row mt-0 mb-1">
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="visual_estimated_arrival" class="form-label w-100 h6">Estimated arrival</label>
									<input type="text" class="form-control" id="visual_estimated_arrival" maxlength="255" name="visual_estimated_arrival" placeholder="Estimated arrival" @if(isset($translationsDetails->visual_estimated_arrival)) value="{{$translationsDetails->visual_estimated_arrival}}" @endif>
								</div>
							  	<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="visual_order_placed" class="form-label w-100 h6">Order placed</label>
									<input type="text" class="form-control" id="visual_order_placed" maxlength="255" name="visual_order_placed" placeholder="Order placed" @if(isset($translationsDetails->visual_order_placed)) value="{{$translationsDetails->visual_order_placed}}" @endif>
					  			</div>
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="visual_order_dispatches" class="form-label w-100 h6">Order dispatches</label>
									<input type="text" class="form-control" id="visual_order_dispatches" maxlength="255" name="visual_order_dispatches" placeholder="Order dispatches" @if(isset($translationsDetails->visual_order_dispatches)) value="{{$translationsDetails->visual_order_dispatches}}" @endif>
					  			</div>
					  			<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-2">
					  				<label for="visual_delivered" class="form-label w-100 h6">Delivered!</label>
									<input type="text" class="form-control" id="visual_delivered" maxlength="255" name="visual_delivered" placeholder="Delivered!" @if(isset($translationsDetails->visual_delivered)) value="{{$translationsDetails->visual_delivered}}" @endif>
					  			</div>
					  		</div>
					  	</div>
					</div>
				</div>
	    	</div>
	    </div>
	    <hr>
	    <div class="row">
	    	<div class="mb-3">
				<button type="submit" class="btn btn-outline-main">Save</button>
			</div>
	    </div> 
		</form>
    </div>
    @include('footer')
@endsection

@section('scripts')
    @parent
	<script type="text/javascript">

		var SettingsToast = window.Swal ? Swal.mixin({
	        toast: true,
	        position: 'top-end',
	        showConfirmButton: false,
	        timer: 5000,
	        timerProgressBar: true,
	        didOpen: (toast) => {
	          toast.addEventListener('mouseenter', Swal.stopTimer)
	          toast.addEventListener('mouseleave', Swal.resumeTimer)
	        }
	      }) : null;
		
		var redirect = null;

		try {
			var AppBridge = window['app-bridge'];
			var createApp = AppBridge && (AppBridge.default || AppBridge.createApp);
			var actions = AppBridge.actions;
			var app = window.app || createApp({
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
			  redirect = Redirect.create(app);

		  var dashboardbutton = Button.create(app, { label: 'Dashboard' });
		  var managebutton = Button.create(app, { label: 'Estimator' });
		  var settingsbutton = Button.create(app, { label: 'Settings' });
		  var contactusbutton = Button.create(app, { label: 'Contact Us' });
		  var usagereportbutton = Button.create(app, { label: 'Usage Report' });

		  	dashboardbutton.subscribe(Button.Action.CLICK, function() {
		    	app.dispatch(Redirect.toApp({path: '/'}));
		  	});

		   	managebutton.subscribe(Button.Action.CLICK, function() {
		   		app.dispatch(Redirect.toApp({path: '/timer'}));
		  	});

		   	contactusbutton.subscribe(Button.Action.CLICK, function() {
			    app.dispatch(Redirect.toApp({path: '/contactus'}));
			});

		   	usagereportbutton.subscribe(Button.Action.CLICK, function() {
			    app.dispatch(Redirect.toApp({path: '/usage_report'}));
			  });

		  // Contextual Save Bar - COMMENTED OUT
		  /*
		  const options = {
	      saveAction: {
	        disabled: false,
	        loading: false,
	      },
	      discardAction: {
	        disabled: false,
	        loading: false,
	        discardConfirmationModal: false,
	      },
	    };
	    const contextualSaveBar = ContextualSaveBar.create(app, options);

	    contextualSaveBar.subscribe(ContextualSaveBar.Action.DISCARD, function () {
	      location.reload();
	      // window.location.href = "/home/index?shop="
	      contextualSaveBar.dispatch(ContextualSaveBar.Action.HIDE);
	    });
	    contextualSaveBar.subscribe(ContextualSaveBar.Action.SAVE, function () {
	      // console.log($('#setting_ds_exclude_tags').val());
	      $('.main-translations-setting-form').trigger('submit');
	      contextualSaveBar.dispatch(ContextualSaveBar.Action.HIDE);
	    });

	    document.addEventListener('DOMContentLoaded', () => {
	      $('#translationssettingform').on('change', function() {
	        contextualSaveBar.dispatch(ContextualSaveBar.Action.SHOW);
	      });
	      $("input,textarea").keyup(function(){
	        contextualSaveBar.dispatch(ContextualSaveBar.Action.SHOW);
	      });
	    });
	    */

	  	var titleBarOptions = {
		    title: 'Settings',
		    buttons: {
		      primary: settingsbutton,
		      secondary: [dashboardbutton,managebutton,usagereportbutton,contactusbutton],
		    },
	  	};
	  	var myTitleBar = TitleBar.create(app, titleBarOptions);
	  	} catch (error) {
	  		console.error('Settings App Bridge setup failed.', error);
	  	}

	  	$(document).ready(function () {
		  	$('.main-translations-setting-form').off('submit.settings').on('submit.settings', function(e) {
		  		e.preventDefault();
		  		e.stopImmediatePropagation();

		  		var text_days 					= $('#text_days').val();
		  		var text_hours 					= $('#text_hours').val();
		  		var text_minutes 				= $('#text_minutes').val();
		  		var text_seconds 				= $('#text_seconds').val();
		  		var visual_estimated_arrival 	= $('#visual_estimated_arrival').val();
		  		var visual_order_placed 		= $('#visual_order_placed').val();
		  		var visual_order_dispatches 	= $('#visual_order_dispatches').val();
		  		var visual_delivered 			= $('#visual_delivered').val();

		  		// ajax
		    $.ajax({
		        type:"POST",
		        url: "{{ route('saveTranslationsSettings') }}",
		        data: { _token: "{{ csrf_token() }}", text_days: text_days, text_hours : text_hours, text_minutes : text_minutes, text_seconds : text_seconds, visual_estimated_arrival : visual_estimated_arrival, visual_order_placed : visual_order_placed, visual_order_dispatches : visual_order_dispatches, visual_delivered : visual_delivered },
		        dataType: 'json',
		        success: function(json_response) {
		        	if (json_response.status === true) {
			        	if (SettingsToast) {
					        	SettingsToast.fire({
		                          icon: 'success',
		                          title: 'Configuration saved!'
		                        });
		                    } else {
		                    	alert('Configuration saved!');
		                    }

			        		setTimeout(function () {
			        			location.reload(true);
			        		}, 700);
			        	}
			        },
			        error: function(xhr) {
			        	var message = 'Unable to save settings.';
			        	if (xhr.responseJSON && xhr.responseJSON.message) {
			        		message = xhr.responseJSON.message;
			        	}

			        	if (SettingsToast) {
			        		SettingsToast.fire({
			        			icon: 'error',
			        			title: message
			        		});
			        	} else {
			        		alert(message);
			        	}
			        }
		    });

		    	return false;
		  	});
		});

		function goto(url) {
			if (app && Redirect) {
		    	app.dispatch(Redirect.toApp({path: url}));
		    } else {
		    	window.location.href = url;
		    }
		}
	</script>
    <script>
        
    </script>
@endsection
