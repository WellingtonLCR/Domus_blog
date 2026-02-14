<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function loginAdmin(string $usuario, string $senha): array
    {
        $admin = Admin::where('usuario', $usuario)->first();

        if (!$admin || !Hash::check($senha, $admin->senha)) {
            throw ValidationException::withMessages([
                'usuario' => ['Credenciais inválidas'],
            ]);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return [
            'admin' => $admin,
            'token' => $token,
            'type' => 'Bearer',
        ];
    }

    public function loginUsuario(string $usuario, string $senha): array
    {
        $usuario = Usuario::where('usuario', $usuario)->first();

        if (!$usuario || !Hash::check($senha, $usuario->senha)) {
            throw ValidationException::withMessages([
                'usuario' => ['Credenciais inválidas'],
            ]);
        }

        if ($usuario->isBanned()) {
            throw ValidationException::withMessages([
                'usuario' => ['Usuário está banido'],
            ]);
        }

        $token = $usuario->createToken('usuario-token')->plainTextToken;

        return [
            'usuario' => $usuario,
            'token' => $token,
            'type' => 'Bearer',
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAll($user): void
    {
        $user->tokens()->delete();
    }
}
