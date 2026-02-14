<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function adminLogin(Request $request): JsonResponse
    {
        $request->validate([
            'usuario' => 'required|string',
            'senha' => 'required|string',
        ]);

        try {
            $result = $this->authService->loginAdmin(
                $request->usuario,
                $request->senha
            );

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de autenticação',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function usuarioLogin(Request $request): JsonResponse
    {
        $request->validate([
            'usuario' => 'required|string',
            'senha' => 'required|string',
        ]);

        try {
            $result = $this->authService->loginUsuario(
                $request->usuario,
                $request->senha
            );

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erro de autenticação',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ], 200);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json([
            'message' => 'Todos os tokens foram revogados com sucesso'
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $userType = $user instanceof \App\Models\Admin ? 'admin' : 'usuario';

        return response()->json([
            'message' => 'Dados do usuário recuperados com sucesso',
            'data' => [
                'type' => $userType,
                'user' => $user
            ]
        ], 200);
    }
}
