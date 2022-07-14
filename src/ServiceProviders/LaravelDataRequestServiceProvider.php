<?php

namespace Primen\LaravelDataTransferRequests\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use Primen\LaravelDataTransferRequests\DataTransferRequest;

class LaravelDataRequestServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->resolving(DataTransferRequest::class, function (DataTransferRequest $request, $app) {
            $request->setData($app['request']->all());
        });
    }
}
