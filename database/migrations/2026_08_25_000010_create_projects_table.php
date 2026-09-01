<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('category', ['conservacion', 'geologia', 'biodiversidad', 'comunidad']);
            $table->text('how_to_collaborate')->nullable();
            $table->enum('status', ['draft', 'published', 'unpublished'])->default('draft');
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });

        DB::statement('ALTER TABLE projects ADD location POINT NULL AFTER how_to_collaborate');
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
