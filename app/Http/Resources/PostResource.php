<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transforma o modelo Post em um array pronto para resposta JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'status' => $this->status,
            'data_criacao' => $this->data_criacao,
            'data_alteracao' => $this->data_alteracao,
            'data_publicacao' => $this->data_publicacao,
            'texto' => $this->texto,
            'is_published' => $this->isPublished(),
            'is_draft' => $this->isDraft(),
            'is_archived' => $this->isArchived(),
            'usuario' => new UsuarioResource($this->whenLoaded('usuario')),
            'comentarios' => ComentarioResource::collection($this->whenLoaded('comentarios')),
            'comentarios_count' => $this->whenCounted('comentarios'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
