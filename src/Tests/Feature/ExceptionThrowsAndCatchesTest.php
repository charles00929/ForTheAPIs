<?php

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Testing\Fluent\AssertableJson;
use BWTV\ForTheAPIs\Tests\Utilities\RuntimeSetup;
use BWTV\ForTheAPIs\Tests\Utilities\ResponseFormat;
use BWTV\ForTheAPIs\Exceptions\ResponseException;

class ExceptionThrowsAndCatchesTest extends TestCase
{
    use RuntimeSetup, ResponseFormat;


    #[Test]
    public function it_would_go_fallback_try_to_go_not_exist_route()
    {
        /** arrange */
        // 不需要設定路由，以觸發 API Handler 的 fallback 處理

        /** act */
        $response = $this->get('api/non-existent-route');

        /** assert */
        $response->assertStatus(404);
        $this->assertResponseFormat($response);

        // 檢查是否有預設的 fallback 訊息
        // ROUTE_NOT_EXIST 的 code
        $response->assertJsonPath('code', '00x9805');
    }

    #[Test]
    public function it_can_render_the_exception_with_defined_code()
    {
        /** arrange */
        config()->set('api-handler.exception_responses.9966', [
            'message' => 'Unit Test',
            'status' => 400,
            'method' => ResponseCode::METHOD_TOAST,
        ]);
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            throw new ResponseException('9966');
                
        });
        Route::get('handler/custom-exception-test', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/custom-exception-test');

        /** assert */
        $response->assertStatus(400);
        $this->assertResponseFormat($response);

        // 檢查是否使用了配置中設定的響應格式
        $response->assertJsonPath('message', 'Unit Test'); // 使用配置中的 message
        $response->assertJsonPath('code', '01x9966'); // METHOD_TOAST + 自定義碼 9966
    }

    #[Test]
    public function it_can_catch_on_syntax_error()
    {
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            //模擬語法錯誤 - array 裡沒有這個key
            $array = []; // 定義空陣列
            $getNotExist = $array['item']; // 這會產生 undefined index 錯誤
        });
        Route::get('handler/runtime-error-test', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/runtime-error-test');

        /** assert */
        $response->assertStatus(500);
        $this->assertResponseFormat($response);

        // 檢查是否被 API Handler 正確處理為統一格式
        $response->assertJsonPath('code', '00x9900'); // SERVER_ERROR

        // 在開發環境中應該包含錯誤詳情
        if (config('app.debug')) {
            $response->assertJsonStructure(['debug']);
        }
    }

    #[Test]
    public function it_can_catch_on_form_validation_fail(){
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            // 模擬表單驗證失敗
            $form = request()->validate(['validation' => ['required']]);

            return Response::with(ResponseCode::SUCCESS, $form);
        });
        Route::get('handler/validation-error-test', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/validation-error-test');

        /** assert */
        $response->assertStatus(422);
        $this->assertResponseFormat($response);

        // 檢查是否有 validation error 的訊息
        $response->assertJsonPath('code', '00x9700'); // VALIDATION_ERROR
    }

    #[Test]
    public function it_can_catch_on_authentication_fail()
    {
        /** arrange */
        // 設定測試用的 api guard
        config()->set('auth.guards.api', [
            'driver' => 'token',
            'provider' => 'users',
        ]);
        
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () {
            return Response::with(ResponseCode::SUCCESS, ['authenticated' => true]);
        });
        Route::get('handler/auth-error-test', fn() => $controller->businessLogic())
            ->middleware(['fta.api', 'auth:api']); // fta.api 在前，確保 APIExceptionHandler 被設定

        /** act */
        // 設定為 API 請求並不帶 token，觸發未驗證例外
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('handler/auth-error-test');

        /** assert */
        $response->assertStatus(401);
        $this->assertResponseFormat($response);

        // 檢查是否有 authentication error 的 code
        $response->assertJsonPath('code', '00x9801'); // UNAUTHENTICATED
    }

    #[Test]
    public function it_can_catch_on_query_error(){
        /** arrange */
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller) {
            // 模擬查詢錯誤
            DB::table('non_existent_table')->get(); // 這會觸發查詢錯誤
        });
        Route::get('handler/query-error-test', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/query-error-test');

        /** assert */
        $response->assertStatus(500);
        $this->assertResponseFormat($response);

        // 檢查是否有 query error 的 code
        $response->assertJsonPath('code', '00x9922'); // SERVER_ERROR
    }
}
