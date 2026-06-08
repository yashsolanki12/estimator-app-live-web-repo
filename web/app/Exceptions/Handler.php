<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Osiset\ShopifyApp\Exceptions\SignatureVerificationException;
use Osiset\ShopifyApp\Exceptions\MissingShopDomainException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

     /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     * @param  Exception  $exception
     * @return void
     */
    public function report(Throwable $exception) {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     * @param  Illuminate\Http\Request  $request
     * @param  Exception  $exception
     * @return Illuminate\Http\Response
     */
    public function render($request, Throwable   $exception) {
        
        if ($exception instanceof SignatureVerificationException) {
            return view('errorpage')->with(['message' => $exception->getMessage()]);
        }
        
        if ($exception instanceof MissingShopDomainException) {
            return \Redirect::route('showError')->with( ['message' => $exception->getMessage()] );
        }

        return parent::render($request, $exception);
    }
}
