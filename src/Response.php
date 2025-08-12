<?php

namespace BWTV\ForTheAPIs;

use Illuminate\Http\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Contracts\Support\Responsable;

class Response implements Responsable
{
    protected static $templates = [
        ResponseCode::SUCCESS => ['message_key' => 'api-handler.messages.success', 'status' => Response::HTTP_OK, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::PERMISSION_DENY => ['message_key' => 'api-handler.messages.forbidden', 'status' => Response::HTTP_FORBIDDEN, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::BAD_REQUEST => ['message_key' => 'api-handler.messages.bad_request', 'status' => Response::HTTP_BAD_REQUEST, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::ENTITY_NOT_EXIST => ['message_key' => 'api-handler.messages.not_found', 'status' => Response::HTTP_NOT_FOUND, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::SERVER_ERROR => ['message_key' => 'api-handler.messages.server_error', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::VALIDATION_ERROR => ['message_key' => 'api-handler.messages.validation_fail', 'status' => Response::HTTP_UNPROCESSABLE_ENTITY, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::UNAUTHENTICATED => ['message_key' => 'api-handler.messages.unauthenticated', 'status' => Response::HTTP_UNAUTHORIZED, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::TOO_MANY_REQUEST => ['message_key' => 'api-handler.messages.too_many_requests', 'status' => 429, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::DB_ERROR => ['message_key' => 'api-handler.messages.db_error', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'method' => ResponseCode::METHOD_NONE],
        ResponseCode::MISSING_FILES => ['message_key' => 'api-handler.messages.file_not_found', 'status' => Response::HTTP_INTERNAL_SERVER_ERROR, 'method' => ResponseCode::METHOD_NONE],
    ];

    /**
     * for developer to debug.
     */
    public mixed $debug;

    /**
     * shows only the form validation errors.
     */
    public array $errors;

    public function __construct(
        public mixed $data = [],
        public ?string $message = null,
        public int $status = 501,
        public string $method = ResponseCode::METHOD_NONE,
        public string $code = ResponseCode::SERVER_ERROR
    ) {
        $this->data = $data;
        $this->message = $message ?? config('api-handler.messages.error', 'not implement.');
        $this->status = $status;
        $this->method = $method;
        $this->code = $code;
        $this->errors = [];
        $this->debug = NULL;
    }

    protected static function mapping($code)
    {
        return static::$templates[$code] ?? [
            'message_key' => 'api-handler.messages.general_error',
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'method' => ResponseCode::METHOD_NONE,
        ];
    }

    /**
     * A template that response by code.
     */
    public static function with($code, $data = [])
    {
        $template = static::mapping($code);
        return new self(
            $data,
            config($template['message_key'], 'This does not implement.'),
            $template['status'],
            $template['method'],
            $code
        );
    }

    /**
     * Modify the response by a callback.  
     * 
     * @param callable $callback to modify the response.
     * @return $this
     */
    public function modify(callable $callback)
    {
        $callback($this);
        return $this;
    }

    /**
     * A template that response some resource.
     */
    public static function done($data = [], $message = null, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::SUCCESS, $data)
            ->modify(function ($response) use ($message, $method) {
                $response->method = $method;
                if($message !== null){
                    $response->message = $message;
                }
            });
    }

    /**
     * A template that response something wrong.
     */
    public static function badRequest($reason = null, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::BAD_REQUEST)
            ->modify(function ($response) use ($reason, $method) {
                $response->method = $method;
                if ($reason !== null) {
                    $response->message = $reason;
                }
            });
    }

    /**
     * A template that response something does not find.
     */
    public static function notFound($reason = null, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::ENTITY_NOT_EXIST)
            ->modify(function ($response) use ($reason, $method) {
                $response->method = $method;
                if ($reason !== null) {
                    $response->message = $reason;
                }
            });
    }

    /**
     * A template that response something wrong with internal.
     * @param $reason given a message to explain what happened.
     * @param $method the response method code
     * @param $code default SERVER_ERROR
     */
    public static function serverError($reason = null, $method = ResponseCode::METHOD_NONE, $code = ResponseCode::SERVER_ERROR)
    {
        return self::with($code)
            ->modify(function ($response) use ($reason, $method) {
                $response->method = $method;
                if ($reason !== null) {
                    $response->message = $reason;
                }
            });
    }

    /**
     * A template that response form validation.
     */
    public static function validationFail($errors, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::VALIDATION_ERROR)
            ->modify(function ($response) use ($errors, $method) {
                $response->method = $method;
                $response->errors = $errors;
            });
    }

    /**
     * A template that response user are unauthenticated.
     */
    public static function unauthenticated($reason = null, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::UNAUTHENTICATED)
            ->modify(function ($response) use ($reason, $method) {
                $response->method = $method;
                if ($reason !== null) {
                    $response->message = $reason;
                }
            });
    }

    /**
     * A template that response user are unauthorized.
     */
    public static function forbidden($reason = null, $method = ResponseCode::METHOD_NONE)
    {
        return self::with(ResponseCode::PERMISSION_DENY)
            ->modify(function ($response) use ($reason, $method) {
                $response->method = $method;
                if ($reason !== null) {
                    $response->message = $reason;
                }
            });
    }

    /**
     * Convert to HTTP response (required by Responsable interface)
     */
    public function toResponse($request = null)
    {
        return response()->json($this->toArray(), $this->status);
    }

    public function toArray()
    {
        $outline = [];

        /**
         * special handling before assigning to array
         * 
         * break down objects by class
         */

        /**  */
        if ($this->data instanceof \Illuminate\Http\Resources\Json\ResourceCollection) {
            $this->data = $this->data->resource;
        }

        if (empty($this->data)) {
            $outline['data'] = [];
        } else if ($this->data instanceof \Illuminate\Pagination\AbstractPaginator) {
            $arraying = $this->data->toArray();

            $payload = $arraying['data'];
            $pagination = $arraying;
            unset($pagination['data']);

            // make 'data' JSONify to be object instead array
            $outline['data']['list'] = $payload;
            $outline['pagination'] = $pagination;
        } else if ($this->data instanceof \Illuminate\Support\Collection) {
            $outline['data']['list'] = $this->data;
        } else {
            $outline['data'] = $this->data;
        }

        $outline['message'] = $this->message;
        $outline['code'] = $this->method . 'x' . $this->code;

        if ($this->errors) {
            $outline['errors'] = $this->errors;
        }

        if ($this->debug) {
            $outline['debug'] = $this->debug;
        }

        return $outline;
    }
}
