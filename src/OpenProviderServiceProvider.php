<?php

namespace jacktalkc\LaravelOpenProvider;

use Illuminate\Support\ServiceProvider;
use OP_API;

class OpenProviderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/openprovider.php', 'openprovider'
        );

        $this->app->singleton('openprovider', function ($app) {
            $config = $app['config']['openprovider'];

            $api = new OP_API(
                $config['url'],
                $config['timeout'] ?? 1000
            );

            return new OpenProviderService($api, $config);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/openprovider.php' => config_path('openprovider.php'),
            ], 'openprovider-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/openprovider'),
            ], 'openprovider-views');
        }
    }
}
