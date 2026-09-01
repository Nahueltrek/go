<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete(); // quien la ofrece, si aplica
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('difficulty')->nullable(); // facil, medio, dificil
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->geography('location', subtype: 'point', srid: 4326)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
