<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege rotas por papel (role).
 *
 * Uso nas rotas:
 *   ->middleware('role:admin')
 *   ->middleware('role:manager,admin')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso não autorizado.'], 403);
            }

            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
