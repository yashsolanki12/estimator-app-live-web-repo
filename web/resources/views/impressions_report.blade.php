@extends('layout')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>

@section('content')
   <div class="container mb-4 mb-lg-5">
	    <div class="row">
			<div class="d-flex align-items-center w-100">
	    		<div class="card w-100 mb-1 mt-3 mt-lg-5">
				  	<div class="card-body">
				  		<h5 class="card-title">Product View Report</h5>
					  	<div class="row mt-0 mb-1">
				  			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				  				<div id="chart_div"></div>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2">
				 				<button type="button" id="back_button" class="btn btn-outline-main">Back</button>
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
<script>
  
    var my_2d = JSON.parse('<?= json_encode($data); ?>');
    console.log(my_2d);
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {packages: ['corechart', 'bar']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Date');
        data.addColumn('number', 'View');
        for(i = 0; i < my_2d.length; i++)
    		data.addRow([my_2d[i]['date'], parseInt(my_2d[i]['total'])]);
        var options = {
          title: 'Total Product View',
          hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0},
		  height:550,
		  width:800,
		  isStacked:true
        };
		var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);		
       }
    </script>
   <script type="text/javascript">
	try {
		var AppBridge = window['app-bridge'];
		var createApp = AppBridge && (AppBridge.default || AppBridge.createApp);
		var actions = AppBridge.actions;
		var app = window.app || createApp({
		    apiKey: "{{env('SHOPIFY_API_KEY', '4a8e478d7e7798129bcdbcd36aeccb4a')}}",
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

		contactusbutton.subscribe(Button.Action.CLICK, function() {
			app.dispatch(Redirect.toApp({path: '/contactus'}));
		});

		usagereportbutton.subscribe(Button.Action.CLICK, function() {
			app.dispatch(Redirect.toApp({path: '/usage_report'}));
		});

		var titleBarOptions = {
			title: 'Product View Report',
			buttons: {
				primary: usagereportbutton,
				secondary: [dashboardbutton, managebutton, settingsbutton, contactusbutton],
			},
		};
		var myTitleBar = TitleBar.create(app, titleBarOptions);

		document.getElementById('back_button').addEventListener('click', function () {
			if (typeof app !== 'undefined' && typeof Redirect !== 'undefined') {
				app.dispatch(Redirect.toApp({path: '/usage_report'}));
			} else {
				window.location.href = '/usage_report';
			}
		});
	} catch (error) {
		console.error('Impressions Report App Bridge setup failed.', error);
		document.getElementById('back_button').addEventListener('click', function () {
			window.location.href = '/usage_report';
		});
	}
</script>
@endsection
