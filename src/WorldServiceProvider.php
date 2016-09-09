<?php
/**
 *  Laravel-CreatedByPolicy (http://github.com/malhal/Laravel-CreatedByPolicy)
 *
 *  Created by Malcolm Hall on 9/9/2016.
 *  Copyright © 2016 Malcolm Hall. All rights reserved.
 */

namespace Malhal\CreatedByPolicy;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class WorldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Laravel policies only work if the user isn't null so for world reads or writes we need to assign a dummpy user.
        // From now on to check for guest use is_null(Auth::user()->getKey())
        if(!Auth::check()) {
            $userClass = config('auth.providers.users.model');
            Auth::setUser(new $userClass());
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
