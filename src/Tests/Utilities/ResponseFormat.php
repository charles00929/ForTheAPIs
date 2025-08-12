<?php

namespace BWTV\ForTheAPIs\Tests\Utilities;

use Illuminate\Testing\Fluent\AssertableJson;

trait ResponseFormat
{

    public function assertResponseFormat($response)
    {
        $response->assertJson(
            fn(AssertableJson $json) => $json
                ->has('data')
                ->has('message')
                ->where('code', fn($value) => preg_match('/^\d{2}x\d{4}$/', $value) != FALSE)
                ->when(
                    array_key_exists('errors', $json->toArray()), 
                    fn($json) => $json->has('errors')
                )
                ->when(
                    array_key_exists('debug', $json->toArray()), 
                    fn($json) => $json->has('debug')
                )
                ->when(
                    array_key_exists('pagination', $json->toArray()), 
                    fn($json) => $json->has('pagination')
                )
        );
        
        $response->assertHeader('Content-Type', 'application/json');
    }
}
