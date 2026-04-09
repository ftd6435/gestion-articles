<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return $next($request);
        }

        if (str_starts_with($routeName, 'livewire.')) {
            return $next($request);
        }

        if ($routeName === 'settings.profile' || $routeName === 'logout') {
            return $next($request);
        }

        if (!$user->hasAnyAccess()) {
            return redirect()->route('settings.profile');
        }

        $routePermissions = (array) config('access.route_permissions', []);
        if (!array_key_exists($routeName, $routePermissions)) {
            return abort(403);
        }

        $permissionKey = $routePermissions[$routeName];
        if ($permissionKey === null) {
            return $next($request);
        }

        $routeAbilities = (array) config('access.route_abilities', []);
        $ability = (string) ($routeAbilities[$routeName] ?? 'view');
        if (!$user->canAccess($permissionKey, $ability)) {
            return abort(403);
        }

        return $next($request);
    }
}
