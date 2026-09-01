<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('difficulty')->nullable();
            // Traza de la ruta (línea) — usada para "negocios cercanos a esta ruta".
            // Nullable porque una ruta puede crearse con sus datos básicos antes de
            // subir el track GPS; por eso, igual que boundary en destinations, no
            // lleva índice spatial (MariaDB lo exige NOT NULL).
            $table->geography('path', subtype: 'linestring', srid: 4326)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
