<?php

namespace App\Providers;

use App\Models\Shift;
use App\Observers\ShiftObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();
        Shift::observe(ShiftObserver::class);

        if (config('app.env') == 'production') {
            URL::forceScheme('https');
        }
    }
}
