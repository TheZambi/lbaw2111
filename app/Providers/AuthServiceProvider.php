<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
      'App\Models\Item' => 'App\Policies\ItemPolicy',
      'App\Models\Review' => 'App\Policies\ReviewPolicy',
      'App\Models\User' => 'App\Policies\UserPolicy',
      'App\Models\Notification' => 'App\Policies\NotificationPolicy'

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
