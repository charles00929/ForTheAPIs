<?php
namespace BWTV\ForTheAPIs\Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;
use Illuminate\Pagination\LengthAwarePaginator;
use BWTV\ForTheAPIs\Tests\Utilities\RuntimeSetup;
use BWTV\ForTheAPIs\Tests\Utilities\ResponseFormat;

class ReturnDataTest extends TestCase
{
    use ResponseFormat, RuntimeSetup;

    #[Test]
    public function it_can_return_resource()
    {
        /** arrange */
        $testData = ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            // 使用 mock resource
            $resource = $this->createMockResource($testData);
            return Response::with(ResponseCode::SUCCESS, $resource->toArray(request()));
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        // 檢查資源數據是否正確包含
        $response->assertJsonPath('data.id', 1);
        $response->assertJsonPath('data.name', 'Test User');
    }

    /**
     * 使用 Resource::collection() 傳入資料
     */
    #[Test]
    public function it_can_return_resource_of_collection()
    {
        /** arrange */
        $testData = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
            ['id' => 3, 'name' => 'User 3']
        ];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            // 使用 Laravel 原生的 Resource::collection() 方式
            $resourceClass = $this->createMockResource([]);
            $collection = $resourceClass::collection($testData);
            return Response::with(ResponseCode::SUCCESS, $collection);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        // 檢查 collection 是否被包裝在 'list' 中
        $response->assertJsonPath('data.list.0.id', 1);
        $response->assertJsonPath('data.list.1.id', 2);
        $response->assertJsonPath('data.list.2.id', 3);
        $response->assertJsonCount(3, 'data.list');
    }

    /**
     * 使用 Resource::collection() 與 LengthAwarePaginator 傳入資料
     */
    #[Test]
    public function it_can_return_resource_of_pagination()
    {
        /** arrange */
        $testData = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
            ['id' => 3, 'name' => 'User 3']
        ];
        $paginationData = [
            'current_page' => 1,
            'per_page' => 10,
            'total' => 3,
            'last_page' => 1,
            'from' => 1,
            'to' => 3
        ];

        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData, $paginationData) {
            // 使用 Laravel 原生的 LengthAwarePaginator
            $paginator = new LengthAwarePaginator(
                $testData,
                $paginationData['total'],
                $paginationData['per_page'],
                $paginationData['current_page'],
                [
                    'path' => 'http://localhost',
                    'pageName' => 'page',
                ]
            );
            
            // 使用 Resource::collection() 包裝分頁資料
            $resourceClass = $this->createMockResource([]);
            $collection = $resourceClass::collection($paginator);
            
            return Response::with(ResponseCode::SUCCESS, $collection);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);

        // 檢查分頁資料結構
        $response->assertJsonPath('data.list.0.id', 1);
        $response->assertJsonPath('data.list.1.id', 2);
        $response->assertJsonPath('data.list.2.id', 3);
        $response->assertJsonCount(3, 'data.list');
        // 檢查分頁資訊（注意：分頁資訊在根層級的 pagination 欄位中）
        $response->assertJsonPath('pagination.current_page', 1);
        $response->assertJsonPath('pagination.per_page', 10);
        $response->assertJsonPath('pagination.total', 3);
        $response->assertJsonPath('pagination.last_page', 1);
    }

    /**
     * 直接使用 done() 傳入 Model
     */
    #[Test]
    public function it_can_return_model_directly()
    {
        /** arrange */
        $testData = ['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com'];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            // 直接使用 mock model
            $model = $this->createMockModel($testData);
            return Response::with(ResponseCode::SUCCESS, $model);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        // 檢查 model 數據是否正確包含
        $response->assertJsonPath('data.id', 1);
        $response->assertJsonPath('data.name', 'Test User');
        $response->assertJsonPath('data.email', 'test@example.com');
    }

    /**
     * 直接使用 done() 傳入 Collection
     */
    #[Test]
    public function it_can_return_collection_directly()
    {
        /** arrange */
        $testData = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
            ['id' => 3, 'name' => 'User 3']
        ];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            // 直接使用 Laravel Collection
            $collection = collect($testData);
            return Response::with(ResponseCode::SUCCESS, $collection);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        // 檢查 collection 是否被包裝在 'list' 中
        $response->assertJsonPath('data.list.0.id', 1);
        $response->assertJsonPath('data.list.1.id', 2);
        $response->assertJsonPath('data.list.2.id', 3);
        $response->assertJsonCount(3, 'data.list');
    }

    /**
     * 直接使用 done() 傳入 LengthAwarePaginator
     */
    #[Test]
    public function it_can_return_paginator_directly()
    {
        /** arrange */
        $testData = [
            ['id' => 1, 'name' => 'User 1'],
            ['id' => 2, 'name' => 'User 2'],
            ['id' => 3, 'name' => 'User 3']
        ];
        $controller = $this->createController();
        $controller->newMethod('businessLogic', function () use ($controller, $testData) {
            // 直接使用 Laravel LengthAwarePaginator
            $paginator = new LengthAwarePaginator(
                $testData,
                count($testData),
                10,
                1,
                [
                    'path' => 'http://localhost',
                    'pageName' => 'page',
                ]
            );
            return Response::with(ResponseCode::SUCCESS, $paginator);
        });
        Route::get('handler/tests', fn() => $controller->businessLogic())
            ->middleware('fta.api');

        /** act */
        $response = $this->get('handler/tests');

        /** assert */
        $this->assertResponseFormat($response);
        $response->assertStatus(200);
        // 檢查分頁資料結構
        $response->assertJsonPath('data.list.0.id', 1);
        $response->assertJsonPath('data.list.1.id', 2);
        $response->assertJsonPath('data.list.2.id', 3);
        $response->assertJsonCount(3, 'data.list');
        // 檢查分頁資訊
        $response->assertJsonPath('pagination.current_page', 1);
        $response->assertJsonPath('pagination.per_page', 10);
        $response->assertJsonPath('pagination.total', 3);
        $response->assertJsonPath('pagination.last_page', 1);
    }
}
