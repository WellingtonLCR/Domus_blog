<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComentarioResource extends JsonResource
{
    /**
     * Transforma o modelo Comentario em um array para resposta JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'usuario_id' => $this->usuario_id,
            'texto' => $this->texto,
            'data_criacao' => $this->data_criacao,
            'post' => new PostResource($this->whenLoaded('post')),
            'usuario' => new UsuarioResource($this->whenLoaded('usuario')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
