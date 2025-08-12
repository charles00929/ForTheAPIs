<?php

namespace BWTV\ForTheAPIs\Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Pagination\LengthAwarePaginator;
use BWTV\ForTheAPIs\Tests\Utilities\RuntimeSetup;
use BWTV\ForTheAPIs\Tests\Utilities\ResponseFormat;

class TemplatesResponseTest extends TestCase
{
    use ResponseFormat, RuntimeSetup;


    /*
    |--------------------------------------------------------------------------
    | Templates Response Tests
    |--------------------------------------------------------------------------
    */
    #[Test]
    public function it_can_return_success_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::SUCCESS);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
    }

    #[Test]
    public function it_can_return_bad_request_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::BAD_REQUEST);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(400);
    }

    #[Test]
    public function it_can_return_not_found_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::ENTITY_NOT_EXIST);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_return_server_error_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::SERVER_ERROR);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(500);
    }

    #[Test]
    public function it_can_return_unauthenticated_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::UNAUTHENTICATED);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_return_forbidden_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::PERMISSION_DENY);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(403);
    }

    #[Test]
    public function it_can_return_too_many_request_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::TOO_MANY_REQUEST);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(429);
    }

    #[Test]
    public function it_can_return_db_error_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::DB_ERROR);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(500);
    }

    #[Test]
    public function it_can_return_missing_files_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::MISSING_FILES);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(500);
    }

    #[Test]
    public function it_can_return_permission_deny_template_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::PERMISSION_DENY);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(403);
    }

    /**
     * -------------------------------------------
     * | v1.x methods tests
     * -------------------------------------------
     */
    #[Test]
    public function it_can_return_classic_bad_request_response() 
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::badRequest();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(400);
    }
    #[Test]
    public function it_can_return_classic_not_found_response() 
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::notFound();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(404);
    }
    #[Test]
    public function it_can_return_classic_server_error_response() 
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::serverError();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(500);
    }
    #[Test]
    public function it_can_return_classic_validation_fail_response()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            $errors = [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ];
            return Response::validationFail($errors);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(422);
    }
    #[Test]
    public function it_can_return_classic_unauthenticated_response() 
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::unauthenticated();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(401);
    }
    #[Test]
    public function it_can_return_classic_forbidden_response() 
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::forbidden();
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Variation tests
    |--------------------------------------------------------------------------
    */
    #[Test]
    public function it_can_return_template_with_data()
    {
        /** arrange */
        $testData = ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            return Response::with(ResponseCode::SUCCESS, $testData);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertJsonFragment(['data' => $testData]);
    }

    #[Test]
    public function it_can_return_template_and_modify_message()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::SUCCESS)
                ->modify(function ($response) {
                    $response->message = 'Custom success message';
                });
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Custom success message']);
    }

    #[Test]
    public function it_can_return_template_and_modify_methodcode()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            return Response::with(ResponseCode::SUCCESS)
                ->modify(function ($response) {
                    $response->method = ResponseCode::METHOD_TOAST;
                });
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        $response->assertJsonFragment(['code' => ResponseCode::METHOD_TOAST . 'x' . ResponseCode::SUCCESS]);
    }

    #[Test]
    public function it_can_return_template_and_modify_errors()
    {
        /** arrange */
        $customErrors = [
            'field1' => ['Custom error 1'],
            'field2' => ['Custom error 2', 'Another error 2']
        ];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $customErrors) {
            return Response::with(ResponseCode::VALIDATION_ERROR)
                ->modify(function ($response) use ($customErrors) {
                    $response->errors = $customErrors;
                });
        });
        Route::get('handler/tests', fn() => $controller->businessLogic());

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(422);
        $response->assertJsonFragment(['errors' => $customErrors]);
    }
}
