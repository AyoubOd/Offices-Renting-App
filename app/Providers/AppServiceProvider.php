<?php

namespace App\Providers;

use App\Models\Office;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // We don't need the protection against the mass assignement attack
        // we always validata the request before using it 
        Model::unguard();

        Relation::enforceMorphMap([
            'office' => Office::class,
            'user' => User::class
        ]);
    }
}
