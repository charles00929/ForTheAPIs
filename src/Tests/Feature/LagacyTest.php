<?php

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Route;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use BWTV\ForTheAPIs\Tests\Utilities\RuntimeSetup;
use BWTV\ForTheAPIs\Tests\Utilities\ResponseFormat;

class LagacyTest extends TestCase
{
    use ResponseFormat, RuntimeSetup;

    /**
     * test the feature of v1.x
     */
    #[Test]
    public function it_can_campatible_with_response_of_old_way()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {

            /********** test this legacy way ************/
            return Response::done()->toResponse();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');
        
        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
    }
}
