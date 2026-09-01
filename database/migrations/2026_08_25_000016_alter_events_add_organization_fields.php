<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Extiende el Event ya existente en rm360 (destination_id, business_id,
     * title, slug, description, cover_image, starts_at, ends_at) para que
     * pueda pertenecer también a una Organization (operadores nuevos de
     * GO Chile), y agrega los campos que pedía el plan de Agenda GO Chile:
     * categoría, capacidad, precio, dificultad y estado de publicación.
     *
     * business_id se conserva intacto — un evento puede seguir asociado a
     * un Business existente de rm360 sin cambios.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('business_id')
                ->constrained('organizations')->nullOnDelete();
            $table->enum('category', [
                'salidas', 'formacion', 'conservacion', 'cicloturismo', 'wellness', 'turismo', 'fotografia',
            ])->nullable()->after('description');
            $table->unsignedInteger('capacity')->nullable()->after('ends_at');
            $table->decimal('price', 10, 0)->nullable()->after('capacity');
            $table->enum('difficulty', ['facil', 'medio', 'dificil', 'experto'])->nullable()->after('price');
            $table->enum('status', ['draft', 'published', 'cancelled'])->default('published')->after('difficulty');
        });

        // Backfill: el taller fijo actual de GO Chile ("Taller NDR + Práctico
        // en Terreno"), si ya está cargado como Event vía business_id, se
        // categoriza como 'formacion'. No se crea el registro acá — solo se
        // deja la categoría por defecto para eventos existentes sin categoría.
        DB::table('events')->whereNull('category')->update(['category' => 'formacion']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn(['category', 'capacity', 'price', 'difficulty', 'status']);
        });
    }
};
