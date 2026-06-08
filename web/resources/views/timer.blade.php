@extends('layouts.app')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify@3/dist/tagify.css" rel="stylesheet" type="text/css" />


<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/jquery.datetimepicker.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/colorpicker/coloris.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/sweetalert2.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/checkbox.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/fontawesome-5.15.4.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.min.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/media.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom_copy.css') }}"/>


<!-- App Bridge is already loaded in layouts/app.blade.php -->
    <div class="container">
		<form id="settingform" class="main-setting-form" method="POST">
		@csrf
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card border-0 mb-1 mt-3 mt-lg-5">
				  <div class="card-body p-3">
				    <h5 class="card-title card-main-title">Status</h5>
				  </div>
				</div>
	    	</div>
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
				<div class="d-flex align-items-center w-100 h-100">
		    		<div class="card card_box w-100 mb-2 mt-1 mt-lg-1">
					  	<div class="card-body">
						  	<div class="row align-items-center">
						  	<?php
                                // ✅ FIXED: Removed $redirectUri = urldecode($_GET['redirectUri']) — not needed here
                                $enabled = true;
                                if (isset($timerDetails->status) && $timerDetails->status == 0) {
                                    $enabled = false;
                                }
                            ?>
						  		<div class="col-12 col-sm-8 col-md-9 col-lg-9 col-xl-9 col-xxl-10">
						  			<p class="card-text mb-2">Control the visibility of Delivery Estimator on your store.</p>
						    		<p class="card-text">Delivery Estimator is currently <b>@if($enabled) Enabled @else Disabled @endif </b>on your store.</p>
						  		</div>
						  		<div class="col-12 col-sm-4 col-md-3 col-lg-3 col-xl-3 col-xxl-2 d-flex align-items-center justify-content-start justify-content-sm-end pt-3 pt-sm-0">
						  			@if($enabled)
					    				<button type="button" class="btn btn-outline-main disable_timer">Disable</button>
					    			@else
					    				<button type="button" class="btn btn-enable enable_timer">Enable</button>
					    			@endif
						  		</div>
						  	</div>
					  	</div>
					</div>
				</div>
	    	</div>
	    </div>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
			<div class="card border-0 mb-2 mt-3 mt-lg-3">
				  <div class="card-body p-3">
				    <h5 class="card-title card-main-title">Timer</h5>
				    <p class="card-text">Manage how the default timer will display on your store including the countdown and estimated date.</p>
				  </div>
				</div>
	    	</div>
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="align-items-center w-100">
		    		<div class="card card_box w-100 mb-2 mt-2 mt-lg-2">
						<div class="card-body pb-0">
						  	<h5 class="card-title card-title-bg">Configuration</h5>
						  	<label for="basic-url" class="form-label d-flex h6 mt-3">Timer Visibility</label>
						  	<?php 
						  		$weekDays = config('api.week_days', ['Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday']);
						  		$timerVisibility = [];
						  		if(isset($timerDetails->timer_visibility) && $timerDetails->timer_visibility) {
						  			$timerVisibility = explode(',', $timerDetails->timer_visibility);
						  		}
						  	?>
						  	@foreach($weekDays as $key => $days)
						  	
							  	<div class="form-check form-check-inline mb-1 ps-0 form_check_timer_visibility">
									<div class="form-check form-switch">
											<input class="form-check-input timer_visibility" type="checkbox" name="timer_visibility" role="switch" id="day{{$key}}" value="{{$key}}" @if($timerVisibility && in_array($key, $timerVisibility)) checked @endif>
					  							<label class="form-check-label" for="day{{$key}}">{{$days}}</label>
									</div>
								</div>
							@endforeach
							<div class="mb-3 mt-1">
								<label for="exampleFormControlTextarea1" class="form-label h6">Select Timezone</label>
								<select class="form-select" id="timezone" name="timezone" aria-label="Default select example">
									@foreach($timezones ?? [] as $tValue)
								  		<option value="{{ $tValue['value'] }}" @if(isset($timerDetails->timezone) && $timerDetails->timezone == $tValue['value']) selected @endif>{{ $tValue['label'] }}</option>
								  	@endforeach
								</select>
							</div>
							<div class="row mt-0">
								<label class="form-label h6">Hide Timer</label>
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 col-xxl-4 mb-3">
									<div id="myDatePicker"></div>
								</div>
								<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 col-xxl-4 mb-3">
									<div id="test" class="ex3 w-100 tablecustom_box">
										<table class="table  table_custom">
											<thead>
												<tr>
													<th scope="col">Date</th>
											        <th class="trash_col" scope="col">Action</th>
												</tr>
											</thead>
											<tbody id="disable_dates_body">
												@foreach($disableDates ?? [] as $dValue)
													<tr>
														<td>{{$dValue['date'] ?? ''}}</td>
														<td class="trash_col"><span class="delete_dates" data-id="{{$dValue['id'] ?? ''}}"><i class='fa fa-trash-alt'></i></span></td>
													</tr>
												@endforeach
											</tbody>
										</table>
				                	</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="align-items-center w-100">
		    		<div class="card card_box mb-2 mt-4 mt-lg-4">
						<div class="card-body">
							<h5 class="card-title card-title-bg">Delivery Date</h5>
							<!-- delivery tabs content -->
									<div>
									  	<div class="row">
									  		<p class="mt-1">This is for use with [deliverydate] in the message below. You could use this if you wanted to offer an estimated delivery date to the customer.</p>
									  	</div>
									  	<div class="row">
									  		<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4 mb-2">
									  			<label for="delivery_lead_time" class="form-label h6">Delivery Lead Time</label>
									  			<div class="row">
									  				<div class="col-9 col-sm-9 col-md-9 col-lg-9 col-xl-9 col-xxl-9 mb-2">
									  					<input type="text" pattern="\d*" maxlength="3" class="form-control number_input" id="delivery_lead_time" name="delivery_lead_time" @if(isset($timerDetails->delivery_lead_time)) value="{{$timerDetails->delivery_lead_time}}" @endif>
									  				</div>
									  				<div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3 col-xxl-3 mb-2 d-flex align-items-center ps-0">
									  					<p class="m-0">Day(s)</p>
									  				</div>
									  			</div>
									  		</div>
									  		<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4 mb-2">
									  			<div>
													<div>
													<div class="form-check mt-0 mb-0 ps-0 form-check_range">
														<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox" name="enable_delivery_add_range" role="switch" id="enable_delivery_add_range" value="1"  @if(isset($timerDetails->enable_delivery_add_range) && $timerDetails->enable_delivery_add_range == "1") checked @endif>
										  					<label class="form-check-label h6 mb-0" for="enable_delivery_add_range">Add Range <a class="tip" href="JavaScript:void(0);" data-bs-toggle="tooltip" title="Used to show Range for Delivery date for Both Timers."><i class='fas fa-question'></i></a></label>
														</div>
														</div>
													</div>
													<div id="day_range" class="@if(isset($timerDetails->enable_delivery_add_range) && $timerDetails->enable_delivery_add_range == "1") @else d-none @endif">
														<div class="row">
											  				<div class="col-9 col-sm-9 col-md-9 col-lg-9 col-xl-9 col-xxl-9 mb-2">
																<input type="text" pattern="\d*" maxlength="3" class="form-control number_input" id="delivery_range_days" name="delivery_range_days" @if(isset($timerDetails->delivery_range_days)) value="{{$timerDetails->delivery_range_days}}" @endif>
											  				</div>
											  				<div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3 col-xxl-3 mb-2 d-flex align-items-center ps-0">
											  					<p class="m-0">Day(s)</p>
											  				</div>
											  			</div>
													</div>
												</div>
									  		</div>
									  		<div class="col-12 col-sm-12 col-md-12 col-lg-4 col-xl-4 col-xxl-4 mb-2">
									  			<label for="delivery_handling_time" class="form-label h6">Handling Time <a class="tip" href="JavaScript:void(0);" data-bs-toggle="tooltip" title="This will apply only on Visual Timer."><i class='fas fa-question'></i></a> (Optional)</label>
									  			<div class="row">
									  				<div class="col-9 col-sm-9 col-md-9 col-lg-9 col-xl-9 col-xxl-9 mb-2">
														<input type="text" pattern="\d*" maxlength="3" class="form-control number_input" id="delivery_handling_time" name="delivery_handling_time" @if(isset($timerDetails->delivery_handling_time)) value="{{$timerDetails->delivery_handling_time}}" @endif>
									  				</div>
									  				<div class="col-3 col-sm-3 col-md-3 col-lg-3 col-xl-3 col-xxl-3 mb-2 d-flex align-items-center ps-0">
									  					<p class="m-0">Day(s)</p>
									  				</div>
									  			</div>
									  		</div>
									  	</div>
									  	<div class="row">
									  		<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-1 mb-2">
											  	<div class="form-check mt-1 mb-0 ps-0">
													<div class="form-check form-switch">
															<input class="form-check-input" type="checkbox" name="enable_dispatch_days" role="switch" id="enable_dispatch_days" value="1"  @if(isset($timerDetails->enable_dispatch_days) && $timerDetails->enable_dispatch_days == "0") @else checked @endif>
									  							<label class="form-check-label" for="enable_dispatch_days{{$key}}"> Dispatch Days?</label>
													</div>
												</div>
											</div>
									  	</div>
									  	<div class="row">
									  		<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mb-2">
									<?php 
										$fullNameWeekDays = config('api.week_day_full_name', ['Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday']);
										$dispatchDays = $activeDeliveryDays = [];
										if(isset($timerDetails->dispatch_days) && $timerDetails->dispatch_days) {
											$dispatchDays = explode(',', $timerDetails->dispatch_days);
										}

										if(isset($timerDetails->active_delivery_days) && $timerDetails->active_delivery_days) {
											$activeDeliveryDays = explode(',', $timerDetails->active_delivery_days);
										}
									?>
									  			<label class="form-label h6 mt-1">Dispatch Days</label>
									  			@foreach($fullNameWeekDays as $key => $wValue)
												<div class="form-check mt-1 mb-0 ps-0">
													<div class="form-check form-switch">
															<input class="form-check-input dispatch_days" type="checkbox" name="dispatch_days" role="switch" id="dispatch_days{{$key}}" value="{{$key}}"  @if($dispatchDays && in_array($key, $dispatchDays)) checked @endif>
									  							<label class="form-check-label" for="dispatch_days{{$key}}"> {{ $wValue }}</label>
													</div>
												</div>


												@endforeach
									  		</div>
									  		<div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mb-2">
									  			<label class="form-label h6">Active Delivery Days</label>
									  			@foreach($fullNameWeekDays as $key => $wValue)
													<div class="form-check mt-1 mb-0 ps-0">
													<div class="form-check form-switch">
															<input class="form-check-input active_delivery_days" type="checkbox" name="active_delivery_days" role="switch" id="active_delivery_days{{$key}}" value="{{$key}}"  @if($activeDeliveryDays && in_array($key, $activeDeliveryDays)) checked @endif>
									  							<label class="form-check-label" for="active_delivery_days{{$key}}"> {{ $wValue }}</label>
													</div>
												</div>
												@endforeach
									  		</div>
										</div>
										<div class="row">
										<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mb-2">
											<?php 
												$dateFormat = config('api.date_format', ['d/m/Y' => 'd/m/Y', 'm/d/Y' => 'm/d/Y', 'Y/m/d' => 'Y/m/d']);
											?>
											<label class="form-label h6 mt-2">Select Date Format</label>
								    		<select class="form-select d-flex" id="delivery_date_format" name="delivery_date_format">
								    			<option value="">Please select</option>
								    			@foreach($dateFormat as $key => $dVal)
								    				<option value="{{$key}}" @if(isset($timerDetails->delivery_date_format) && $timerDetails->delivery_date_format == $key) selected @endif>{{$dVal}}</option>
								    			@endforeach
								    		</select>
										</div>
										</div>
										<div class="row">
											<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mb-2">
									  			<p class="h6 mt-2 mb-0">Please enter any public holidays or office closures. The timer will skip these for dispatch dates.</p>
											</div>
									  	</div>
									  	<div class="mb-0 row mt-0">
											<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 col-xxl-4 mb-0 mt-2 mb-1">
												<div id="myDatePicker1" class="w-100"></div>
											</div>
											<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 col-xxl-4 mb-0 mt-2 mb-1">
												<div id="test" class="ex3 w-100 tablecustom_box">
													<table class="table table_custom">
														<thead>
															<tr>
																<th scope="col">Date</th>
														        <th class="trash_col" scope="col">Action</th>
															</tr>
														</thead>
														<tbody id="dispacth_dates_body">
															@foreach($disableDispatchDates ?? [] as $dValue)
																<tr>
																	<td>{{$dValue['date'] ?? ''}}</td>
																	<td class="trash_col"><span class="delete_dispacth_dates" data-id="{{$dValue['id'] ?? ''}}"><i class='fa fa-trash-alt'></i></span></td>
																</tr>
															@endforeach
														</tbody>
													</table>
							                	</div>
											</div>
										</div>
									</div>
									<hr>
							<h5 class="card-title card-title-bg countdown-card-title">Countdown</h5>
							<!-- countdown tabs content -->
							  	<div >
							  		<div class="row">
								    	<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6">
								    		<label class="form-label h6">Select Cutoff Time</label>
								    		<div class="row">
								    			<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 mb-2">
										    		<select class="form-select d-flex" id="cutoff_hour" name="cutoff_hour">
										    			<option value="0" @if(isset($timerDetails->cutoff_hour) && $timerDetails->cutoff_hour == "0") selected @endif>12 AM</option>
														@for($i=1; $i < 12; $i++)
													  		<option value="{{ $i }}" @if(isset($timerDetails->cutoff_hour) && $timerDetails->cutoff_hour == $i) selected @endif>{{ $i }} AM</option>
													  	@endfor
													  	<option value="12" @if(isset($timerDetails->cutoff_hour) && $timerDetails->cutoff_hour == "12") selected @endif>12 PM</option>
														@for($i=1; $i < 12; $i++)
															@php $j = $i+12; @endphp
													  		<option value="{{ $j }}" @if(isset($timerDetails->cutoff_hour) && $timerDetails->cutoff_hour == $j) selected @endif>{{ $i }} PM</option>
													  	@endfor
													</select>
												</div>
												<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 mb-2">
													<select class="form-select d-flex" id="cutoff_minutes" name="cutoff_minutes">
														<option value="0">Please select minutes</option>
														@for($i=0; $i < 60; $i++)
													  		<option value="{{ $i }}" @if(isset($timerDetails->cutoff_minutes) && $timerDetails->cutoff_minutes == $i) selected @endif>{{ $i }}</option>
													  	@endfor
													</select>
												</div>
											</div>
								    	</div>
								    	<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 mb-2">
								    		<label class="form-label h6">Countdown Format</label>
								    		<?php $cFormat = config('api.countdown_format', ['text' => 'Text Based', 'visual' => 'Visual']); ?>
								    		<select class="form-select d-flex" id="countdown_format" name="countdown_format">
								    			<option value="">Please select</option>
								    			@foreach($cFormat as $key => $cfVal)
								    				<option value="{{$key}}" @if(isset($timerDetails->countdown_format) && $timerDetails->countdown_format == $key) selected @endif>{{$cfVal}}</option>
								    			@endforeach
								    		</select>
								    	</div>
								    </div>
								   <div class="mt-2">
								   		<div class="form-check form-check-inline mt-1 mb-0 ps-0">
											<div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" name="enable_second" role="switch" id="enable_second" value="1" @if(isset($timerDetails->enable_second) && $timerDetails->enable_second == "0") @else checked @endif>
							  							<label class="form-check-label" for="enable_second">Seconds</label>
											</div>
										</div>
								  </div>
							  	</div>
						</div>
					</div>
				</div>
	    	</div>
	    </div>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card border-0 mb-2 mt-3 mt-lg-3">
				  <div class="card-body p-3">
				    <h5 class="card-title card-main-title">Display</h5>
				    <p class="card-text">Manage how Delivery Estimator will look on your store.</p>
				  </div>
				</div>
	    	</div>
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card card_box w-100 mb-2 mt-2 mt-lg-2">
				  	<div class="card-body">
				  		<div class="">
							<ul class="nav nav-tabs custommytab w-100" id="myTab2" role="tablist">
								<li class="nav-item" role="presentation">
								    <button class="nav-link active" id="text-based-tab" data-bs-toggle="tab" data-bs-target="#text-based" type="button" role="tab" aria-controls="text-based" aria-selected="true">Text Based Timer</button>
								</li>
							 	<li class="nav-item" role="presentation">
							    	<button class="nav-link" id="visual-tab" data-bs-toggle="tab" data-bs-target="#visual" type="button" role="tab" aria-controls="visual" aria-selected="false">Visual Timer</button>
							  	</li>
							</ul>
							<div class="tab-content mt-3" id="myTabContent2">
							  	<div class="tab-pane fade show active" id="text-based" role="tabpanel" aria-labelledby="text-based-tab">
							  		<label for="basic-url" class="form-label h6">Preview <a class="tip" href="JavaScript:void(0);" data-bs-toggle="tooltip" title="If Timer is not visible than Refresh the page!"><i class="fas fa-question" aria-hidden="true"></i></a></label>
									<div class="row">
							  			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
							  				<?php 
							  					$wrapper_style = $countdown_style = $deliverydate_style = '';
							  					if (isset($timerDetails->text_font_size) && $timerDetails->text_font_size) {
							  						$wrapper_style .= "font-size:".$timerDetails->text_font_size ."px;";
							  					}
							  					if (isset($timerDetails->text_align) && $timerDetails->text_align) {
							  						$wrapper_style .= "text-align:".$timerDetails->text_align .";";
							  					} else {
							  						$wrapper_style .= "text-align:left;";
							  					}
							  					if (isset($timerDetails->text_background_color) && $timerDetails->text_background_color) {
							  						$wrapper_style .= "background-color:".$timerDetails->text_background_color .";";
							  					}
							  					if (isset($timerDetails->text_font_color) && $timerDetails->text_font_color) {
							  						$wrapper_style .= "color:".$timerDetails->text_font_color .";";
							  					}
							  					if (isset($timerDetails->text_border_size) && $timerDetails->text_border_size) {
							  						$wrapper_style .= "border-width:".$timerDetails->text_border_size ."px;";
							  					}
							  					if (isset($timerDetails->text_border_color) && $timerDetails->text_border_color) {
							  						$wrapper_style .= "border-color:".$timerDetails->text_border_color .";";
							  					}
							  					if (isset($timerDetails->text_border_style) && $timerDetails->text_border_style) {
							  						$wrapper_style .= "border-style:".$timerDetails->text_border_style .";";
							  					}
							  					if (isset($timerDetails->text_border_radius) && $timerDetails->text_border_radius) {
							  						$wrapper_style .= "border-radius:".$timerDetails->text_border_radius ."px;";
							  					}
							  					if (isset($timerDetails->text_margin_top) && $timerDetails->text_margin_top) {
							  						$wrapper_style .= "margin-top:".$timerDetails->text_margin_top ."px;";
							  					}
							  					if (isset($timerDetails->text_margin_bottom) && $timerDetails->text_margin_bottom) {
							  						$wrapper_style .= "margin-bottom:".$timerDetails->text_margin_bottom ."px;";
							  					}
							  					if (isset($timerDetails->text_margin_left) && $timerDetails->text_margin_left) {
							  						$wrapper_style .= "margin-left:".$timerDetails->text_margin_left ."px;";
							  					}
							  					if (isset($timerDetails->text_margin_right) && $timerDetails->text_margin_right) {
							  						$wrapper_style .= "margin-right:".$timerDetails->text_margin_right ."px;";
							  					}

							  					if (isset($timerDetails->text_countdown_color) && $timerDetails->text_countdown_color) {
							  						$countdown_style .= "color:".$timerDetails->text_countdown_color .";";
							  					}
							  					if (isset($timerDetails->text_deliverydate_color) && $timerDetails->text_deliverydate_color) {
							  						$deliverydate_style .= "color:".$timerDetails->text_deliverydate_color .";";
							  					}

							  					$messageContent = $seconds = "";
							  					if (isset($timerDetails->custom_message)) {
							  						$messageContent = $timerDetails->custom_message;
							  					}
							  					$hidePreview = false;
							  					$hidePreviewClass="d-none";
							  					$estimatorPreviewClass="";
							  					$previwErrorMsg = "If you want to preview, please enable the dispatch days and delivery days.";
							  					if (isset($timerDetails->enable_dispatch_days) && $timerDetails->enable_dispatch_days == 1) {
										            if (!isset($timerDetails->dispatch_days) || (isset($timerDetails->dispatch_days) && !$timerDetails->dispatch_days)) {
										                $hidePreview = true;
										                $hidePreviewClass="";
										                $estimatorPreviewClass="d-none";
										            }
										        }

										        if (!isset($timerDetails->active_delivery_days) || (isset($timerDetails->active_delivery_days) && !$timerDetails->active_delivery_days)) {
									                $hidePreview = true;
									                $hidePreviewClass="";
									                $estimatorPreviewClass="d-none";
									            }
							  				?>
								  			<div class="timer_preview">
								  				<div id="delivery_estimator_wrapper" style="{{$wrapper_style}}" class="{{$estimatorPreviewClass}}">
								  					{!! $messageContent !!}								  					
								  				</div>
								  				<div id="disable_text_preview_message" class="preview_error {{$hidePreviewClass}}">
								  					{!! $previwErrorMsg !!}
								  				</div>
								  			</div>
							  			</div>
							  			<br>
							  			<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
								  			<div class="mb-3">
												<label for="custom_message" class="form-label">Message</label>
												<textarea class="form-control" id="custom_message" name="custom_message" rows="3" placeholder="Order within the next [countdown] for delivery by [deliverydate].">@if(isset($timerDetails->custom_message)){{$timerDetails->custom_message}}@endif</textarea>
											</div>
											<div class="d-flex justify-content-end w-100 mb-2">
												<button type="button" class="btn btn-outline-main reset_text_settings">Reset Settings</button>
											</div>
								  		</div>
							  		</div>
							  		<div class="row mt-2">
							  			<div class="pl-2 pr-2 pb-2">
							  				<h5 class="card-title mt-0 mb-0 card-title-bg pl-2 pr-2">Preview Settings</h5>
							  			</div>
							  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Alignment</h5>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label class="form-label h6">Position</label>
								    		<select class="form-select" id="text_position" name="text_position">
								    			<option value="0" @if(isset($timerDetails->text_position) && $timerDetails->text_position == "0") selected @endif>Below Add to cart</option>
								    			<option value="1" @if(isset($timerDetails->text_position) && $timerDetails->text_position == "1") selected @endif>Above Add to cart</option>
								    		</select>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label class="form-label h6">Text Align</label>
								    		<select class="form-select" id="text_align" name="text_align">
								    			<option value="left" @if(isset($timerDetails->text_align) && $timerDetails->text_align == "left") selected @endif>Left</option>
												<option value="center" @if(isset($timerDetails->text_align) && $timerDetails->text_align == "center") selected @endif>Center</option>
												<option value="right" @if(isset($timerDetails->text_align) && $timerDetails->text_align == "right") selected @endif>Right</option>
								    		</select>
							  			</div>
							  			<hr class="mt-3 mb-2">
							  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Font</h5>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_font_size" class="form-label h6">Font Size (px)</label>
												<input type="number" min="0" maxlength="255" class="form-control" id="text_font_size" placeholder="15px" name="text_font_size" @if(isset($timerDetails->text_font_size)) value="{{$timerDetails->text_font_size}}" @endif>
							  			</div>
									  	<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_font_color" class="form-label h6 w-100">Font Color</label>
											<input type="text" class="form-control w-100" maxlength="255" id="text_font_color" name="text_font_color" @if(isset($timerDetails->text_font_color)) value="{{$timerDetails->text_font_color}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_countdown_color" class="form-label h6 w-100">Countdown Color</label>
											<input type="text" class="form-control" id="text_countdown_color" maxlength="255" name="text_countdown_color" @if(isset($timerDetails->text_countdown_color)) value="{{$timerDetails->text_countdown_color}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_deliverydate_color" class="form-label h6 w-100">Delivery Date Color</label>
											<input type="text" class="form-control w-100" id="text_deliverydate_color" maxlength="255" name="text_deliverydate_color" @if(isset($timerDetails->text_deliverydate_color)) value="{{$timerDetails->text_deliverydate_color}}" @endif>
							  			</div>
							  				<hr class="mt-3 mb-2">
							  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Border</h5>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label class="form-label h6">Border Style</label>
							  				<?php 
							  					$borderStyles = ['none' => 'None','solid' => 'Solid', 'dashed' => 'Dashed', 'dotted' => 'Dotted', 'double' => 'Double', 'groove' => 'Groove', 'inset' => 'Inset', 'outset' => 'Outset', 'ridge' => 'Ridge'];
							  				?>
								    		<select class="form-select" id="text_border_style" name="text_border_style">
								    			@foreach($borderStyles as $key => $sVal)
								    				<option value="{{$key}}" @if(isset($timerDetails->text_border_style) && $timerDetails->text_border_style == $key) selected @endif>{{$sVal}}</option>
								    			@endforeach
								    		</select>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_border_color" class="form-label h6 w-100">Border Color</label>
											<input type="text" class="form-control w-100" id="text_border_color" maxlength="255" name="text_border_color" @if(isset($timerDetails->text_border_color)) value="{{$timerDetails->text_border_color}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_border_size" class="form-label h6">Border Size (px)</label>
											<input type="number" min="0" class="form-control w-100" id="text_border_size" maxlength="255" name="text_border_size" placeholder="1px" @if(isset($timerDetails->text_border_size)) value="{{$timerDetails->text_border_size}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_border_radius" class="form-label h6">Border Radius</label>
											<input type="number" min="0" maxlength="255" class="form-control w-100" id="text_border_radius" maxlength="255" name="text_border_radius" placeholder="1px" @if(isset($timerDetails->text_border_radius)) value="{{$timerDetails->text_border_radius}}" @endif>
							  			</div>
							  			<hr class="mt-3 mb-2">
							  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Spacing</h5>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_margin_top" class="form-label h6">Margin Top (px)</label>
											<input type="number" min="0" class="form-control" id="text_margin_top" maxlength="255" name="text_margin_top" placeholder="10px" @if(isset($timerDetails->text_margin_top)) value="{{$timerDetails->text_margin_top}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_margin_bottom" class="form-label h6">Margin Bottom (px)</label>
											<input type="number" min="0" class="form-control" id="text_margin_bottom" maxlength="255" name="text_margin_bottom" placeholder="10px" @if(isset($timerDetails->text_margin_bottom)) value="{{$timerDetails->text_margin_bottom}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_margin_left" class="form-label h6">Margin Left (px)</label>
											<input type="number" min="0" class="form-control" id="text_margin_left" maxlength="255" name="text_margin_left" placeholder="10px" @if(isset($timerDetails->text_margin_left)) value="{{$timerDetails->text_margin_left}}" @endif>
							  			</div>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_margin_right" class="form-label h6">Margin Right (px)</label>
											<input type="number" min="0" class="form-control" id="text_margin_right" maxlength="255" name="text_margin_right" placeholder="10px" @if(isset($timerDetails->text_margin_right)) value="{{$timerDetails->text_margin_right}}" @endif>
							  			</div>
							  			<hr class="mt-3 mb-2">
							  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Other</h5>
							  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
							  				<label for="text_background_color" class="form-label h6 w-100">Background Color</label>
											<input type="text" class="form-control w-100" maxlength="255" id="text_background_color" name="text_background_color" @if(isset($timerDetails->text_background_color)) value="{{$timerDetails->text_background_color}}" @endif>
										</div>
							  		</div>
							  	</div>
							  	<div class="tab-pane fade show" id="visual" role="tabpanel" aria-labelledby="visual-tab">
								  	<div class="tab-pane fade show active" id="text-based" role="tabpanel" aria-labelledby="text-based-tab">
								  		<label class="form-label h6">Preview <a class="tip" href="JavaScript:void(0);" data-bs-toggle="tooltip" title="If Timer is not visible than Refresh the page!"><i class="fas fa-question" aria-hidden="true"></i></a></label>
									</div>
								  		<?php
								  			$estimated_arrival_text_style = $icon_color_style = $accent_color_style = $visual_wrapper_style = $step_content_style = $toparrow_style = '' ;
								  			if (isset($timerDetails->visual_font_color) && $timerDetails->visual_font_color) {
						  						$estimated_arrival_text_style = "color:".$timerDetails->visual_font_color .";";
						  					}
						  					if (isset($timerDetails->visual_icon_color) && $timerDetails->visual_icon_color) {
						  						$icon_color_style = "color:".$timerDetails->visual_icon_color .";";
						  					}
						  					if (isset($timerDetails->visual_accent_color) && $timerDetails->visual_accent_color) {
						  						$accent_color_style = "background:".$timerDetails->visual_accent_color .";";
						  					}
						  					if (isset($timerDetails->visual_background_color) && $timerDetails->visual_background_color) {
						  						$step_content_style .= "background:".$timerDetails->visual_background_color .";";
						  						$toparrow_style = "border-bottom: 10px solid ".$timerDetails->visual_background_color .";";
						  					}
						  					if (isset($timerDetails->visual_text_color) && $timerDetails->visual_text_color) {
						  						$step_content_style .= "color:".$timerDetails->visual_text_color .";";
						  					}
						  					if (isset($timerDetails->visual_margin_top) && $timerDetails->visual_margin_top) {
						  						$visual_wrapper_style .= "margin-top:".$timerDetails->visual_margin_top ."px;";
						  					}
						  					if (isset($timerDetails->visual_margin_bottom) && $timerDetails->visual_margin_bottom) {
						  						$visual_wrapper_style .= "margin-bottom:".$timerDetails->visual_margin_bottom ."px;";
						  					}
						  					if (isset($timerDetails->visual_margin_left) && $timerDetails->visual_margin_left) {
						  						$visual_wrapper_style .= "margin-left:".$timerDetails->visual_margin_left ."px;";
						  					}
						  					if (isset($timerDetails->visual_margin_right) && $timerDetails->visual_margin_right) {
						  						$visual_wrapper_style .= "margin-right:".$timerDetails->visual_margin_right ."px;";
						  					}
								  		?>
								  		<div class="timer_preview1">
					                      <!-- -------------here--------------- -->
					                    	<div id="visual_estimator_wrapper" style="{{$visual_wrapper_style}}" class="{{$estimatorPreviewClass}}">
										  		<div class="estimated_arrival_text" style="{{$estimated_arrival_text_style}}">
										  			<span class="estimated_date">{{$settingsData['visual_delivery_date_text'] ?? 'Delivery Date'}}</span><span style="" class="estimated_arrival_color">{{$settingsData['visual_estimated_arrival'] ?? 'Estimated Arrival'}}</span>
										  		</div>
												
												<div class="step_indicators_section d-flex align-items-start justify-content-center mt-3">
													<div class="step_indicators_border" style="{{$accent_color_style}}"></div>
													<div class="step_indicators d-flex justify-content-start flex-wrap align-items-start">
														<div class="step_circle w-100 d-flex justify-content-start">
															<div class="step_circle_icon d-flex align-items-center justify-content-center" style="{{$accent_color_style}}"><i class='fas fa-calendar-check' style="{{$icon_color_style}}"></i></div>
														</div>
														<div class="step_content step_content_placed mt-2 d-flex flex-wrap justify-content-start  text-start" style="{{$step_content_style}}">
															<div class="toparrowbox" style="{{$toparrow_style}}"></div>
															<span class="w-100">{{$settingsData['visual_order_date_text'] ?? 'Order Date'}}</span>

															<span class="w-100">{{$settingsData['visual_order_placed'] ?? 'Order Placed'}}</span>
														</div>
													</div>
													<div class="step_indicators d-flex justify-content-center align-items-start flex-wrap">
														<div class="step_circle w-100 d-flex justify-content-center">
															<div class="step_circle_icon d-flex align-items-center justify-content-center" style="{{$accent_color_style}}"><i class="fas fa-shipping-fast" style="{{$icon_color_style}}"></i></div>
														</div>
														<div class="step_content step_content_dispatches mt-2 d-flex flex-wrap text-center" style="{{$step_content_style}}">
															<div class="toparrowbox" style="{{$toparrow_style}}"></div>
															<span class="w-100" id="vdis_date_text">{{$settingsData['visual_dispatches_date_text'] ?? 'Dispatches'}}</span>

															<span class="w-100">{{$settingsData['visual_order_dispatches'] ?? 'Order Dispatches'}}</span>
														</div>
													</div>
													<div class="step_indicators d-flex justify-content-end align-items-start flex-wrap">
														<div class="step_circle w-100 d-flex justify-content-end">
															<div class="step_circle_icon d-flex align-items-center justify-content-center" style="{{$accent_color_style}}"><i class="fas fa-box-open" style="{{$icon_color_style}}"></i></div>
														</div>
														<div class="step_content step_content_delivered mt-2 d-flex flex-wrap text-end" style="{{$step_content_style}}">
															<div class="toparrowbox" style="{{$toparrow_style}}"></div>
															<span class="w-100" id="vdel_date_text">{{$settingsData['visual_delivery_date_text'] ?? 'Delivery Date'}}</span>

															<span class="w-100">{{$settingsData['visual_delivered'] ?? 'Delivered'}}</span>
														</div>
													</div>
												</div>
											</div>
											<div id="disable_visual_preview_message" class="preview_error {{$hidePreviewClass}}">
							  					{!! $previwErrorMsg !!}
							  				</div>
									  	</div>
									  	<div class="d-flex justify-content-end w-100 mt-3 mb-2">
											<button type="button" class="btn btn-outline-main reset_visual_settings">Reset Settings</button>
										</div>
										<div class="row mt-0">
											<div class="pl-2 pr-2 pb-2">
												<h5 class="card-title mt-0 mb-0 card-title-bg pl-2 pr-2">Preview Settings</h5>
											</div>
											<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Color</h5>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_font_color" class="form-label h6 w-100">Font Color</label>
												<input type="text" class="form-control w-100" id="visual_font_color" maxlength="255" name="visual_font_color" @if(isset($timerDetails->visual_font_color)) value="{{$timerDetails->visual_font_color}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_icon_color" class="form-label h6 w-100">Icon Color</label>
												<input type="text" class="form-control w-100" id="visual_icon_color" maxlength="255" name="visual_icon_color" @if(isset($timerDetails->visual_icon_color)) value="{{$timerDetails->visual_icon_color}}" @endif>
											</div>
										  	<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_accent_color" class="form-label h6 w-100">Accent Color</label>
												<input type="text" class="form-control w-100" id="visual_accent_color" maxlength="255" name="visual_accent_color" @if(isset($timerDetails->visual_accent_color)) value="{{$timerDetails->visual_accent_color}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_text_color" class="form-label h6 w-100">Text Color</label>
												<input type="text" class="form-control w-100" id="visual_text_color" maxlength="255" name="visual_text_color" @if(isset($timerDetails->visual_text_color)) value="{{$timerDetails->visual_text_color}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_background_color" class="form-label h6 w-100">Background Color</label>
												<input type="text" class="form-control w-100" id="visual_background_color" maxlength="255" name="visual_background_color" @if(isset($timerDetails->visual_background_color)) value="{{$timerDetails->visual_background_color}}" @endif>
								  			</div>
								  			<hr class="mt-3 mb-2">
								  			<h5 class="card-title mt-2 mb-0 card-title_sub-title card-title-bg">Spacing</h5>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_margin_top" class="form-label h6 w-100">Margin Top (px)</label>
												<input type="number" min="0" class="form-control" id="visual_margin_top" maxlength="255" name="visual_margin_top" placeholder="10px" @if(isset($timerDetails->visual_margin_top)) value="{{$timerDetails->visual_margin_top}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_margin_bottom" class="form-label h6 w-100">Margin Bottom (px)</label>
												<input type="number" min="0" class="form-control" id="visual_margin_bottom" maxlength="255" name="visual_margin_bottom" placeholder="10px" @if(isset($timerDetails->visual_margin_bottom)) value="{{$timerDetails->visual_margin_bottom}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_margin_left" class="form-label h6">Margin Left (px)</label>
												<input type="number" min="0" class="form-control" id="visual_margin_left" maxlength="255" name="visual_margin_left" placeholder="10px" @if(isset($timerDetails->visual_margin_left)) value="{{$timerDetails->visual_margin_left}}" @endif>
								  			</div>
								  			<div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl-3 mt-2 mb-1">
								  				<label for="visual_margin_right" class="form-label h6">Margin Right (px)</label>
												<input type="number" min="0" class="form-control" id="visual_margin_right" maxlength="255" name="visual_margin_right" placeholder="10px" @if(isset($timerDetails->visual_margin_right)) value="{{$timerDetails->visual_margin_right}}" @endif>
								  			</div>
								  		</div>
							  		</div>
							  	</div>
							</div>
						</div>
				  	</div>
				</div>
	    	</div>
	    <div class="row">
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card border-0 mb-2 mt-3 mt-lg-3">
				  <div class="card-body p-3">
				    <h5 class="card-title card-main-title">Visibility</h5>
				    <p class="card-text">Manage Visibility of Delivery Estimator on your store.</p>
				  </div>
				</div>
	    	</div>
	    	<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
	    		<div class="card card_box w-100 mb-2 mt-2 mt-lg-2">
				  	<div class="card-body">
				  		<h5 class="card-title card-title-bg">Timer Visibility</h5>
						<div class="form-check form-check-inline mt-1 mb-0 ps-0">
							<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" name="enable_tbtimer" role="switch" id="enable_tbtimer" value="1"  @if(isset($timerDetails->enable_tbtimer) && $timerDetails->enable_tbtimer == "0") @else checked @endif>
			  							<label class="form-check-label h6" for="enable_tbtimer">Text Based Timer</label>
							</div>
						</div>

						<div class="form-check form-check-inline mt-1 mb-0 ps-0">
							<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" name="enable_vtimer" role="switch" id="enable_vtimer" value="1"  @if(isset($timerDetails->enable_vtimer) && $timerDetails->enable_vtimer == "1") checked @endif>
			  							<label class="form-check-label h6" for="enable_vtimer">Visual Timer</label>
							</div>
						</div>
			  			<div class="row mt-1">
						  <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
							<label class="form-label h6">Product Visibility</label>
							<p class="mb-2">By default, Delivery Estimator is visible on all products. If you wish to hide it on certain products, select a collection or enter any tags you like and if a product is contained in the collection or contains those tags, it will not be visible.</p>
						</div>
						</div>
				  		<div class="row mb-1">
				  			<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 mt-2">
								<label for="hide_on_collection" class="form-label h6 w-100">Hide on Collection</label>
								<?php
									$hideCollection = [];
									if (isset($timerDetails->hide_on_collection) && $timerDetails->hide_on_collection) {
										$hideCollection = explode(',', $timerDetails->hide_on_collection);
									}
								?>
								<select class="form-select form-control" id="hide_on_collection" name="hide_on_collection[]" multiple="multiple">
									@foreach($collection ?? [] as $cVal)
										<option value="{{$cVal['id'] ?? ''}}"  @if($hideCollection && in_array(($cVal['id'] ?? ''), $hideCollection)) selected @endif>{{$cVal['title'] ?? 'Untitled'}}</option>
									@endforeach
								</select>
							</div>
							<div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 col-xxl-6 mt-2">
								<label for="product_tags" class="form-label h6 w-100">Product Tags</label>
								<input type="text" class="form-control" id="product_tags" maxlength="255" name="product_tags" @if(isset($timerDetails->product_tags)) value="{{$timerDetails->product_tags}}" @endif>
							</div>
						</div>
				  	</div>
				</div>
	    	</div>
	    </div>
	   <hr>
	    <div class="row">
	    	<div class="mb-3">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
	    </div> 
		</form>
    </div>

    @include('footer')
