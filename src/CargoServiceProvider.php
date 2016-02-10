<?php

namespace FabianPimminger\Cargo;

use Illuminate\Support\ServiceProvider;

class CargoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //require __DIR__ . '/vendor/autoload.php';

        $this->publishes([
            __DIR__.'/config/cargo.php' => config_path('cargo.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__.'/config/cargo.php', 'cargo'
        );
        
        //dd("hi");
    }
}
