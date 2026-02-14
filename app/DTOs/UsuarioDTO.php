<?php

namespace App\DTOs;

class UsuarioDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nome,
        public readonly string $usuario,
        public readonly ?string $senha,
        public readonly ?string $biografia,
        public readonly ?\DateTime $banido_em
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nome: $data['nome'],
            usuario: $data['usuario'],
            senha: $data['senha'] ?? null,
            biografia: $data['biografia'] ?? null,
            banido_em: isset($data['banido_em']) ? new \DateTime($data['banido_em']) : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'nome' => $this->nome,
            'usuario' => $this->usuario,
            'senha' => $this->senha,
            'biografia' => $this->biografia,
            'banido_em' => $this->banido_em?->format('Y-m-d H:i:s'),
        ], fn($value) => $value !== null);
    }
}
