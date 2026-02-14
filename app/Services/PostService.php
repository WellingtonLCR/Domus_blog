<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Usuario;
use App\DTOs\PostDTO;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostService
{
    public function create(Usuario $usuario, PostDTO $dto): Post
    {
        $data = $dto->toArray();
        $data['usuario_id'] = $usuario->id;
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['titulo']);
        }

        $dataPublicacao = $data['data_publicacao'] ?? null;

        if (($data['status'] ?? null) === 'publicado' && !$dataPublicacao) {
            $data['data_publicacao'] = now();
        }

        return Post::create($data);
    }

    public function update(Post $post, PostDTO $dto): Post
    {
        $data = $dto->toArray();
        $data['data_alteracao'] = now();

        if (isset($data['titulo']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['titulo']);
        }

        $dataPublicacao = $data['data_publicacao'] ?? null;

        if (isset($data['status']) && $data['status'] === 'publicado' && !$dataPublicacao) {
            $data['data_publicacao'] = now();
        }

        $post->update(array_filter($data, fn($value) => $value !== null));
        return $post->fresh();
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function findById(int $id): ?Post
    {
        return Post::find($id);
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Post::with(['usuario', 'comentarios.usuario']);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['with_comments']) && $filters['with_comments']) {
            $query->withComments();
        }

        if (isset($filters['usuario_id'])) {
            $query->byUser($filters['usuario_id']);
        }

        if (isset($filters['comment_usuario_id'])) {
            $query->withCommentsByUser($filters['comment_usuario_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['show_banned']) && !$filters['show_banned']) {
            $query->whereHas('usuario', function (Builder $q) {
                $q->whereNull('banido_em');
            });
        }

        $query->orderBy('data_criacao', 'desc');

        return $query->paginate($perPage);
    }

    public function getPublished(int $perPage = 15)
    {
        return Post::published()
            ->with(['usuario', 'comentarios.usuario'])
            ->whereHas('usuario', function (Builder $q) {
                $q->whereNull('banido_em');
            })
            ->orderBy('data_criacao', 'desc')
            ->paginate($perPage);
    }

    public function getByUser($usuario, int $perPage = 15)
    {
        return Post::where('usuario_id', $usuario->id)
            ->with(['usuario', 'comentarios.usuario'])
            ->orderBy('data_criacao', 'desc')
            ->paginate($perPage);
    }

    public function publish(Post $post): Post
    {
        $post->update([
            'status' => 'publicado',
            'data_publicacao' => $post->data_publicacao ?: now(),
            'data_alteracao' => now(),
        ]);
        
        return $post->fresh();
    }

    public function archive(Post $post): Post
    {
        $post->update([
            'status' => 'arquivado',
            'data_alteracao' => now(),
        ]);
        
        return $post->fresh();
    }

    public function draft(Post $post): Post
    {
        $post->update([
            'status' => 'rascunho',
            'data_alteracao' => now(),
        ]);
        
        return $post->fresh();
    }
}
