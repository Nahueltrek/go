<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->foreignId('activity_type_id')->constrained('business_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['facil', 'medio', 'dificil', 'experto'])->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('price', 10, 0)->nullable(); // CLP, sin decimales
            $table->enum('status', ['draft', 'published', 'unpublished'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });

        // Ubicación nullable, sin spatial index por ahora — mismo criterio
        // conservador que en organizations. Si se necesita spatial index más
        // adelante, la columna deberá pasar a NOT NULL y asignarse *antes*
        // del primer save() (el bug documentado en rm360 fue justamente
        // guardar sin location asignado con la columna en NOT NULL).
        DB::statement('ALTER TABLE experiences ADD location POINT NULL AFTER capacity');
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
