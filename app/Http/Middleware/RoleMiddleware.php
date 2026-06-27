<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan pengguna telah log masuk
        if (! auth()->check()) {
            abort(401, 'Pengguna belum log masuk');
        }

        // Semak peranan pengguna
        if (! in_array(auth()->user()->role, $roles)) {
            abort(403, 'Akses tidak dibenarkan');
        }

        return $next($request);
    }
}
