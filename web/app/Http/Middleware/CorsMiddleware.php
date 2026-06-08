<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CorsMiddleware 
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		header('Access-Control-Allow-Origin: *');
		$headers = [
			// 'Access-Control-Allow-Origin' => '*',
			'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE,PATCH',
			'Access-Control-Allow-Credentials' => 'true',
			'Access-Control-Max-Age' => '86400',
			'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
		];
		if ($request->isMethod('OPTIONS')) {
			return response()->json('{"method":"OPTIONS"}', 200, $headers);
		}
		$response = $next($request);

		$user = Auth::user();
 	    if($user)
	    {
	        $response->header('Content-Security-Policy', "frame-ancestors https://".$user->name." https://admin.shopify.com");
	    }
	 
		foreach ($headers as $key => $value) {
			$response->header($key, $value);
		}
		return $response;
	}

}
