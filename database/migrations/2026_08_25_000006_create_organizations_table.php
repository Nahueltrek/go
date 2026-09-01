<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', [
                'guia', 'operador', 'agencia', 'emprendimiento',
                'alojamiento', 'marca', 'proyecto', 'organizacion',
            ]);
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->nullOnDelete();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('logo_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Ubicación: nullable, sin índice espacial por ahora (no todo operador
        // tiene punto fijo). Si más adelante se necesita spatial index, la
        // columna deberá pasar a NOT NULL primero (mismo patrón que rm360).
        DB::statement('ALTER TABLE organizations ADD location POINT NULL AFTER whatsapp');
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
