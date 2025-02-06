<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HideAdminNavigation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    public function handle(Request $request, Closure $next)
    {
        // Set a configuration value based on the session value
        config([
            'filament.navigation_enabled' => session('groupID') === 'admin',
        ]);

        return $next($request);
    }
}
