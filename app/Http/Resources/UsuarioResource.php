<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transforma o modelo Usuario em um array para resposta JSON.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'usuario' => $this->usuario,
            'biografia' => $this->biografia,
            'data_criacao' => $this->data_criacao,
            'data_alteracao' => $this->data_alteracao,
            'banido_em' => $this->banido_em,
            'is_banned' => $this->isBanned(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
