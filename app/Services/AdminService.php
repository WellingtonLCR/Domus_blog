<?php

namespace App\Services;

use App\Models\Admin;
use App\DTOs\AdminDTO;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function create(AdminDTO $dto): Admin
    {
        return Admin::create([
            'nome' => $dto->nome,
            'usuario' => $dto->usuario,
            'senha' => Hash::make($dto->senha),
        ]);
    }

    public function update(Admin $admin, AdminDTO $dto): Admin
    {
        $updateData = [
            'nome' => $dto->nome,
            'usuario' => $dto->usuario,
        ];

        if ($dto->senha) {
            $updateData['senha'] = Hash::make($dto->senha);
        }

        $admin->update($updateData);
        return $admin->fresh();
    }

    public function delete(Admin $admin): bool
    {
        return $admin->delete();
    }

    public function findById(int $id): ?Admin
    {
        return Admin::find($id);
    }

    public function findByUsuario(string $usuario): ?Admin
    {
        return Admin::where('usuario', $usuario)->first();
    }

    public function getAll(int $perPage = 15)
    {
        return Admin::paginate($perPage);
    }
}
