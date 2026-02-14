<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UsuarioController extends Controller
{
    protected UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('create-admin');
        $perPage = $request->get('per_page', 15);
        // Admin visualiza todos os usuários (banidos e não banidos)
        $usuarios = $this->usuarioService->getAll($perPage);

        return response()->json([
            'message' => 'Usuários recuperados com sucesso',
            'data' => UsuarioResource::collection($usuarios)
        ], 200);
    }

    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $dto = \App\DTOs\UsuarioDTO::fromArray($request->validated());
        $usuario = $this->usuarioService->create($dto);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'data' => new UsuarioResource($usuario)
        ], 201);
    }

    public function show(Usuario $usuario): JsonResponse
    {
        Gate::authorize('create-admin');
        return response()->json([
            'message' => 'Usuário recuperado com sucesso',
            'data' => new UsuarioResource($usuario)
        ], 200);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): JsonResponse
    {
        Gate::authorize('manage-usuario', $usuario);
        $dto = \App\DTOs\UsuarioDTO::fromArray($request->validated());
        $usuario = $this->usuarioService->update($usuario, $dto);

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'data' => new UsuarioResource($usuario)
        ], 200);
    }

    public function destroy(Usuario $usuario): JsonResponse
    {
        Gate::authorize('manage-usuario', $usuario);
        $this->usuarioService->delete($usuario);

        return response()->json([
            'message' => 'Usuário excluído com sucesso'
        ], 200);
    }

    public function ban(Usuario $usuario): JsonResponse
    {
        Gate::authorize('ban-usuario', $usuario);
        $usuario = $this->usuarioService->ban($usuario);

        return response()->json([
            'message' => 'Usuário banido com sucesso',
            'data' => new UsuarioResource($usuario)
        ], 200);
    }

    public function unban(Usuario $usuario): JsonResponse
    {
        Gate::authorize('unban-usuario', $usuario);
        $usuario = $this->usuarioService->unban($usuario);

        return response()->json([
            'message' => 'Usuário desbanido com sucesso',
            'data' => new UsuarioResource($usuario)
        ], 200);
    }
}
