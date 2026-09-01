<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('relatable_type'); // Business | Attraction | Route | Activity | Destination
            $table->unsignedBigInteger('relatable_id');
            $table->timestamps();

            $table->index(['relatable_type', 'relatable_id']);
            $table->unique(['article_id', 'relatable_type', 'relatable_id'], 'article_relations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_relations');
    }
};
