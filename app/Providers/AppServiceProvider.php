<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
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
        try {
            if (Schema::hasTable('settings')) {
                Setting::all()->each(function (Setting $setting) {
                    config(['app.' . $setting->key => $setting->value]);
                });
            }
        } catch (\Exception $e) {
            // Ignore database access exceptions during early bootstrap.
        }
    }
}
