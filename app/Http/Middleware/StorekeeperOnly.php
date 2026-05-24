<?php

namespace App\Http\Middleware;

use Closure;

class StorekeeperOnly
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'storekeeper') {
            abort(403, 'Разрешено только кладовщику');
        }

        return $next($request);
    }
}
