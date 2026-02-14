<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nome',
        'usuario',
        'senha',
        'biografia',
        'banido_em',
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    protected $casts = [
        'data_criacao' => 'datetime',
        'data_alteracao' => 'datetime',
        'banido_em' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function isBanned()
    {
        return !is_null($this->banido_em);
    }
}
