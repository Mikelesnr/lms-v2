<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use App\Mail\Transports\GmailApiTransport;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;

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
        Vite::prefetch(concurrency: 3);

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Register custom Gmail API transport
        Mail::extend('gmail_api', function (array $config = []) {
            return new GmailApiTransport();
        });

        // Define gates for authorization
        Gate::define('system-admin-panel', function ($user) {
            return $user->role === UserRole::SYSTEM_ADMIN;
        });

        Gate::define('system-staff-panel', function ($user) {
            return $user->role === UserRole::SYSTEM_STAFF || $user->role === UserRole::SYSTEM_TECHNICIAN || $user->role === UserRole::SYSTEM_ACCOUNTANT || $user->role === UserRole::SYSTEM_ADMIN;
        });

        Gate::define('system-technician-panel', function ($user) {
            return $user->role === UserRole::SYSTEM_TECHNICIAN || $user->role === UserRole::SYSTEM_ADMIN;
        });

        Gate::define('system-accountant-panel', function ($user) {
            return $user->role === UserRole::SYSTEM_ACCOUNTANT || $user->role === UserRole::SYSTEM_ADMIN;
        });

        Gate::define('instructor-panel', function ($user) {
            return $user->role === UserRole::INSTRUCTOR || $user->role === UserRole::SYSTEM_ADMIN;
        });

        Gate::define('student-panel', function ($user) {
            return $user->role === UserRole::STUDENT || $user->role === UserRole::SYSTEM_ADMIN;
        });

    }
}
