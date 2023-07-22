<?php

namespace Firevel\RequestLogger\Providers;

use Firevel\RequestLogger\Services\QueryLogger;
use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/request-logger.php', 'request-logger');

        $this->app->singleton(QueryLogger::class, function ($app) {
            return new QueryLogger();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Listen to database queries.
        if (config('request-logger.log.queries')) {
            app(QueryLogger::class)->listen();
        }

        $this->publishes(
            [
                __DIR__.'/../config/request-logger.php' => config_path('request-logger.php'),
            ],
            'config'
        );
    }
}
