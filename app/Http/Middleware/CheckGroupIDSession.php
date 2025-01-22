<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGroupIDSession
{
   /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if groupID is not set in session
        if (!session()->has('groupID')) {
            // Redirect to /your-groups if groupID is not in session
            return redirect('/admin/your-roles')->with('error', 'Group ID is required!');
        }

        return $next($request);
    }
}
