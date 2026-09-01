<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->longText('content');
            $table->enum('category', [
                'rutas', 'personas', 'territorio', 'educacion', 'conservacion', 'experiencias',
            ]);
            $table->string('cover_image_url')->nullable();
            $table->foreignId('related_organization_id')->nullable()
                ->constrained('organizations')->nullOnDelete();
            $table->foreignId('related_destination_id')->nullable()
                ->constrained('destinations')->nullOnDelete();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
