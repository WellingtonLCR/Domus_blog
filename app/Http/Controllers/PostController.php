<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\QueryException;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = [
            'status' => $request->get('status'),
            'with_comments' => $request->boolean('with_comments'),
            'usuario_id' => $request->get('usuario_id'),
            'comment_usuario_id' => $request->get('comment_usuario_id'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'show_banned' => $request->boolean('show_banned', false),
        ];

        $posts = $this->postService->getAll($filters, $perPage);

        return response()->json([
            'message' => 'Posts recuperados com sucesso',
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function published(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->getPublished($perPage);

        return response()->json([
            'message' => 'Posts publicados recuperados com sucesso',
            'data' => PostResource::collection($posts)
        ], 200);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $usuario = $request->user();
            
            // Permite tanto Admin quanto Usuario criar posts
            if (!$usuario) {
                return response()->json([
                    'message' => 'Não autorizado',
                ], 403);
            }

            $data = $request->validated();
            $data['usuario_id'] = $usuario->id;
            
            if (empty($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['titulo']);
            }
            
            $post = Post::create($data);

            return response()->json([
                'message' => 'Post criado com sucesso',
                'data' => [
                    'id' => $post->id,
                    'titulo' => $post->titulo,
                    'status' => $post->status
                ]
            ], 201);
        } catch (QueryException $e) {
            $message = $e->getMessage();
            $isUniqueViolation = str_contains($message, 'UNIQUE constraint failed')
                || str_contains($message, 'Integrity constraint violation');

            if ($isUniqueViolation) {
                return response()->json([
                    'message' => 'Este título ou slug já está em uso.',
                ], 422);
            }

            return response()->json([
                'message' => 'Erro ao criar post',
                'error' => $message,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        // Tenta obter o usuário autenticado (via request ou guard Sanctum)
        $user = auth('sanctum')->user() ?? $request->user();

        // Sempre carregamos o autor para aplicar as regras abaixo
        $post->load(['usuario', 'comentarios.usuario']);
        $autor = $post->usuario;

        // Regra 1: qualquer pessoa pode ver posts publicados de autores não banidos
        if ($post->status === 'publicado' && $autor && !$autor->isBanned()) {
            // Liberado para visitantes, usuários e admin
        } else {
            // Para rascunhos/arquivados ou posts de autores banidos,
            // só permitimos acesso autenticado conforme regras abaixo.
            if (!$user) {
                return response()->json([
                    'message' => 'Não autorizado',
                ], 403);
            }

            // Admin pode ver qualquer post
            if ($user instanceof \App\Models\Admin) {
                // permitido
            } elseif ($user instanceof Usuario) {
                // Usuário comum só vê seus próprios posts e se não estiver banido
                if ($user->id !== $post->usuario_id || $user->isBanned()) {
                    return response()->json([
                        'message' => 'Não autorizado',
                    ], 403);
                }
            } else {
                // Qualquer outro tipo inesperado é bloqueado
                return response()->json([
                    'message' => 'Não autorizado',
                ], 403);
            }
        }

        return response()->json([
            'message' => 'Post recuperado com sucesso',
            'data' => new PostResource($post)
        ], 200);
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        Gate::authorize('update-post', $post);
        $dto = \App\DTOs\PostDTO::fromArray($request->validated());
        $post = $this->postService->update($post, $dto);

        return response()->json([
            'message' => 'Post atualizado com sucesso',
            'data' => new PostResource($post)
        ], 200);
    }

    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete-post', $post);
        $this->postService->delete($post);

        return response()->json([
            'message' => 'Post excluído com sucesso'
        ], 200);
    }

    public function publish(Post $post): JsonResponse
    {
        Gate::authorize('update-post', $post);
        $post = $this->postService->publish($post);

        return response()->json([
            'message' => 'Post publicado com sucesso',
            'data' => new PostResource($post)
        ], 200);
    }

    public function archive(Post $post): JsonResponse
    {
        Gate::authorize('update-post', $post);
        $post = $this->postService->archive($post);

        return response()->json([
            'message' => 'Post arquivado com sucesso',
            'data' => new PostResource($post)
        ], 200);
    }

    public function draft(Post $post): JsonResponse
    {
        Gate::authorize('update-post', $post);
        $post = $this->postService->draft($post);

        return response()->json([
            'message' => 'Post movido para rascunho com sucesso',
            'data' => new PostResource($post)
        ], 200);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $usuario = $request->user();

        // Permite tanto Admin quanto Usuario ver seus posts
        if (!$usuario) {
            return response()->json([
                'message' => 'Não autorizado',
            ], 403);
        }

        $perPage = $request->get('per_page', 15);
        $posts = $this->postService->getByUser($usuario, $perPage);

        return response()->json([
            'message' => 'Seus posts recuperados com sucesso',
            'data' => PostResource::collection($posts)
        ], 200);
    }
}
