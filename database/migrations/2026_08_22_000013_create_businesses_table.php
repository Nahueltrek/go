<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address')->nullable();

            // Estado SERNATUR / verificación / reclamación
            $table->enum('sernatur_status', ['sin_registro', 'registrado', 'desactualizado', 'dado_de_baja'])
                ->default('sin_registro');
            $table->foreignId('sernatur_record_id')->nullable()->constrained('sernatur_records')->nullOnDelete();
            $table->enum('verification_status', ['unverified', 'pending', 'verified'])->default('unverified');
            $table->enum('claim_status', ['unclaimed', 'pending', 'claimed'])->default('unclaimed');

            $table->boolean('is_active')->default(true);
            $table->json('opening_hours')->nullable();

            // Trazabilidad de origen — principio "no inventar datos"
            $table->string('source')->default('admin'); // admin | sernatur | business_owner
            $table->string('source_url')->nullable();
            $table->string('source_type')->nullable(); // api | csv | manual
            $table->string('source_record_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Ubicación puntual — clave para el mapa. NOT NULL porque MariaDB exige
            // que toda columna con índice SPATIAL no admita nulos (a diferencia de
            // PostGIS): el catastro manual siempre carga coordenadas al crear el negocio.
            $table->geography('location', subtype: 'point', srid: 4326);

            $table->index(['destination_id', 'business_category_id']);
            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
