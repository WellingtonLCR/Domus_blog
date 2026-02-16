<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ComentarioController;

Route::prefix('auth')->group(function () {
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
    Route::post('usuario/login', [AuthController::class, 'usuarioLogin']);
    Route::middleware(['auth:sanctum', 'auth.banned'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
    });
});

Route::prefix('usuarios')->group(function () {
    Route::post('/', [UsuarioController::class, 'store']);
});

Route::prefix('posts')->group(function () {
    Route::get('published', [PostController::class, 'published']);
    Route::get('{post}', [PostController::class, 'show']);
});

Route::prefix('comentarios')->group(function () {
    Route::get('post/{post}', [ComentarioController::class, 'byPost']);
});

Route::middleware(['auth:sanctum', 'auth.banned'])->group(function () {
    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::get('{admin}', [AdminController::class, 'show']);
        Route::put('{admin}', [AdminController::class, 'update']);
        Route::delete('{admin}', [AdminController::class, 'destroy']);
    });

    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::get('{usuario}', [UsuarioController::class, 'show']);
        Route::put('{usuario}', [UsuarioController::class, 'update']);
        Route::delete('{usuario}', [UsuarioController::class, 'destroy']);
        Route::post('{usuario}/ban', [UsuarioController::class, 'ban']);
        Route::post('{usuario}/unban', [UsuarioController::class, 'unban']);
    });

    Route::prefix('posts')->group(function () {
        // Rotas autenticadas para gestão de posts
        Route::get('/', [PostController::class, 'index']);
        Route::get('auth/{post}', [PostController::class, 'show']);
        Route::post('/', [PostController::class, 'store']);
        Route::put('{post}', [PostController::class, 'update']);
        Route::delete('{post}', [PostController::class, 'destroy']);
        Route::post('{post}/publish', [PostController::class, 'publish']);
        Route::post('{post}/archive', [PostController::class, 'archive']);
        Route::post('{post}/draft', [PostController::class, 'draft']);
        Route::get('my-posts', [PostController::class, 'myPosts']);
    });

    Route::prefix('comentarios')->group(function () {
        Route::get('/', [ComentarioController::class, 'index']);
        Route::post('/', [ComentarioController::class, 'store']);

        // Rotas específicas devem vir antes da rota parametrizada {comentario}
        Route::get('my-comments', [ComentarioController::class, 'myComments']);
        Route::get('my-posts', [ComentarioController::class, 'commentsOnMyPosts']);

        Route::get('{comentario}', [ComentarioController::class, 'show']);
        Route::delete('{comentario}', [ComentarioController::class, 'destroy']);
    });
});
