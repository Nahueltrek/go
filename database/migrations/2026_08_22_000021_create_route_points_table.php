<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position'); // orden dentro de la ruta
            $table->string('pointable_type'); // Business | Attraction | Activity
            $table->unsignedBigInteger('pointable_id');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['pointable_type', 'pointable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_points');
    }
};
