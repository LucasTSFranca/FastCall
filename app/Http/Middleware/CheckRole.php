<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege rotas por perfil.
 *
 * Uso nas rotas:
 *   ->middleware('role:administrador')
 *   ->middleware('role:gerente,administrador')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->perfil, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Acesso não autorizado.'], 403);
            }

            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
