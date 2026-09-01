<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaboration_requests', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'guia', 'operador', 'agencia', 'emprendimiento',
                'alojamiento', 'marca', 'proyecto', 'organizacion',
            ]);
            $table->string('name');
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained('communes')->nullOnDelete();
            $table->string('instagram')->nullable();
            $table->string('website')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('description')->nullable();
            $table->json('services')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaboration_requests');
    }
};
