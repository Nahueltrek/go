<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('active_layers')->nullable(); // capas de mapa habilitadas para este destino
            // Polígono territorial del destino — clave para "no hardcodear Cajón del Maipo".
            // Se deja nullable (un destino puede crearse antes de tener el polígono
            // dibujado — de hecho el propio seeder de Cajón del Maipo no lo trae aún)
            // y por eso NO lleva índice spatial: MariaDB exige NOT NULL para indexar.
            // Si más adelante todos los destinos tienen boundary cargado, se puede
            // agregar el índice en una migración separada.
            $table->geography('boundary', subtype: 'polygon', srid: 4326)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
