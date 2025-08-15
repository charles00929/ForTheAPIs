<?php

namespace BWTV\ForTheAPIs\Exceptions;

use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Foundation\Exceptions\Handler;


/**
 * instiniating when \BWTV\ForTheAPIs\Controllers\APIBaseController or subclass was resolved.
 */
class APIExceptionHandler extends Handler
{
    public function register()
    {
        $this->renderable(function (ForTheAPIsException $e, $request) {
            return $e->render();
        })->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            $response = Response::with(ResponseCode::VALIDATION_ERROR);
            $response->errors = $e->errors();
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            $response = Response::with(ResponseCode::UNAUTHENTICATED);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            $response = Response::with(ResponseCode::TOO_MANY_REQUEST);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            $response = Response::with(ResponseCode::PERMISSION_DENY);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            $response = Response::with(ResponseCode::BAD_REQUEST);
            if (config('app.debug')) {
                $response->status = $e->getStatusCode();
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            $response = Response::with(ResponseCode::DB_ERROR);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Illuminate\Contracts\Filesystem\FileNotFoundException $e, $request) {
            $response = Response::with(ResponseCode::MISSING_FILES);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        })->renderable(function (\Throwable $e, $request) {
            $response = Response::with(ResponseCode::SERVER_ERROR);
            if (config('app.debug')) {
                $response->debug = $e->getMessage();
            }
            return $response;
        });

        /** 
         * handle fallback by Routes/fallback.php
         */
    }
}
