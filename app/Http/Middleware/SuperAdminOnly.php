<?php


namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\UserController;
class SuperAdminOnly
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Доступ разрешён только суперадминистратору');
        }

        return $next($request);
    }
}

