<?php

namespace Chameleon\Yigim;

use Illuminate\Support\ServiceProvider;

class YigimServiceProvider extends ServiceProvider
{
    /**
     * Boot
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/yigim.php' => config_path('yigim.php'),
        ]);
    }

    /**
     * Register
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/yigim.php',
            'yigim'
        );
        $this->app->singleton(Yigim::class, function () {
            return Yigim::create($this->app['config']->get('yigim'));
        });
        $this->app->alias(Yigim::class, 'yigim');
    }
}
