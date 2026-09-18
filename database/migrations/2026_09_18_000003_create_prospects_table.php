<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM de prospectos/fundadores (GO_CHILE_MODELO_COMERCIAL.md §15-16 y §31):
 * la base de 100 prospectos que el equipo GO Chile contacta a mano para el
 * programa de 20 fundadores. Independiente de collaboration_requests (esa
 * es la postulación pública entrante; esta es la prospección saliente del
 * equipo comercial, con su propio pipeline de estados).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('contact_name')->nullable();
            $table->string('territory')->nullable();
            $table->string('category')->nullable();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('observed_problem')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', [
                'prospecto', 'contactado', 'respondio', 'demo', 'fundador',
                'activo', 'conversion_pro', 'no_responde', 'no_interesado',
                'volver_a_contactar', 'requiere_informacion', 'cerrado',
            ])->default('prospecto');
            $table->timestamp('last_contacted_at')->nullable();
            // Se completa al convertir el prospecto en Organization (botón
            // "Crear organización" en el admin, ver Prospect::convertToOrganization()).
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
