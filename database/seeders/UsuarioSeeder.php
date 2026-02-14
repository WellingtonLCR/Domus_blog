<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = [
            [
                'nome' => 'João Silva',
                'usuario' => 'joao',
                'senha' => Hash::make('senha123'),
                'biografia' => 'Desenvolvedor e entusiasta de tecnologia.',
            ],
            [
                'nome' => 'Maria Santos',
                'usuario' => 'maria',
                'senha' => Hash::make('senha123'),
                'biografia' => 'Designer gráfico e fotógrafa.',
            ],
            [
                'nome' => 'Pedro Oliveira',
                'usuario' => 'pedro',
                'senha' => Hash::make('senha123'),
                'biografia' => 'Engenheiro de software.',
            ],
            [
                'nome' => 'Ana Costa',
                'usuario' => 'ana',
                'senha' => Hash::make('senha123'),
                'biografia' => 'Professora e escritora.',
            ],
            [
                'nome' => 'Carlos Ferreira',
                'usuario' => 'carlos',
                'senha' => Hash::make('senha123'),
                'biografia' => 'Músico e compositor.',
            ],
        ];

        foreach ($usuarios as $usuario) {
            Usuario::updateOrCreate(
                ['usuario' => $usuario['usuario']],
                $usuario
            );
        }
    }
}
