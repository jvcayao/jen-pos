<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureUrls();
    }

    private function configureModels(): void
    {
        Model::shouldBeStrict(!app()->isProduction());
        Model::unguard(false);
    }

    private function configureUrls(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
