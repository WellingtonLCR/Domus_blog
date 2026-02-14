<?php

namespace App\Services;

use App\Models\Usuario;
use App\DTOs\UsuarioDTO;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    public function create(UsuarioDTO $dto): Usuario
    {
        return Usuario::create([
            'nome' => $dto->nome,
            'usuario' => $dto->usuario,
            'senha' => Hash::make($dto->senha),
            'biografia' => $dto->biografia,
        ]);
    }

    public function update(Usuario $usuario, UsuarioDTO $dto): Usuario
    {
        $updateData = [
            'nome' => $dto->nome,
            'usuario' => $dto->usuario,
            'biografia' => $dto->biografia,
            'data_alteracao' => now(),
        ];

        if ($dto->senha) {
            $updateData['senha'] = Hash::make($dto->senha);
        }

        if ($dto->banido_em !== null) {
            $updateData['banido_em'] = $dto->banido_em;
        }

        $usuario->update($updateData);
        return $usuario->fresh();
    }

    public function ban(Usuario $usuario): Usuario
    {
        $usuario->update([
            'banido_em' => now(),
            'data_alteracao' => now(),
        ]);
        
        return $usuario->fresh();
    }

    public function unban(Usuario $usuario): Usuario
    {
        $usuario->update([
            'banido_em' => null,
            'data_alteracao' => now(),
        ]);
        
        return $usuario->fresh();
    }

    public function delete(Usuario $usuario): bool
    {
        return $usuario->delete();
    }

    public function findById(int $id): ?Usuario
    {
        return Usuario::find($id);
    }

    public function findByUsuario(string $usuario): ?Usuario
    {
        return Usuario::where('usuario', $usuario)->first();
    }

    public function getAll(int $perPage = 15)
    {
        return Usuario::paginate($perPage);
    }

    public function getNonBanned(int $perPage = 15)
    {
        return Usuario::whereNull('banido_em')->paginate($perPage);
    }

    public function getBanned(int $perPage = 15)
    {
        return Usuario::whereNotNull('banido_em')->paginate($perPage);
    }
}
