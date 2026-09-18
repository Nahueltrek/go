<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo comercial GO Chile §21: registra visitas y clics de contacto por
 * organización, para poder mostrarle a los fundadores el valor real que
 * genera su perfil ("recibiste X visitas, X clics a WhatsApp...").
 * Una fila por evento (no un contador agregado) para poder cortar por
 * fecha más adelante sin perder granularidad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_metric_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['visit', 'whatsapp_click', 'phone_click', 'website_click', 'instagram_click']);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['organization_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_metric_events');
    }
};
