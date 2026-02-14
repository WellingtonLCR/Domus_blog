<?php

namespace App\DTOs;

class PostDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $usuario_id,
        public readonly string $titulo,
        public readonly ?string $slug,
        public readonly string $status,
        public readonly ?\DateTime $data_publicacao,
        public readonly string $texto
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            usuario_id: $data['usuario_id'] ?? null,
            titulo: $data['titulo'],
            slug: $data['slug'] ?? null,
            status: $data['status'] ?? 'rascunho',
            data_publicacao: isset($data['data_publicacao']) ? new \DateTime($data['data_publicacao']) : null,
            texto: $data['texto']
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'usuario_id' => $this->usuario_id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'status' => $this->status,
            'data_publicacao' => $this->data_publicacao?->format('Y-m-d H:i:s'),
            'texto' => $this->texto,
        ], fn($value) => $value !== null);
    }
}
