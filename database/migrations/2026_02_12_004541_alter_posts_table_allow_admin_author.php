<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Remove a foreign key constraint para permitir Admin como autor
            $table->dropForeign(['usuario_id']);
            // Mantém o campo usuario_id mas sem constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Restaura a foreign key constraint
            $table->foreign('usuario_id')->constrained('usuarios');
        });
    }
};
