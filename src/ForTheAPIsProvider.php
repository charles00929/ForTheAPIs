<?php

namespace BWTV\ForTheAPIs;

use Illuminate\Support\ServiceProvider;
use BWTV\ForTheAPIs\Middleware\APIHandling;

class ForTheAPIsProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 載入配置文件
        $this->mergeConfigFrom(__DIR__ . '/Configs/for-the-apis.php', 'for-the-apis');
        
        $this->loadRoutesFrom(__DIR__ . '/Routes/fallback.php');

        $this->publishes([
            __DIR__ . '/Configs/for-the-apis.php' => config_path('for-the-apis.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/Enums/ResponseCode.php.stub' => app_path('Enums/ResponseCode.php'),
        ], 'stub');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * A middleware that scoped-bind an ExceptionHandler on the specific routes
         */
        $alias = config('for-the-apis.middleware_alias', 'fta.api');
        $this->app->get('router')->aliasMiddleware($alias, APIHandling::class);
    }
}
