<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        // Del den nåværende brukeren og teamet med alle Vue-komponenter
        Inertia::share([
            'auth' => [
                'user' => function () {
                    if (Auth::check()) {
                        $user = Auth::user();
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            // Andre nødvendige felt kan legges til her
                        ];
                    }
                    return null;
                },
                'team' => function () {
                    if (Auth::check()) {
                        $currentTeam = Auth::user()->currentTeam; // Anta at bruker har en currentTeam relasjon
                        return $currentTeam ? [
                            'id' => $currentTeam->id,
                            'name' => $currentTeam->name,
                            // Andre nødvendige felt kan legges til her
                        ] : null;
                    }
                    return null;
                },
            ],
        ]);
    }
}
