# API Handler

一個統一 API Response 格式的 Laravel Library，提供標準化的 JSON Response 格式、異常處理和路由 Fallback 機制。

## 特色功能

- ✅ **統一 Response 格式** - 所有 API Response 使用一致的 JSON 結構
- ✅ **預建 Response 方法** - 常用的成功/錯誤 Response 快捷方法
- ✅ **智能數據處理** - 自動處理 Collection、Pagination、Resource 等數據類型
- ✅ **自動異常處理** - 捕獲並轉換異常為統一 Response 格式
- ✅ **路由 Fallback 機制** - 統一處理不存在的路由
- ✅ **可配置訊息** - 支援自定義預設訊息
- ✅ **回應碼系統** - 結構化的回應碼管理
- ✅ **Debug支援** - 開發環境下提供詳細錯誤訊息

## 系統需求

- Laravel 10.x 或更高版本
- PHP 8.1 或更高版本

## 安裝與配置

### 1. 服務提供者註冊

API Handler 使用 Laravel 自動發現機制，無需手動註冊。

### 2. 發布配置文件

```bash
# 發布配置檔案
php artisan vendor:publish --provider="BWTV\ForTheAPIs\ForTheAPIsProvider" --tag="config"

# 發布回應碼模板（可選）
php artisan vendor:publish --provider="BWTV\ForTheAPIs\ForTheAPIsProvider" --tag="stub"
```

### 3. Middleware 配置

在路由中使用 `fta.api` middleware：

```php
// 路由群組
Route::middleware(['fta.api'])->prefix('api')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
});

// 或單一路由
Route::get('/users', [UserController::class, 'index'])->middleware('fta.api');
```

## 基本使用

### 1. 控制器中使用

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use BWTV\ForTheAPIs\Response;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return Response::done($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users'
        ]);

        $user = User::create($request->validated());
        return Response::done($user, 'User created successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return Response::notFound('User not found');
        }
        
        return Response::done($user, 'User retrieved successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        
        return Response::done([], 'User deleted successfully');
    }
}
```

### 2. Response 格式範例

**成功 Response：**
```json
{
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "message": "User retrieved successfully",
    "code": "00x0000"
}
```

**列表 Response（Collection）：**
```json
{
    "data": {
        "list": [
            {"id": 1, "name": "John Doe"},
            {"id": 2, "name": "Jane Smith"}
        ]
    },
    "message": "Users retrieved successfully",
    "code": "00x0000"
}
```

**分頁 Response（Pagination）：**
```json
{
    "data": {
        "list": [
            {"id": 1, "name": "John Doe"},
            {"id": 2, "name": "Jane Smith"}
        ]
    },
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15
    },
    "message": "Users retrieved successfully",
    "code": "00x0000"
}
```

**錯誤 Response：**
```json
{
    "data": [],
    "message": "User not found",
    "code": "00x9804"
}
```

**表單驗證錯誤：**
```json
{
    "data": [],
    "message": "Validation failed.",
    "code": "00x9700",
    "errors": {
        "email": ["The email field is required."],
        "name": ["The name field is required."]
    }
}
```

## 預建 Response 方法

### 成功 Response

```php
// 基本成功 Response
Response::done($data, $message, $method);

// 等同於：
Response::with(ResponseCode::SUCCESS, $data)
    ->modify(function ($response) use ($message, $method) {
        $response->message = $message;
        $response->method = $method;
    });
```

### 錯誤 Response

```php
// 客戶端錯誤（400）
Response::badRequest($reason, $method);

// 未找到（404）
Response::notFound($reason, $method);

// 服務器錯誤（500）
Response::serverError($reason, $method, $code);

// 身份驗證錯誤（401）
Response::unauthenticated($reason, $method);

// 權限拒絕（403）
Response::forbidden($reason, $method);

// 表單驗證錯誤（422）
Response::validationFail($errors, $method);
```

### 使用範例

```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users'
    ]);

    if ($validator->fails()) {
        return Response::validationFail($validator->errors());
    }

    try {
        $user = User::create($request->validated());
        return Response::done($user, 'User created successfully');
    } catch (Exception $e) {
        return Response::serverError('Failed to create user');
    }
}
```

## 數據類型自動處理

### 1. 普通數組/對象
```php
$data = ['id' => 1, 'name' => 'John'];
Response::done($data);
// 輸出：{"data": {"id": 1, "name": "John"}}
```

### 2. Laravel Collection
```php
$collection = collect([
    ['id' => 1, 'name' => 'User 1'],
    ['id' => 2, 'name' => 'User 2']
]);
Response::done($collection);
// 輸出：{"data": {"list": [...]}}
```

### 3. Eloquent Model
```php
$user = User::find(1);
Response::done($user);
// 輸出：{"data": {"id": 1, "name": "John", ...}}
```

### 4. 分頁數據（Paginator）
```php
$users = User::paginate(15);
Response::done($users);
// 輸出：包含 data.list 和 pagination 字段
```

### 5. 資源集合（ResourceCollection）
```php
$users = User::all();
$collection = UserResource::collection($users);
Response::done($collection);
// 輸出：{"data": {"list": [...]}}
```

### 6. 使用 Resource::collection() 與分頁
```php
$users = User::paginate(15);
$collection = UserResource::collection($users);
Response::done($collection);
// 自動處理分頁並包裝在 list 中
```

## 回應碼系統

### 回應碼結構

回應碼由方法碼（2位）+ 狀態碼（4位）組成：

```
格式：{method}x{code}
範例：
"00x0000" // 無特殊操作 + 成功
"01x9800" // 顯示提示 + 客戶端錯誤
"02x9900" // 確認對話框 + 服務器錯誤
```

### 方法碼定義

```php
const METHOD_NONE = '00';     // 無特殊操作
const METHOD_TOAST = '01';    // 顯示提示訊息
const METHOD_CONFIRM = '02';  // 顯示確認對話框
```

### 狀態碼定義

```php
// 成功
const SUCCESS = '0000';

