<?php

namespace App\Http\Controllers;

use App\Services\ComentarioService;
use App\Http\Requests\StoreComentarioRequest;
use App\Http\Resources\ComentarioResource;
use App\Models\Comentario;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ComentarioController extends Controller
{
    protected ComentarioService $comentarioService;

    public function __construct(ComentarioService $comentarioService)
    {
        $this->comentarioService = $comentarioService;
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('create-admin');
        $perPage = $request->get('per_page', 15);
        $filters = [
            'post_id' => $request->get('post_id'),
            'usuario_id' => $request->get('usuario_id'),
            'show_banned' => $request->boolean('show_banned', false),
        ];

        $comentarios = $this->comentarioService->getAll($filters, $perPage);

        return response()->json([
            'message' => 'Comentários recuperados com sucesso',
            'data' => ComentarioResource::collection($comentarios)
        ], 200);
    }

    public function store(StoreComentarioRequest $request): JsonResponse
    {
        $usuario = $request->user();
        $post = Post::findOrFail($request->post_id);
        Gate::authorize('create-comentario', $post);
        $dto = \App\DTOs\ComentarioDTO::fromArray($request->validated());
        $comentario = $this->comentarioService->create($usuario, $post, $dto);

        return response()->json([
            'message' => 'Comentário criado com sucesso',
            'data' => new ComentarioResource($comentario)
        ], 201);
    }

    public function show(Comentario $comentario): JsonResponse
    {
        Gate::authorize('view-comentario', $comentario);
        $comentario->load(['post.usuario', 'usuario']);

        return response()->json([
            'message' => 'Comentário recuperado com sucesso',
            'data' => new ComentarioResource($comentario)
        ], 200);
    }

    public function destroy(Comentario $comentario): JsonResponse
    {
        Gate::authorize('delete-comentario', $comentario);
        $this->comentarioService->delete($comentario);

        return response()->json([
            'message' => 'Comentário excluído com sucesso'
        ], 200);
    }

    public function byPost(Request $request, Post $post): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $comentarios = $this->comentarioService->getByPost($post, $perPage);

        return response()->json([
            'message' => 'Comentários do post recuperados com sucesso',
            'data' => ComentarioResource::collection($comentarios)
        ], 200);
    }

    public function myComments(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $perPage = $request->get('per_page', 15);
        $comentarios = $this->comentarioService->getByUser($usuario, $perPage);

        return response()->json([
            'message' => 'Seus comentários recuperados com sucesso',
            'data' => ComentarioResource::collection($comentarios)
        ], 200);
    }

    public function commentsOnMyPosts(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $perPage = $request->get('per_page', 15);
        $comentarios = $this->comentarioService->getByPostAuthor($usuario, $perPage);

        return response()->json([
            'message' => 'Comentários nos seus posts recuperados com sucesso',
            'data' => ComentarioResource::collection($comentarios)
        ], 200);
    }
}
