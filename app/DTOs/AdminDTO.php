<?php

namespace App\DTOs;

class AdminDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nome,
        public readonly string $usuario,
        public readonly ?string $senha
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nome: $data['nome'],
            usuario: $data['usuario'],
            senha: $data['senha'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'nome' => $this->nome,
            'usuario' => $this->usuario,
            'senha' => $this->senha,
        ], fn($value) => $value !== null);
    }
}
