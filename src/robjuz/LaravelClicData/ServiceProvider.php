<?php
namespace robjuz\LaravelClicData;

use Illuminate\Foundation\Application;
use robjuz\LaravelClicData\Controllers\OAuthController;
use robjuz\LaravelClicData\Exceptions\ConfigurationException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider

{
    /**
     * Publish config
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('clicdata.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Register package
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerServices();

        $this->app->make(OAuthController::class);
    }

    /**
     * Merge config
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php',
            'clicdata'
        );
    }

    /**
     * Register services
     */
    protected function registerServices()
    {
        // PodioService
        $this->app->bind(LaravelClicData::class, function (Application $app) {
            /** @var array $config */
            $config = config('clicdata');

            if (!$config) {
                ConfigurationException::message('Please provide a ClicData configuration');
            }

            return new LaravelClicData($config);
        });
    }
}
