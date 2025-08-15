<?php

namespace BWTV\ForTheAPIs\Exceptions;

use BWTV\ForTheAPIs\Response;
use Illuminate\Contracts\Support\Renderable;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Http\Response as LaravelResponse;

/**
 * to handle the business logic error in API manually.
 */
class ForTheAPIsException extends \Exception implements Renderable
{
    public function __construct(protected $responseCode, protected $data = []) {}

    public function render()
    {
        //TODO: configs 
        $exceptionResponse = config('for-the-apis.exception_responses.' . $this->responseCode, [
            'message' => 'This exception does not implement.',
            'status' => LaravelResponse::HTTP_INTERNAL_SERVER_ERROR,
            'method' => ResponseCode::METHOD_NONE,
        ]);

        return new Response(
            data: $this->data,
            message: $exceptionResponse['message'],
            status: $exceptionResponse['status'],
            method: $exceptionResponse['method'],
            code: $this->responseCode,
        );
    }
}
