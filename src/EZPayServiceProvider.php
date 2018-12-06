<?php
namespace TsaiYiHua\EZPay;

use Illuminate\Support\ServiceProvider;

class EZPayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerConfigs();
        }
        $this->registerResources();
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    protected function registerConfigs()
    {
        $this->publishes([
            __DIR__ . '/../config/ezpay.php' => config_path('ezpay.php')
        ], 'ezpay');
    }

    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ezpay');
    }
}