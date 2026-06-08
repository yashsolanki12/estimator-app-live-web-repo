@extends('layout')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>


@section('content')
   <div class="container mb-4 mb-lg-5">
		<form id="contactus-form" method="POST" action="{{ route('contactus')}}" autocomplete="on" novalidate>
		@csrf
	    <div class="row">
			<div class="d-flex align-items-center w-100">
	    		<div class="card w-100 mb-1 mt-3 mt-lg-5">
				  	<div class="card-body">
				  		<h5 class="card-title">Contact Us Form</h5>
					  	<div class="row mt-0 mb-1">
				  			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<label for="name" class="form-label w-100 h6">Your name (*)</label>
								<input type="text" class="form-control" aria-describedby="inputName" id="name" maxlength="255" name="name" placeholder="Your name" required>
								<div id="inputName" class="invalid-feedback">Please enter your name.</div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<label for="email" class="form-label w-100 h6">Email address (*)</label>
								<input type="email" class="form-control" aria-describedby="inputEmail" id="email" maxlength="255" name="email" placeholder="Email address" required>
								<div id="inputEmail" class="invalid-feedback">Please enter your valid email address.</div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<label for="website" class="form-label w-100 h6">Your website (*)</label>
								<input type="text" class="form-control" autocomplete="on" aria-describedby="inputWebsite" id="website" maxlength="255" name="website" placeholder="Your website" required>
								<div id="inputWebsite" class="invalid-feedback">Please enter your website.</div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<label for="message" class="form-label w-100 h6">Write your message below (*)</label>
				  				<textarea class="form-control" rows="4" aria-describedby="inputMessage" id="message" maxlength="65000" name="message" placeholder="Write your message" required></textarea>
								<div id="inputMessage" class="invalid-feedback">Please enter your message.</div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
								<div class="d-flex w-100 mt-2">
									<button type="submit" class="btn btn-outline-main">Submit</button>
								</div>
							</div>
				  		</div>
				  	</div>
				</div>
			</div>
	    </div>
		</form>
    </div>
    @include('footer')
@endsection

@section('scripts')
@parent
<script type="text/javascript">
	try {
	  var AppBridge = window['app-bridge'];
	  var createApp = AppBridge && (AppBridge.default || AppBridge.createApp);
	  var actions = AppBridge.actions;
	  var app = window.app || createApp({
	    apiKey: "{{env('SHOPIFY_API_KEY', '18406be3958ef2225422b7f8eecc804d')}}",
	    shopOrigin: "{{Auth::user()->name}}",
	    host: "{{ request()->query('host') }}"
	  });

	  var ContextualSaveBar = actions.ContextualSaveBar;
	  var TitleBar = actions.TitleBar;
	  var Button = actions.Button;
	  var Toast = actions.Toast;
	  var Modal = actions.Modal;
	  var ResourcePicker = actions.ResourcePicker;
	  var Redirect = actions.Redirect;
	  var redirect = Redirect.create(app);

	  var settingsbutton = Button.create(app, { label: 'Settings' });
	  var managebutton = Button.create(app, { label: 'Estimator' });
	  var dashboardbutton = Button.create(app, { label: 'Dashboard' });
	  var contactusbutton = Button.create(app, { label: 'Contact Us' });
	  var usagereportbutton = Button.create(app, { label: 'Usage Report' });

	  settingsbutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/settings'}));
	  });

	  managebutton.subscribe(Button.Action.CLICK, function() {
	    app.dispatch(Redirect.toApp({path: '/timer'}));
	  });

	   	dashboardbutton.subscribe(Button.Action.CLICK, function() {
		    app.dispatch(Redirect.toApp({path: '/'}));
		});

	  usagereportbutton.subscribe(Button.Action.CLICK, function() {
		    app.dispatch(Redirect.toApp({path: '/usage_report'}));
		  });


	  var titleBarOptions = {
	    title: 'Contact Us',
	    buttons: {
	      primary: contactusbutton,
	      secondary: [dashboardbutton,managebutton,settingsbutton,usagereportbutton],
	    },
	  };
	  var myTitleBar = TitleBar.create(app, titleBarOptions);
	} catch (error) {
	  console.error('Contact Us App Bridge setup failed.', error);
	}

	 var Toastr = window.Swal ? Swal.mixin({
	        toast: true,
	        position: 'top-end',
	        showConfirmButton: false,
	        timer: 5000,
	        timerProgressBar: true,
	        didOpen: (toast) => {
	          toast.addEventListener('mouseenter', Swal.stopTimer)
	          toast.addEventListener('mouseleave', Swal.resumeTimer)
	        }
	      })
	      : null;

	@php
	    $userId = Auth::check() ? Auth::user()->id : 0;
	@endphp

	function showContactToast(type, message) {
		if (Toastr) {
			Toastr.fire({
				icon: type,
				title: message
			});

			return;
		}

		alert(message);
	}

	function setFieldState(field, isValid) {
		var input = $('#' + field);

		input.toggleClass('is-invalid', !isValid);
		input.toggleClass('is-valid', isValid);
	}

	function validateContactForm() {
		var emailformat = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z]{2,})+$/;
		var valid = true;

		var nameValid = $.trim($('#name').val()) !== '';
		setFieldState('name', nameValid);
		valid = valid && nameValid;

		var emailValid = emailformat.test($.trim($('#email').val()));
		setFieldState('email', emailValid);
		valid = valid && emailValid;

		var websiteValid = $.trim($('#website').val()) !== '';
		setFieldState('website', websiteValid);
		valid = valid && websiteValid;

		var messageValid = $.trim($('#message').val()) !== '';
		setFieldState('message', messageValid);
		valid = valid && messageValid;

		return valid;
	}

	$(document).ready(function () {
		var form = $('#contactus-form');
		var submitButton = form.find('button[type="submit"]');
		var fields = '#name, #email, #website, #message';
		var isSubmitting = false;

		form.off('input.contactus change.contactus', fields);
		form.on('input.contactus change.contactus', fields, function () {
			$(this).removeClass('is-invalid is-valid');
		});

		form.off('submit.contactus');
		form.on('submit.contactus', function (e) {
			e.preventDefault();
			e.stopImmediatePropagation();

			if (isSubmitting) {
				return false;
			}

			if (!validateContactForm()) {
				return false;
			}

			isSubmitting = true;
			submitButton.prop('disabled', true);

			$.ajax({
				type: 'POST',
				url: "{{ route('contactus') }}",
				data: {
					_token: "{{ csrf_token() }}",
					@if($userId)
					user_store_id: "{{ $userId }}",
					@endif
					name: $.trim($('#name').val()),
					email: $.trim($('#email').val()),
					website: $.trim($('#website').val()),
					message: $.trim($('#message').val())
				},
				dataType: 'json',
				success: function (json_response) {
					if (json_response.status === true) {
						showContactToast('success', 'Your request sent to support team successfully.');
						form.find(fields).val('').removeClass('is-invalid is-valid');
					} else {
						showContactToast('error', 'Unable to submit your request. Please try again.');
					}
				},
				error: function (xhr) {
					if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
						$.each(xhr.responseJSON.errors, function (field) {
							setFieldState(field, false);
						});
						return;
					}

					showContactToast('error', 'Unable to submit your request. Please try again.');
				},
				complete: function () {
					isSubmitting = false;
					submitButton.prop('disabled', false);
				}
			});

			return false;
		});
	});
</script>
@endsection
