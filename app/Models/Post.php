<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'usuario_id',
        'titulo',
        'slug',
        'status',
        'data_publicacao',
        'texto',
    ];

    protected $casts = [
        'data_criacao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_publicacao' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->titulo);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('titulo') && empty($post->slug)) {
                $post->slug = Str::slug($post->titulo);
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function scopeWithComments(Builder $query)
    {
        return $query->has('comentarios');
    }

    public function scopeByStatus(Builder $query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser(Builder $query, $userId)
    {
        return $query->where('usuario_id', $userId);
    }

    public function scopeWithCommentsByUser(Builder $query, $userId)
    {
        return $query->whereHas('comentarios', function ($q) use ($userId) {
            $q->where('usuario_id', $userId);
        });
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('data_criacao', [$startDate, $endDate]);
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('status', 'publicado');
    }

    public function isPublished()
    {
        return $this->status === 'publicado';
    }

    public function isDraft()
    {
        return $this->status === 'rascunho';
    }

    public function isArchived()
    {
        return $this->status === 'arquivado';
    }
}
