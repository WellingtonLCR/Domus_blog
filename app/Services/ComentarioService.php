<?php

namespace App\Services;

use App\Models\Comentario;
use App\Models\Post;
use App\Models\Usuario;
use App\DTOs\ComentarioDTO;
use Illuminate\Database\Eloquent\Builder;

class ComentarioService
{
    public function create(Usuario $usuario, Post $post, ComentarioDTO $dto): Comentario
    {
        return Comentario::create([
            'post_id' => $post->id,
            'usuario_id' => $usuario->id,
            'texto' => $dto->texto,
        ]);
    }

    public function delete(Comentario $comentario): bool
    {
        return $comentario->delete();
    }

    public function findById(int $id): ?Comentario
    {
        return Comentario::find($id);
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Comentario::with(['post.usuario', 'usuario']);

        if (isset($filters['post_id'])) {
            $query->where('post_id', $filters['post_id']);
        }

        if (isset($filters['usuario_id'])) {
            $query->where('usuario_id', $filters['usuario_id']);
        }

        if (isset($filters['show_banned']) && !$filters['show_banned']) {
            $query->whereHas('usuario', function (Builder $q) {
                $q->whereNull('banido_em');
            });
        }

        $query->orderBy('data_criacao', 'desc');

        return $query->paginate($perPage);
    }

    public function getByPost(Post $post, int $perPage = 15)
    {
        return $post->comentarios()
            ->with('usuario')
            ->whereHas('usuario', function (Builder $q) {
                $q->whereNull('banido_em');
            })
            ->orderBy('data_criacao', 'desc')
            ->paginate($perPage);
    }

    public function getByUser(Usuario $usuario, int $perPage = 15)
    {
        return $usuario->comentarios()
            ->with(['post.usuario'])
            ->orderBy('data_criacao', 'desc')
            ->paginate($perPage);
    }
}
