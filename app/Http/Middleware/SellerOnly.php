<?php

namespace App\Http\Middleware;

use Closure;

class SellerOnly
{
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'seller') {
            abort(403, 'Разрешено только продавцу');
        }

        return $next($request);
    }
}
