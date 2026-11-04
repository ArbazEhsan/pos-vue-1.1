<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();

        // If user is not logged in, let auth middleware handle it, or fail.
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        // Check if user has the permission record
        if (!$user->permission && $user->type != 'Admin') {
             // If no permission record exists, maybe deny or allow? 
             // Usually deny if we are checking for specific permission.
             abort(403, 'Unauthorized. No permissions assigned.');
        }

        // Check the specific permission in the 'pages' JSON column
        $pages = $user->permission->pages??[];

        if (!is_array($pages) || empty($pages[$permission]) && $user->type != 'Admin') {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
