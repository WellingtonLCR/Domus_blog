<?php

namespace App\DTOs;

class ComentarioDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $post_id,
        public readonly ?int $usuario_id,
        public readonly string $texto
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            post_id: $data['post_id'],
            usuario_id: $data['usuario_id'] ?? null,
            texto: $data['texto']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'usuario_id' => $this->usuario_id,
            'texto' => $this->texto,
        ];
    }
}
