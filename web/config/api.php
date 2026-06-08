<?php

return [
	'week_days' => [
		1 => 'Mon',
		2 => 'Tue',
		3 => 'Wed',
		4 => 'Thu',
		5 => 'Fri',
		6 => 'Sat',
		7 => 'Sun',
	],

	'week_day_full_name' => [
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
		7 => 'Sunday',
	],

	'countdown_format' => [
		'format1' => 'xxdays xxhours xxminutes xxseconds',
		'format2' => 'xxD xxH xxM xxS',
		'format3' => 'hh:mm:ss',
		'format4' => 'xxdays, xxhours, xxminutes, xxseconds',
		'format5' => 'xxD, xxH, xxM, xxS',
	],

	'date_format' => [
		'd/m' => 'DD/MM',
		'm/d (l)' => 'MM/DD Day',
		'l, d/m' => 'Day, DD/MM',
		'l, m/d' => 'Day, MM/DD',
		'l, d F' => 'Day, Date Month',
		'l, F d' => 'Day, Month Date',
		'l, d/n' => 'Day, Date/Month',
		'l d/m' => 'Day DD/MM',
		'l m/d' => 'Day MM/DD',
		'l d F' => 'Day Date Month',
		'l F d' => 'Day Month Date',
		'l d/n' => 'Day Date/Month',
	],

	'plan_type' => env('PLAN_TYPE','free'),

	'recurring_application_plan' => [
		'capped_amount' => 9.99,
		'terms' => 'Up to 3000 Monthly Views - Free<br>
                    Up to 15,000 Monthly Views - $4.99/Month<br>
                    Unlimited Monthly Views - $9.99/Month',
        'limit' => env('FREE_PLAN_LIMIT',3000),
	]
];
?>