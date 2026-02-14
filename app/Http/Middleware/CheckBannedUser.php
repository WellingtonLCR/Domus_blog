<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Manipula uma requisição autenticada verificando se o usuário está banido.
     *
     * Se o campo `banido_em` estiver preenchido o acesso é bloqueado
     * para todos os endpoints protegidos por este middleware.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user instanceof \App\Models\Usuario && $user->isBanned()) {
            return response()->json([
                'message' => 'Usuário está banido e não pode realizar esta ação',
                'error' => 'banned_user'
            ], 403);
        }

        return $next($request);
    }
}
