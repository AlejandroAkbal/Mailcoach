<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class BetterLaravelDefaultsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Fix: make URL scheme HTTPS on "production" environment
        // https://stackoverflow.com/a/62111949/11398632
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Remove keys from arrays that are not validated
        Validator::excludeUnvalidatedArrayKeys();

        // Enable strict mode
        Model::shouldBeStrict(! app()->isProduction());
    }
}