// 表單類錯誤
const VALIDATION_ERROR = '9700';

// 客戶端錯誤
const BAD_REQUEST = '9800';
const UNAUTHORIZED = '9801';
const PERMISSION_DENY = '9803';
const NOT_EXIST = '9804';
const ENTITY_NOT_EXIST = '9804';
const ROUTE_NOT_EXIST = '9805';
const TOO_MANY_REQUEST = '9829';

// 服務器錯誤
const SERVER_ERROR = '9900';
const UNDER_MAINTAINACE = '9903';
const MISSING_FILES = '9904';
const DB_ERROR = '9922';
const CACHE_ERROR = '9963';
```

### 自定義回應碼

發布回應碼模板後，在 `app/Enums/ResponseCode.php` 中擴展：

```php
<?php

namespace App\Enums;

use BWTV\ForTheAPIs\Enums\ResponseCode as BaseResponseCode;

class ResponseCode extends BaseResponseCode
{
    // 業務邏輯回應碼
    const BUSINESS_VALIDATION_ERROR = '1001';
    const PAYMENT_FAILED = '2001';
    const INVENTORY_INSUFFICIENT = '3001';
    const EMAIL_SEND_FAILED = '4001';
}
```

使用自定義回應碼：

```php
use App\Enums\ResponseCode;

Response::serverError(
    'Payment processing failed',
    ResponseCode::METHOD_TOAST,
    ResponseCode::PAYMENT_FAILED
);
```

## 自動異常處理

使用 `fta.api` middleware 後，以下異常會自動處理：

- **ValidationException** → 400 Response，包含驗證錯誤
- **AuthenticationException** → 401 Response
- **AccessDeniedHttpException** → 403 Response  
- **NotFoundHttpException** → 404 Response
- **HttpException** → 對應狀態碼 Response
- **QueryException** → 500 Response，數據庫錯誤
- **ModelNotFoundException** → 404 Response
- **其他異常** → 500 Response

### 自定義異常處理

```php
class CustomException extends Exception
{
    public function render($request)
    {
        return Response::badRequest(
            'Custom exception occurred',
            ResponseCode::METHOD_TOAST
        );
    }
}
```

## 路由 Fallback 機制

當訪問不存在的 API 路由時，自動返回統一格式的 404 Response：

```json
{
    "data": [],
    "message": "The route is not recognized.",
    "code": "00x9805"
}
```

Fallback 機制僅處理以 `api` 前綴開頭的路由（可在配置中修改）。

## 配置

### 配置文件 `config/api-handler.php`
請使用 `php artisan vendor:publish --provider="BWTV\ForTheAPIs\ForTheAPIsProvider" --tag="config"` 將設定檔放置到專案內

```php
<?php

use BWTV\ForTheAPIs\Enums\ResponseCode;

return [
    /**
     * 預設訊息配置
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
     * 處理範圍 - 只處理匹配此前綴的路由
     */
    'handle_scope' => env('BWTV_FORTHEAPIS_PREFIX', 'api'),

    /**
     * Middleware 別名
     */
    'middleware_alias' => 'fta.api',

    /**
     * 自定義異常回應配置
     * 當拋出 ResponseException 時使用
     * 建議使用 ResponseCode 類的常數作為鍵值
     */
    'exception_responses' => [
        // 範例：
        ResponseCode::SERVER_ERROR => [
            'message' => 'Server internal error.',
            'status' => 500,
            'method' => ResponseCode::METHOD_NONE,
        ],
        
        // 可在此處新增更多自定義異常回應
        // '自定義回應碼' => [
        //     'message' => '自定義錯誤訊息',
        //     'status' => HTTP狀態碼,
        //     'method' => 回應方法碼,
        // ],
    ],
];
```

### 環境變數

```env
# .env 文件中設定
BWTV_FORTHEAPIS_PREFIX=api
```

## Debug模式

### 啟用Debug訊息

在 `.env` 文件中設置：
```env
APP_DEBUG=true
```

### Debug Response 格式

Debug模式下，Response 會包含額外的Debug訊息：

```json
{
    "data": [],
    "message": "Server internal error",
    "code": "00x9900",
    "debug": "SQLSTATE[42S02]: Base table or view not found: 1146 Table 'database.users' doesn't exist"
}
```

### 自定義Debug訊息

```php
$response = Response::done($data);
$response->debug = 'Custom debug information';
return $response;
```

## 進階用法

### 1. 使用 with() 和 modify() 方法

API Handler 提供了更靈活的 Response 建構方式：

#### with() 方法
使用回應碼建立基礎 Response：

```php
use BWTV\ForTheAPIs\Enums\ResponseCode;

