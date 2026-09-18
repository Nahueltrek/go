<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint Bitácora GO 1.0 — prepara la Bitácora para conectarse
 * progresivamente con Ruta/Experiencia/Proyecto, con el mismo patrón
 * simple (belongsTo nullable) que ya usan related_organization_id y
 * related_destination_id. No se implementa una arquitectura de grafo
 * nueva; solo se dejan las columnas listas para usarse cuando haya
 * contenido real que las necesite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('related_route_id')->nullable()
                ->after('related_destination_id')
                ->constrained('routes')->nullOnDelete();
            $table->foreignId('related_experience_id')->nullable()
                ->after('related_route_id')
                ->constrained('experiences')->nullOnDelete();
            $table->foreignId('related_project_id')->nullable()
                ->after('related_experience_id')
                ->constrained('projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('related_project_id');
            $table->dropConstrainedForeignId('related_experience_id');
            $table->dropConstrainedForeignId('related_route_id');
        });
    }
};
