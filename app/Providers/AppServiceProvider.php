<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar layout por defecto para componentes Livewire
        Livewire::component('login', \App\Livewire\Login::class);

        // El resto de componentes usarán el layout por defecto
    }
}
