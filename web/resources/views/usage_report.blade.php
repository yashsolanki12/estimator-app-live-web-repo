@extends('layout')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0yZ1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/media.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css"/>

<style>
	.sorting_1 {
		padding-top: 0 !important;
		padding-bottom: 0 !important;
	}
	td {
		padding-top: 4 !important;
		padding-bottom: 4 !important;
	}
	.form-control-sm {
		width: 190px !important;

	}
</style>

@section('content')
   <div class="container mb-4 mb-lg-5">
	    <div class="row">
			<div class="d-flex align-items-center w-100">
	    		<div class="card w-100 mb-1 mt-3 mt-lg-5">
				 	<div class="card-body">
				 		<h5 class="card-title mb-3">Usage Report</h5>
				 		<div class="row mb-3">
				 			<div class="col-md-6"></div>
				 			<div>
				 				<div class="d-flex">
				 					<div class="dataTables_filter">
				 						<label>Search: <input type="search" id="custom_search" class="form-control form-control-sm" placeholder="Product name (3+ chars)" aria-controls="report_table"></label>
				 					</div>
				 				</div>
				 			</div>
				 		</div>
					  	<div class="row mt-0 mb-1">
					  		<div class="table-responsive">
					  		<table class="table table-bordered table-hover align-middle w-100" id="report_table">
					            <thead>
					                <tr>
					                    <th>Product Name</th>
					                    <th>Total</th>
					                    <th>Action</th>
					                </tr>
					            </thead>
					        </table>
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
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
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

		var TitleBar = actions.TitleBar;
		var Button = actions.Button;
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

		var titleBarOptions = {
			title: 'Usage Report',
			buttons: {
				primary: usagereportbutton,
				secondary: [dashboardbutton, managebutton, settingsbutton, contactusbutton],
			},
		};
		var myTitleBar = TitleBar.create(app, titleBarOptions);
	} catch (error) {
		console.error('Usage Report App Bridge setup failed.', error);
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
	      }) : null;

	function showToast(icon, title) {
	    if (Toastr) {
	        Toastr.fire({ icon: icon, title: title });
	    }
	}

	function debounce(fn, delay) {
		var timer = null;
		return function () {
			var context = this;
			var args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(context, args);
			}, delay);
		};
	}

	$(document).ready(function () {
		if (window.__usageReportTableInitialized) {
			return;
		}
		window.__usageReportTableInitialized = true;

		if ($.fn.DataTable.isDataTable('#report_table')) {
			$('#report_table').DataTable().destroy();
		}

	    var table = $('#report_table').DataTable({
	    	processing: true,
	    	serverSide: true,
	    	searching: false, // Completely disable DataTables search
	    	responsive: true,
	    	dom: 'lrtip', // Remove search box from DataTables DOM
	    	ajax: {
		      method: 'POST',
		      url: '{{ route("getProducts") }}',
		      data: function (d) {
		          d.shop_name = "{{Auth::user()->name}}";
		          d._token = "{{ csrf_token() }}";
		          // Add custom search parameter - create search object if it doesn't exist
		          if (!d.search) {
		              d.search = {};
		          }
		          d.search.value = $('#custom_search').val() || '';
		      },
		      error: function(xhr, error, thrown) {
		          console.error('DataTable AJAX Error:', error, thrown);
		          console.error('Response:', xhr.responseText);
		          console.error('Status:', xhr.status);
		          showToast('error', 'Failed to load usage data: ' + error);
		      }
		    },
	    	columns: [
	            { data: 'product_name', name: 'product_name' },
	            { data: 'total', name: 'total', searchable: false },
	            { data: 'action', orderable: false, searchable: false }
	        ],
	        language: {
	            emptyTable: "No usage data found for this month",
	            zeroRecords: "No matching records found",
	            processing: "Loading usage data..."
	        }
	    });

	    // Custom search functionality
	    var searchTimeout;
	    $('#custom_search').on('input', function() {
	    	clearTimeout(searchTimeout);
	    	var searchValue = $(this).val();
	    	
	    	searchTimeout = setTimeout(function() {
	    		if (searchValue.length === 0 || searchValue.length > 3) {
	    			table.ajax.reload();
	    		}
	    	}, 400);
	    });

	    table.on('xhr.dt', function (e, settings, json) {
	        if (json && Array.isArray(json.data) && json.data.length === 0) {
	            showToast('info', 'No usage data found for this month');
	        }
	    });

	    table.on('draw.dt', function () {
	    	setTimeout(function () {
	    		window.scrollTo({ top: 0, behavior: 'smooth' });
	    		document.documentElement.scrollTop = 0;
	    		document.body.scrollTop = 0;
	    	}, 0);
	    });

	    table.on('error.dt', function (e, settings, techNote, message) {
	        console.error('DataTable Error:', message);
	        showToast('error', message);
	    });
	    
	    $(document).on('click', 'button.view_chart', function() {
			var product_id = $(this).attr('data-product-id');
			if (typeof app !== 'undefined' && typeof Redirect !== 'undefined') {
				app.dispatch(Redirect.toApp({path: '/impression_report/' + product_id}));
			} else {
				window.location.href = '/impression_report/' + product_id;
			}
		});
	});
</script>
@endsection