@endsection

@section('scripts')
    @parent
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@3"></script>
   	<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify@3/dist/tagify.polyfills.min.js"></script>
	<!-- Perfect-DateTimePicker JS -->
	<script type="text/javascript" src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/js/colorpicker/coloris.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/js/select2.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/js/countdown.js') }}"></script>
	<script type="text/javascript">
		// The DOM element you wish to replace with Tagify
		var input = document.querySelector('input[name=product_tags]');

		// initialize Tagify on the above input node reference
		new Tagify(input)

		Coloris({
	      el: '#text_background_color,#text_font_color,#text_countdown_color,#text_deliverydate_color,#text_border_color,#visual_icon_color,#visual_accent_color,#visual_font_color,#visual_text_color,#visual_background_color'
	    });

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

		var AppBridge = window['app-bridge'];
		var createApp = AppBridge.createApp;
		var actions = AppBridge.actions;
		var app = createApp({
		    apiKey: "{{env('SHOPIFY_API_KEY', '18406be3958ef2225422b7f8eecc804d')}}",
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

		  var learnmorebutton = Button.create(app, { label: 'Learn More' });
		  var settingsbutton = Button.create(app, { label: 'Settings' });
		  var dashboardbutton = Button.create(app, { label: 'Dashboard' });
		  var managebutton = Button.create(app, { label: 'Estimator' });
		  var contactusbutton = Button.create(app, { label: 'Contact Us' });
		  var usagereportbutton = Button.create(app, { label: 'Usage Report' });

		  learnmorebutton.subscribe(Button.Action.CLICK, function() {
		    redirect.dispatch(Redirect.Action.REMOTE, { url: '#', newContext: true, });
		  });

		  settingsbutton.subscribe(Button.Action.CLICK, function() {
		    app.dispatch(Redirect.toApp({path: '/settings'}));
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
	      $('.main-setting-form').trigger('submit');
	      contextualSaveBar.dispatch(ContextualSaveBar.Action.HIDE);
	    });

	    document.addEventListener('DOMContentLoaded', () => {
	      $('#settingform').on('change', function() {
	        contextualSaveBar.dispatch(ContextualSaveBar.Action.SHOW);
	      });
	      $("input,textarea#custom_message").keyup(function(){
	        contextualSaveBar.dispatch(ContextualSaveBar.Action.SHOW);
	      });
	    });

	  	var titleBarOptions = {
		    title: 'Estimator',
		    buttons: {
		      primary: managebutton,
		      secondary: [dashboardbutton,settingsbutton, usagereportbutton,contactusbutton],
		    },
	  	};
	  	var myTitleBar = TitleBar.create(app, titleBarOptions);

		$(document).ready(function() {
			$('#hide_on_collection').select2();

			$(document).on('input', '.number_input', function(evt) {
			   var self = $(this);
			    self.val(self.val().replace(/\D/g, ""));
			    if ((evt.which < 48 || evt.which > 57)) 
			    {
			       evt.preventDefault();
			    }
			});

			$('#myDatePicker').datetimepicker({
		      baseCls: "perfect-datetimepicker w-100", 
		      viewMode: 'YMD',
		      firstDayOfWeek: 0,
		      date: new Date(), //initial date
		      endDate: null, //end date
		      startDate: new Date(), //start date
		      language: 'en', //I18N
		      //date update event
		      onDateUpdate: null,
		      onClear: null,
		      onOk: function(){
		      	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to hide timer on this date!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	var date  = this.getText('YYYY-MM-DD');

				        if (date != '') { //here
				          $.ajax({
				            type: 'post',
				            url: "{{ route('addDisabledDate') }}",
				            data: {
				              date: date,
				              user_store_id : "{{Auth::user()->id}}",
				            },
				            success: function(json_response) {
				            	if (json_response.status == true) {
				            		Toastr.fire({
			                          icon: 'success',
			                          title: 'Configuration saved!'
			                        })
				            		$('#disable_dates_body').html(json_response.body);
				            	} else {
				            		Toastr.fire({
			                          icon: 'error',
			                          title: 'This date is already added!'
			                        })
				            	}
				            },
				            dataType: 'json'
				          });
				        }
					}
				});
		      },
		      onClose: null,
		      onToday: null
		    });

			$('#myDatePicker1').datetimepicker({
		      baseCls: "perfect-datetimepicker w-100", 
		      viewMode: 'YMD',
		      firstDayOfWeek: 0,
		      date: new Date(), //initial date
		      endDate: null, //end date
		      startDate: new Date(), //start date
		      language: 'en', //I18N
		      //date update event
		      onDateUpdate: null,
		      onClear: null,
		      onOk: function(){
		      	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to skip dispatch on this date!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	var date  = this.getText('YYYY-MM-DD');
				        if (date != '') { //here
				          $.ajax({
				            type: 'post',
				            url: "{{ route('addDispatchDisabledDate') }}",
				            data: {
				              date: date,
				              user_store_id : "{{Auth::user()->id}}",
				            },	            
				            success: function(json_response) {
				            	if (json_response.status == true) {
				            		Toastr.fire({
			                          icon: 'success',
			                          title: 'Configuration saved!'
			                        })
				            		$('#dispacth_dates_body').html(json_response.body);
				            		changePreviewData();
				            	} else {
				            		Toastr.fire({
			                          icon: 'error',
			                          title: 'This date is already added!'
			                        })
				            	}
				            },
				            dataType: 'json'
				          });
				        }
					}
				});
		      },
		      onClose: null,
		      onToday: null
		    });

			// settings end
		    $('#settingform').submit(function(e) {
		    	var timer_visibility_val = [];
		        $('.timer_visibility:checked').each(function(i) {
		          timer_visibility_val[i] = $(this).val();
		        });
		        var timezone 			= $('#timezone').val();
		        var cutoff_hour 		= $('#cutoff_hour').val();
		        var cutoff_minutes 		= $('#cutoff_minutes').val();
		        var countdown_format 	= $('#countdown_format').val();

		        var enable_second, show_timer_past_cutoff, hide_comma_separator, enable_delivery_add_range, enable_dispatch_days, enable_tbtimer, enable_vtimer;
		        enable_second = show_timer_past_cutoff = hide_comma_separator = enable_delivery_add_range = enable_dispatch_days = enable_tbtimer = enable_vtimer = 0;

		        if ($('#enable_second').is(':checked')) {
		        	enable_second = 1;
		        }
		        if ($('#show_timer_past_cutoff').is(':checked')) {
		        	show_timer_past_cutoff = 1;
		        }
		        if ($('#hide_comma_separator').is(':checked')) {
		        	hide_comma_separator = 1;
		        }

		        var delivery_lead_time = $('#delivery_lead_time').val();
		        if ($('#enable_delivery_add_range').is(':checked')) {
		        	enable_delivery_add_range = 1;
		        }
		        var delivery_range_days = $('#delivery_range_days').val();
		        var delivery_handling_time = $('#delivery_handling_time').val();
		        if ($('#enable_dispatch_days').is(':checked')) {
		        	enable_dispatch_days = 1;
		        }
		        var dispatch_days_val = [];
		        $('.dispatch_days:checked').each(function(i) {
		          dispatch_days_val[i] = $(this).val();
		        });

		        var active_delivery_days_val = [];
		        $('.active_delivery_days:checked').each(function(i) {
		          active_delivery_days_val[i] = $(this).val();
		        });
		        
		        var delivery_date_format = $('#delivery_date_format').val(); 

		        var custom_message = $('#custom_message').val();
		        var text_position = $('#text_position').val(); 
		        var text_font_size = $('#text_font_size').val(); 
		        var text_align = $('#text_align').val(); 
		        var text_background_color = $('#text_background_color').val(); 
		        var text_font_color = $('#text_font_color').val(); 
		        var text_countdown_color = $('#text_countdown_color').val(); 
		        var text_deliverydate_color = $('#text_deliverydate_color').val(); 
		        var text_border_size = $('#text_border_size').val(); 
		        var text_border_color = $('#text_border_color').val(); 
		        var text_border_radius = $('#text_border_radius').val(); 
		        var text_border_style = $('#text_border_style').val(); 
		        var text_margin_top = $('#text_margin_top').val(); 
		        var text_margin_bottom = $('#text_margin_bottom').val(); 
		        var text_margin_left = $('#text_margin_left').val(); 
		        var text_margin_right = $('#text_margin_right').val(); 
		        var visual_icon_color = $('#visual_icon_color').val(); 
		        var visual_accent_color = $('#visual_accent_color').val(); 
		        var visual_font_color = $('#visual_font_color').val(); 
		        var visual_text_color = $('#visual_text_color').val(); 
		        var visual_background_color = $('#visual_background_color').val(); 
		        var visual_margin_top = $('#visual_margin_top').val(); 
		        var visual_margin_bottom = $('#visual_margin_bottom').val(); 
		        var visual_margin_left = $('#visual_margin_left').val();
		        var visual_margin_right = $('#visual_margin_right').val();
		        if ($('#enable_tbtimer').is(':checked')) {
		        	enable_tbtimer = 1;
		        }

		        if ($('#enable_vtimer').is(':checked')) {
		        	enable_vtimer = 1;
		        }
		        var hide_on_collection 	= $('#hide_on_collection').val();
		        var product_tags 		= $('#product_tags').val();
		        
		        // ajax
			    $.ajax({
			        type:"POST",
			        url: "{{ route('saveTimerConfiguration') }}",
			        data: { user_store_id: "{{Auth::user()->id}}", timer_visibility: timer_visibility_val, timezone : timezone, cutoff_hour : cutoff_hour, cutoff_minutes : cutoff_minutes, countdown_format : countdown_format, show_timer_past_cutoff : show_timer_past_cutoff, enable_second : enable_second, hide_comma_separator : hide_comma_separator, delivery_lead_time : delivery_lead_time, delivery_range_days : delivery_range_days, enable_delivery_add_range : enable_delivery_add_range, delivery_handling_time : delivery_handling_time, enable_dispatch_days : enable_dispatch_days, dispatch_days : dispatch_days_val, active_delivery_days : active_delivery_days_val, delivery_date_format: delivery_date_format, enable_tbtimer: enable_tbtimer, enable_vtimer: enable_vtimer, hide_on_collection : hide_on_collection, product_tags : product_tags, custom_message: custom_message, text_position : text_position, text_font_size : text_font_size, text_align : text_align, text_background_color : text_background_color, text_font_color : text_font_color, text_countdown_color : text_countdown_color, text_deliverydate_color : text_deliverydate_color, text_border_size : text_border_size, text_border_color : text_border_color,text_border_radius: text_border_radius, text_border_style: text_border_style, text_margin_top : text_margin_top, text_margin_bottom : text_margin_bottom, text_margin_left: text_margin_left, text_margin_right: text_margin_right, visual_icon_color : visual_icon_color, visual_accent_color : visual_accent_color, visual_font_color : visual_font_color, visual_text_color : visual_text_color, visual_background_color : visual_background_color, visual_margin_top : visual_margin_top, visual_margin_bottom : visual_margin_bottom, visual_margin_left : visual_margin_left, visual_margin_right : visual_margin_right },
			        dataType: 'json',
			        success: function(json_response) {
			        	Toastr.fire({
                          icon: 'success',
                          title: 'Configuration saved!'
                        })
		        		location.reload(true);
			        }
			    });
		    	return false;
		    });

			// settings start
				function findReplaceString(string, find1, replace1) {
                  if ((/[a-zA-Z\_]+/g).test(string)) {
                    var replace1 = '<span class="countdown">' + replace1 + '</span>'
                    s1 = string.toString().replace("[countdown]", replace1);
                    return s1;
                  } else {
                    return false;
                  }
                }

                function findReplaceStringDelivery(string, find1, replace1) {
                  if ((/[a-zA-Z\_]+/g).test(string)) {
                    s1 = string.toString().replace("[deliverydate]", replace1);
                    return s1;
                  } else {
                    return false;
                  }
                }

                var cutofftime = "{{$settingsData['cutofftime'] ?? ''}}";
                var finalhtml = "{{$settingsData['custom_message'] ?? ''}}";
                var deliverydate_color = "{{$settingsData['text_deliverydate_color'] ?? ''}}";
                var delivery_date_text = "{{$settingsData['text_delivery_date'] ?? 'Delivery Date'}}";

                var dDateStyle = "";
                if (deliverydate_color) {
                	dDateStyle = "color: "+deliverydate_color;
                }

                finalhtml = findReplaceString(finalhtml, 'countdown', cutofftime)

                var deliverydate = '<span class="delivery_date" style="' + dDateStyle + '">' + delivery_date_text + '</span>'

                if (deliverydate != null) {
                  finalhtml = findReplaceStringDelivery(finalhtml, 'deliverydate', deliverydate)
                }
                
				$("#delivery_estimator_wrapper").html(finalhtml);

               	var enable_second = "{{$settingsData['enable_second'] ?? ''}}";
               	var countdown_format = "{{$settingsData['countdown_format'] ?? ''}}";
              	var text_days = "{{$settingsData['text_days'] ?? 'Day(s)'}}";
               	var text_hours = "{{$settingsData['text_hours'] ?? 'hours'}}";
               	var text_minutes = "{{$settingsData['text_minutes'] ?? ''}}";
               	var text_seconds = "{{$settingsData['text_seconds'] ?? ''}}";

              	if (!text_days) {
                	text_days = "Day(s)"
              	}
              	if (!text_hours) {
                	text_hours = "hours"
              	}

              	function getminutes(data){
                	if(!text_minutes){
                    	return "%s  minutes"
                	} else if(text_minutes){
                    	return data + text_minutes
                	}
              	}

              	function getseconds(data){
                	if(!text_seconds){
                    	return "%s  seconds"
                	}else if(text_seconds){
                    	return data + text_seconds
                	}
              	}

				if ($.fn.countdown === undefined) {
                  // jQuery(document).ready(function($){
                  // console.log("countdown here");
                  // console.log($.fn.countdown);
                  // })
                } else {
                  // -----------------------------------------------------------
                  showPreview(finalhtml);
                }

                var ret1 = $('#delivery_estimator_wrapper').find('.days').text().replace(' day(s),', '');
                if (parseInt(ret1) == 0) {
                  $('head').append('<style>\
                  #delivery_estimator_wrapper .days{display: none!important;}\
                  </style>');
                }

                var text_countdown_color = "{{$settingsData['text_countdown_color'] ?? ''}}";
                if (text_countdown_color != '') {
                  $('#delivery_estimator_wrapper').find('.countdown').css('color', text_countdown_color)
                }
	
		    $(document).on("click", ".delete_dates", function() {
		    	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to remove date from hide timer!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	var id = $(this).data('id');
				    	 // ajax
					    $.ajax({
					        type:"POST",
					        url: "{{ route('deleteDisabledDate') }}",
					        data: { user_store_id: "{{Auth::user()->id}}", id: id },
					        dataType: 'json',
					        success: function(json_response) {
				        		if (json_response.status == true) {
				        			Toastr.fire({
			                          icon: 'success',
			                          title: 'Configuration saved!'
			                        })
				            		$('#disable_dates_body').html(json_response.body);
				            	}
					        }
					    });
					}
				});
		    });

		    $(document).on("click", ".delete_dispacth_dates", function() {
		    	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to remove date from dispatch dates!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	var id = $(this).data('id');
				    	 // ajax
					    $.ajax({
					        type:"POST",
					        url: "{{ route('deleteDispatchDisabledDate') }}",
					        data: { user_store_id: "{{Auth::user()->id}}", id: id },
					        dataType: 'json',
					        success: function(json_response) {
				        		if (json_response.status == true) {
				        			Toastr.fire({
			                          icon: 'success',
			                          title: 'Configuration saved!'
			                        })
				        			$('#dispacth_dates_body').html(json_response.body);
				        			changePreviewData();
				            	}
					        }
					    });
					}
				});
		    });

		    // reset text settings
			$(document).on("click", ".reset_text_settings", function() {
				Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to reset preview settings!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	$('#text_position').val('0').trigger('change');
					    $('#text_align').val('left').trigger('change');
						$('#text_font_size').val('').trigger('change');
						$('#delivery_estimator_wrapper').css('font-size', '');
					    $('#text_font_color').val('').trigger('change');
					    $('#text_countdown_color').val('').trigger('change');
					    $('#text_deliverydate_color').val('').trigger('change');
					    $('#text_border_style').val('none').trigger('change');
					    $('#text_border_color').val('').trigger('change');
					    $('#text_border_size').val('').trigger('change');
		    	      	$('#delivery_estimator_wrapper').css('border-width', '');
					    $('#text_border_radius').val('').trigger('change');
					    $('#delivery_estimator_wrapper').css('border-radius', '');
					    $('#text_margin_top').val('').trigger('change');
					    $('#text_margin_bottom').val('').trigger('change');
					    $('#text_margin_left').val('').trigger('change');
					    $('#text_margin_right').val('').trigger('change');
					    $('#text_background_color').val('').trigger('change');
					    $('#custom_message').val('Order within the next [countdown] for delivery by [deliverydate].');
					    showPreview();
					}
				});
			});

			// reset visual settings
			$(document).on("click", ".reset_visual_settings", function() {
				Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to reset preview settings!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	$('#visual_font_color').val('').trigger('change');
					    $('#visual_icon_color').val('').trigger('change');
					    $('#visual_accent_color').val('').trigger('change');
					    $('#visual_text_color').val('').trigger('change');
					    $('#visual_background_color').val('').trigger('change');
					    $('#visual_margin_top').val('').trigger('change');
					    $('#visual_margin_bottom').val('').trigger('change');
					    $('#visual_margin_left').val('').trigger('change');
					    $('#visual_margin_right').val('').trigger('change');
					}
				});
			});
			
			// custom message change event			
			$("#custom_message").keyup(function(e) {
                showPreview();
		    });

			$(document).on("change","#countdown_format",function(e){
		      showPreview();
		    });

			$(document).on("change","#delivery_date_format",function(e){
		      	changePreviewData();
		    });

			$(document).on("change","#cutoff_hour",function(e){
				changePreviewData();
		    });

			$(document).on("change","#cutoff_minutes",function(e){
				changePreviewData();
		    });

			$(document).on("change","#timezone",function(e){
				changePreviewData();
		    });

		    $('#enable_second').change(function() {
		    	if ($('#enable_second').is(':checked')) {
		    		enable_second = 1;
		    	} else {
		    		enable_second = 0;
		    	}
		    	showPreview();
			});

			$('#enable_delivery_add_range').change(function() {
				changePreviewData();		    	
			});

		    $("#delivery_lead_time").keyup(function(e) {
                changePreviewData();
		    });

		    $("#delivery_range_days").keyup(function(e) {
                changePreviewData();
		    });

		    $("#delivery_handling_time").keyup(function(e) {
                changePreviewData();
		    });

		    $('#enable_dispatch_days').change(function() {
				changePreviewData();		    	
			});

		    $('.dispatch_days').change(function() {
				changePreviewData();		    	
			});

			$('.active_delivery_days').change(function() {
				changePreviewData();		    	
			});

			function changePreviewData() {
		      	var format = $('#delivery_date_format').val();
		      	var cutoff_hour = $('#cutoff_hour').val();
		      	var cutoff_minutes = $('#cutoff_minutes').val();
		      	var timezone = $('#timezone').val();
		      	var del_l_time = 0;
		      	var del_add_range = 0;
		      	var del_h_time = 0;
		      	var enable_delivery_add_range = 0;

		      	if ($('#delivery_lead_time').val() && $('#delivery_lead_time').val() > 0) {
		      		del_l_time = $('#delivery_lead_time').val();
		      	}
		      	if ($('#enable_delivery_add_range').is(':checked')) {
		        	enable_delivery_add_range = 1;
		        }
		      	if (enable_delivery_add_range == 1 && $('#delivery_range_days').val() && $('#delivery_range_days').val() > 0) {
		      		del_add_range = $('#delivery_range_days').val();
		      	}
		      	if ($('#delivery_handling_time').val() && $('#delivery_handling_time').val() > 0) {
		      		del_h_time = $('#delivery_handling_time').val();
		      	}
		      	var enable_dispatch_days = 0;
		      	if ($('#enable_dispatch_days').is(':checked')) {
		        	enable_dispatch_days = 1;
		        }
		        var dispatch_days_val = [];
		        $('.dispatch_days:checked').each(function(i) {
		          dispatch_days_val[i] = $(this).val();
		        });

		        var active_delivery_days_val = [];
		        $('.active_delivery_days:checked').each(function(i) {
		          active_delivery_days_val[i] = $(this).val();
		        });

		      	// ajax
			    $.ajax({
			        type:"POST",
			        url: "{{ route('changePreviewData') }}",
			        data: { user_store_id: "{{Auth::user()->id}}", format: format, cutoff_hour: cutoff_hour, cutoff_minutes: cutoff_minutes, timezone: timezone, del_l_time: del_l_time, enable_delivery_add_range: enable_delivery_add_range, del_add_range: del_add_range, del_h_time: del_h_time , enable_dispatch_days: enable_dispatch_days,dispatch_days_val: dispatch_days_val, active_delivery_days_val: active_delivery_days_val },
			        dataType: 'json',
			        success: function(json_response) {
			        	if (json_response.status == true) {	
			        		delivery_date_text = JSON.parse(JSON.stringify(json_response.data.text_delivery_date));
			        		cutofftime = json_response.data.cutofftime;
			        		$("#vdel_date_text").text(JSON.parse(JSON.stringify(json_response.data.visual_delivery_date_text)));
			        		$(".estimated_date").text(JSON.parse(JSON.stringify(json_response.data.visual_delivery_date_text)));
			        		$("#vdis_date_text").text(JSON.parse(JSON.stringify(json_response.data.visual_dispatches_date_text)));

			        		if (json_response.data.hide_preview == true) {
			        			$('#visual_estimator_wrapper, #delivery_estimator_wrapper').addClass('d-none');
			        			$('#disable_text_preview_message, #disable_visual_preview_message').removeClass('d-none');
			        		} else {
			        			$('#visual_estimator_wrapper, #delivery_estimator_wrapper').removeClass('d-none');
			        			$('#disable_text_preview_message, #disable_visual_preview_message').addClass('d-none');
			        		}
			    			showPreview();
			        	}
			        }
			    });
			}

			function showPreview() {
				var preview_message = $('#custom_message').val();
				if (preview_message) {
					finalhtml = preview_message;
				} else {
					finalhtml = 'Order within the next [countdown] for delivery by [deliverydate].';
				}

				var dDateStyle = "";
				var delDateFormat = $('#delivery_date_format').val();
				deliverydate_color = $('#text_deliverydate_color').val();
				countdown_format = $('#countdown_format').val();
                if (deliverydate_color) {
                	dDateStyle = "color: "+deliverydate_color;
                }

                finalhtml = findReplaceString(finalhtml, 'countdown', cutofftime)

                var deliverydate = '<span class="delivery_date" style="' + dDateStyle + '">' + delivery_date_text + '</span>'
                if (deliverydate != null) {
                  finalhtml = findReplaceStringDelivery(finalhtml, 'deliverydate', deliverydate)
                }

                $("#delivery_estimator_wrapper").html(finalhtml);
				// -----------------------------------------------------------
	              if (countdown_format == "format2") {
	                if (enable_second == true) {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%sD </span> %sH %sM<span class='seconds' style=''> %sS</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                } else {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%sD </span> %sH %sM<span class='seconds' style='display: none;'> %sS</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                }
	              } else if (countdown_format == "format3") {
	                if (enable_second == true) {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s Day(s) </span> %s:%s<span class='seconds' style=''>:%s</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                } else {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s Day(s) </span> %s:%s<span class='seconds' style='display: none;'>:%s</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                }
	              }else if (countdown_format == "format4") {
	                if (enable_second == true) {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s " + text_days + ",</span> %s " + text_hours + ", " + getminutes('%s ') + "<span class='seconds' style=''>, " + getseconds('%s ') + "</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                } else {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s " + text_days + ",</span> %s " + text_hours + ", " + getminutes('%s ') + "<span class='seconds' style='display: none;'>, " + getseconds('%s ') + "</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                }
	              }else if (countdown_format == "format5") {
	                if (enable_second == true) {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%sD,</span> %sH, %sM<span class='seconds' style=''>, %sS</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                } else {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%sD,</span> %sH, %sM<span class='seconds' style='display: none;'>, %sS</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                }
	                // ------------------------here custom-------------------------------
	              } else {
	                if (enable_second == true) {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s " + text_days + "</span> %s " + text_hours + " " + getminutes('%s ') + "<span class='seconds' style=''> " + getseconds('%s ') + "</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                } else {
	                  $("#delivery_estimator_wrapper .countdown").countdown({
	                    date: cutofftime,
	                    text: "<span class='days'>%s " + text_days + "</span> %s " + text_hours + " " + getminutes('%s ') + "<span class='seconds' style='display: none;'> " + getseconds('%s ') + "</span>",
	                    end: function () {
	                      $('#delivery_estimator_wrapper').remove();
	                    }
	                  });
	                }
	              }

	             var ret1 = $('#delivery_estimator_wrapper').find('.days').text().replace(' day(s),', '');
                if (parseInt(ret1) == 0) {
                  $('head').append('<style>\
                  #delivery_estimator_wrapper .days{display: none!important;}\
                  </style>');
                } else {
                	$('head').append('<style>\
                  #delivery_estimator_wrapper .days{display: inline!important;}\
                  </style>');
                }

                text_countdown_color = $('#text_countdown_color').val();
                if (text_countdown_color != '') {
                  $('#delivery_estimator_wrapper').find('.countdown').css('color', text_countdown_color)
                }
			}

		    // -----live visual changes-------
		    $(document).on("change","#visual_icon_color",function(e){
		      $('.step_circle_icon i.fas').css('color',$(this).val());
		    });
		    $(document).on("change","#visual_accent_color",function(e){
		      $('#visual_estimator_wrapper .step_circle_icon').css('background',$(this).val());
		      $('#visual_estimator_wrapper .step_indicators_border').css('background',$(this).val());
		    });
		    $(document).on("change","#visual_font_color",function(e){
		    	$('.estimated_arrival_text').css('color',$(this).val());
		    });
		    $(document).on("change","#visual_background_color",function(e){
		    	$('.step_content').css('background',$(this).val());
		    	if ($(this).val() != "") {
		    		$('.toparrowbox').css('border-bottom','10px solid '+$(this).val());
		    	} else {
		    		$('.toparrowbox').css('border-bottom','');
		    	}
		    });
		    $(document).on("change","#visual_text_color",function(e){
		    	$('.step_content').css('color',$(this).val());
		    });
		    // -----live text based timer changes-------
		    $("#text_font_size").keyup(function(e){
		      $('#delivery_estimator_wrapper').css('font-size', $(this).val() + 'px');
		    });
		    $("#text_align").change(function(e){
		      $('#delivery_estimator_wrapper').css('text-align', $(this).val());
		    });
		    $(document).on("change","#text_background_color",function(e){
		      $('#delivery_estimator_wrapper').css('background-color',$(this).val());
		    });
		    $(document).on("change","#text_font_color",function(e){
		      $('#delivery_estimator_wrapper').css('color',$(this).val());
		    });
		    $(document).on("change","#text_countdown_color",function(e){
		      $('#delivery_estimator_wrapper .countdown').css('color',$(this).val());
		    });
		    $(document).on("change","#text_deliverydate_color",function(e){
		      $('#delivery_estimator_wrapper .delivery_date').css('color',$(this).val());
		    });

		    $(document).on("change","#text_border_style",function(e){
		      $('#delivery_estimator_wrapper').css('border-style',$(this).val());
		    });
		    $("#text_border_radius").keyup(function(e){
		    	$('#delivery_estimator_wrapper').css('border-radius', $(this).val() + 'px');
		    });
		    $("#text_border_size").keyup(function(e){
		      	$('#delivery_estimator_wrapper').css('border-width', $(this).val() + 'px');
		    });
		    $(document).on("change","#text_border_color",function(e){
		      	$('#delivery_estimator_wrapper').css('border-color',$(this).val());
		    });
		    // ----------end------------

		    $(document).on("change","#enable_delivery_add_range",function(e) {
		    	if ($('#enable_delivery_add_range').is(':checked')) {
		    		$('#day_range').removeClass('d-none');
		    	} else {
		    		$('#day_range').addClass('d-none');
		    	}	    	
		    });

		    $(document).on("click", ".disable_timer", function() {
		    	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to disable the estimator!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	// ajax
					    $.ajax({
					        type:"POST",
					        url: "{{ route('changeStatus') }}",
					        data: { user_store_id: "{{Auth::user()->id}}", status: 0 },
					        dataType: 'json',
					        success: function(json_response) {
					        	Toastr.fire({
		                          icon: 'success',
		                          title: 'Configuration saved!'
		                        })
				        		location.reload(true);
					        }
					    });
					}
				});
		    });

		    $(document).on("click", ".enable_timer", function() {
		    	Swal.fire({
				  title: 'Are you sure?',
				  text: "You want to enable the estimator!",
				  icon: 'warning',
				  showCancelButton: true,
				  confirmButtonColor: '#004b68',
				  cancelButtonColor: '#a7c638',
				  confirmButtonText: 'Ok'
				}).then((result) => {
					if (result.isConfirmed) {
					  	// ajax
					    $.ajax({
					        type:"POST",
					        url: "{{ route('changeStatus') }}",
					        data: { user_store_id: "{{Auth::user()->id}}", status: 1 },
					        dataType: 'json',
					        success: function(json_response) {
					        	Toastr.fire({
		                          icon: 'success',
		                          title: 'Configuration saved!'
		                        })
				        		location.reload(true);
					        }
					    });	
					}
				});
		    });
		});

	  function goto(url){
	    app.dispatch(Redirect.toApp({path: url}));
	  }
	</script>
<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})
</script>

@endsection