// 基礎用法
$response = Response::with(ResponseCode::SUCCESS, $data);

// 不同回應碼
$response = Response::with(ResponseCode::BAD_REQUEST);
$response = Response::with(ResponseCode::VALIDATION_ERROR);
$response = Response::with(ResponseCode::UNAUTHENTICATED);
```

#### modify() 方法
用於修改已建立的 Response：

```php
$response = Response::with(ResponseCode::SUCCESS, $data)
    ->modify(function ($response) {
        $response->message = 'Custom success message';
        $response->method = ResponseCode::METHOD_TOAST;
    });

// 複雜的修改範例
$response = Response::with(ResponseCode::VALIDATION_ERROR)
    ->modify(function ($response) {
        $response->message = 'Custom validation message';
        $response->method = ResponseCode::METHOD_TOAST;
        $response->errors = [
            'email' => ['Email is required'],
            'password' => ['Password must be at least 8 characters']
        ];
    });
```

#### 組合使用範例

```php
public function updateProfile(Request $request)
{
    $user = auth()->user();
    
    // 驗證失敗
    if ($validation->fails()) {
        return Response::with(ResponseCode::VALIDATION_ERROR)
            ->modify(function ($response) use ($validation) {
                $response->errors = $validation->errors();
                $response->method = ResponseCode::METHOD_TOAST;
            });
    }
    
    // 成功更新
    $user->update($request->validated());
    
    return Response::with(ResponseCode::SUCCESS, $user)
        ->modify(function ($response) {
            $response->message = 'Profile updated successfully';
            $response->method = ResponseCode::METHOD_TOAST;
        });
}
```

#### 條件式修改

```php
$response = Response::with(ResponseCode::SUCCESS, $data);

if ($showToast) {
    $response->modify(function ($resp) {
        $resp->method = ResponseCode::METHOD_TOAST;
    });
}

if (app()->environment('local')) {
    $response->modify(function ($resp) {
        $resp->debug = 'Development environment debug info';
    });
}

return $response;
```

### 2. 帶方法碼的 Response

```php
use BWTV\ForTheAPIs\Enums\ResponseCode;

// 需要顯示提示訊息的 Response
Response::done($data, 'Operation completed', ResponseCode::METHOD_TOAST);

// 需要確認的 Response
Response::done($data, 'Are you sure?', ResponseCode::METHOD_CONFIRM);
```

### 3. 自定義 Response 建構

```php
$response = new Response(
    $data,
    'Custom message',
    200,
    ResponseCode::METHOD_TOAST,
    ResponseCode::SUCCESS
);

// 額外訊息
$response->debug = 'Debug information';
$response->errors = ['field' => 'error message'];

return $response;
```

### 4. 條件 Response

```php
public function getUserData(Request $request)
{
    $user = User::find($request->user_id);
    
    if (!$user) {
        return Response::notFound('User not found');
    }
    
    if (!$user->isActive()) {
        return Response::forbidden('User account is disabled');
    }
    
    return Response::done($user->toArray());
}
```

## 最佳實踐

### 1. 基礎控制器

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use BWTV\ForTheAPIs\Response;
use BWTV\ForTheAPIs\Enums\ResponseCode;

class BaseAPIController extends Controller
{
    protected function success($data = [], $message = 'Success')
    {
        return Response::done($data, $message);
    }

    protected function error($message = 'Error', $code = null)
    {
        return Response::badRequest($message);
    }

    protected function notFound($message = 'Resource not found')
    {
        return Response::notFound($message);
    }
}
```

### 3. 表單驗證處理

```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8'
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password)
    ]);

    return Response::done($user, 'User created successfully');
}
```

## 常見問題

### Q: 為什麼我的Response沒有使用統一格式？
A: 確保路由使用了 `fta.api` middleware。

### Q: 如何自定義預設訊息？
A: 發布配置文件後，在 `config/api-handler.php` 中修改 `messages` 陣列。

### Q: 如何處理自定義異常？
A: 在異常類中實現 `render()` 方法，返回 `Response` 實例。

### Q: 分頁數據為什麼被包裝在 `list` 中？
A: 為了保持 JSON Response中 `data` 欄位始終為對象類型，提高前端處理一致性。

### Q: 如何自定義業務邏輯異常？
A: 可以在 `config/api-handler.php` 中配置自定義異常回應：
```php
'exception_responses' => [
    '9966' => [
        'message' => 'Custom business logic error',
        'status' => 400,
        'method' => ResponseCode::METHOD_TOAST,
    ],
],
```
然後在業務邏輯中拋出：
```php
throw new ResponseException('9966');
```