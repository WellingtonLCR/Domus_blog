<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\Usuario;
use App\Models\Post;
use App\Models\Comentario;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerGates();
    }

    private function registerGates(): void
    {
        Gate::define('create-admin', function ($user) {
            return $user instanceof Admin;
        });

        Gate::define('ban-usuario', function ($user, Usuario $usuario) {
            return $user instanceof Admin;
        });

        Gate::define('unban-usuario', function ($user, Usuario $usuario) {
            return $user instanceof Admin;
        });

        Gate::define('manage-usuario', function ($user, Usuario $targetUsuario) {
            return $user instanceof Usuario && $user->id === $targetUsuario->id;
        });

        Gate::define('create-post', function ($user) {
            return $user instanceof Usuario && !$user->isBanned();
        });

        Gate::define('update-post', function ($user, Post $post) {
            return $user instanceof Usuario && $user->id === $post->usuario_id && !$user->isBanned();
        });

        Gate::define('delete-post', function ($user, Post $post) {
            if ($user instanceof Admin) {
                return true;
            }
            return $user instanceof Usuario && $user->id === $post->usuario_id && !$user->isBanned();
        });

        Gate::define('view-post', function ($user, Post $post) {
            $autor = $post->usuario;

            if ($post->status === 'publicado') {
                if ($autor && $autor->isBanned()) {
                    return $user instanceof Admin;
                }

                return $autor ? !$autor->isBanned() : false;
            }

            if ($user instanceof Admin) {
                return true;
            }

            return $user instanceof Usuario && $user->id === $post->usuario_id && !$user->isBanned();
        });

        Gate::define('create-comentario', function ($user, Post $post) {
            $autor = $post->usuario;
            $postVisivel = $post->status === 'publicado' && $autor && !$autor->isBanned();

            return $user instanceof Usuario && !$user->isBanned() && $postVisivel;
        });

        Gate::define('delete-comentario', function ($user, Comentario $comentario) {
            if ($user instanceof Admin) {
                return true;
            }
            return $user instanceof Usuario && $user->id === $comentario->usuario_id && !$user->isBanned();
        });

        Gate::define('view-comentario', function ($user, Comentario $comentario) {
            $autor = $comentario->usuario;

            if ($autor && $autor->isBanned()) {
                return $user instanceof Admin;
            }

            return $autor ? !$autor->isBanned() : false;
        });
    }
}
