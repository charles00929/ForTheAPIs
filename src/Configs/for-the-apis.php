<?php

use BWTV\ForTheAPIs\Enums\ResponseCode;

return [
    /**
     * default messages for each response.
     */
    'messages' => [
        'success' => 'Success.',
        'error' => 'Error',
        'validation_fail' => 'Validation failed.',
        'unauthenticated' => 'Invalid or expired authentication.',
        'forbidden' => 'Permission deny.',
        'not_found' => 'Doesn\'t find related entities.',
        'too_many_requests' => 'Too many requests.',
        'bad_request' => 'Don\'t try anything invalid.',
        'server_error' => 'Server internal error.',
        'db_error' => 'Database error occurred.',
        'file_not_found' => 'Required file not found.',
        'general_error' => 'An unexpected error occurred.',
        'route_not_found' => 'The route is not recognized.',
    ],

    /**
     * handling only the routes prefix matching this prefix.
     */
    'handle_scope' => env('BWTV_FORTHEAPIS_PREFIX', 'api'),

    /**
     * middleware alias
     */
    'middleware_alias' => 'fta.api',

    /**
     * a list of response codes that defined by the project you own,
     * mapping to the response of the business logic error, 
     * when you throw BWTV\ForTheAPIs\Exceptions.
     * 
     * recommand that the key use constant of ResponseCode class you extend
     */
    'exception_responses' => [
        // example:
        ResponseCode::SERVER_ERROR => [
            'message' => 'Server internal error.',
            'status' => 500,
            'method' => ResponseCode::METHOD_NONE,
        ],
        
    ],
];
