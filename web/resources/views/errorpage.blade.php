@extends('layout')

@section('content')
   <div class="container mb-4 mb-lg-5">
	    <div class="row">
			<div class="d-flex align-items-center w-100">
	    		<div class="card w-100 mb-1 mt-3 mt-lg-5">
				  	<div class="card-body">
					  	<div class="row mt-0 mb-1">
				  			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<h5 style="color: #cb0000;">Oops! Something Went Wrong.</h5>
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
<script type="text/javascript">
	  var AppBridge = window['app-bridge'];
	  var createApp = AppBridge.createApp;
	  var actions = AppBridge.actions;
	  var app = createApp({
	    apiKey: "{{env('SHOPIFY_API_KEY', '4a8e478d7e7798129bcdbcd36aeccb4a')}}",
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

	    contactusbutton.subscribe(Button.Action.CLICK, function() {
		    app.dispatch(Redirect.toApp({path: '/contactus'}));
		});

	    usagereportbutton.subscribe(Button.Action.CLICK, function() {
		    app.dispatch(Redirect.toApp({path: '/usage_report'}));
		  });
	  var titleBarOptions = {
	    title: 'Error Page',
	    buttons: {
	      secondary: [dashboardbutton,managebutton,settingsbutton,usagereportbutton,contactusbutton],
	    },
	  };
	  var myTitleBar = TitleBar.create(app, titleBarOptions);

	 const Toastr = Swal.mixin({
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
</script>
@endsection