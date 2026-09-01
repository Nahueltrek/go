<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable(); // "Sucursal centro", "Punto de encuentro", etc.
            $table->string('address')->nullable();
            $table->geography('point', subtype: 'point', srid: 4326);
            $table->timestamps();

            $table->spatialIndex('point');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
