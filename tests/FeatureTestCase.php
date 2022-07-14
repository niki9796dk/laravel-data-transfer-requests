<?php

namespace Tests;

use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase;
use Tests\TestApplication\Controllers\TestController;
use Niki9796dk\LaravelDataTransferRequests\ServiceProviders\LaravelDataRequestServiceProvider;

class FeatureTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelDataRequestServiceProvider::class,
        ];
    }

    /**
     * @param Router $router
     *
     * @return void
     */
    protected function defineRoutes($router): void
    {
        $router->post('/single-level-data', [TestController::class, 'singleLevelData']);
        $router->post('/required-field', [TestController::class, 'requiredField']);
        $router->post('/nested-field', [TestController::class, 'nestedField']);
    }

}
