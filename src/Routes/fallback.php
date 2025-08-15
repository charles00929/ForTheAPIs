<?php

use Illuminate\Support\Facades\Route;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;

/**
 * to catch all of undefined routes or HTTP-Methods under the specific namespace,
 */
Route::prefix(config('for-the-apis.handle_scope', 'api'))->any('/{any?}', function () {
    return (new Response(
        message: config('for-the-apis.messages.route_not_recognized', 'The route is not recognized.'),
        status: 404,
        code: ResponseCode::ROUTE_NOT_EXIST
    ));
})->where('any', '.*')->setFallback(TRUE);
