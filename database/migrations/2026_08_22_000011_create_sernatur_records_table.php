<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // NOTA: módulo construido pero no activo para el piloto de Cajón del Maipo
    // (ver decisión de Fase 0 — catastro manual/admin es la fuente principal).
    public function up(): void
    {
        Schema::create('sernatur_records', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('category')->nullable(); // alojamiento, restaurante, turismo_aventura, etc.
            $table->string('registration_status')->nullable();
            $table->json('raw_payload')->nullable(); // fila original tal cual llegó de la fuente

            $table->string('source')->default('sernatur');
            $table->string('source_url')->nullable();
            $table->string('source_type')->nullable(); // api | csv
            $table->string('source_record_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->decimal('geocoding_confidence', 4, 3)->nullable();

            $table->timestamps();

            $table->index('rut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sernatur_records');
    }
};
