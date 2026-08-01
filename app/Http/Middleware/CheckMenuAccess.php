<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Hard-block akses route untuk staff yang tidak punya permission menu terkait.
     * Admin selalu lolos (canAccessMenu auto-return true).
     */
    public function handle(Request $request, Closure $next, string $menuKey): Response
    {
        if (!auth()->check() || !auth()->user()->canAccessMenu($menuKey)) {
            abort(403, 'Anda tidak memiliki akses ke menu ini.');
        }

        return $next($request);
    }
}
