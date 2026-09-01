<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type'); // Business | Attraction | Article | Route | Event
            $table->unsignedBigInteger('mediable_id');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('galleryable_type'); // Business | Attraction | Route
            $table->unsignedBigInteger('galleryable_id');
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index(['galleryable_type', 'galleryable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('media');
    }
};
