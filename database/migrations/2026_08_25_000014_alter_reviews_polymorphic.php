<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar las columnas polimórficas SIN tocar la FK actual todavía.
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('reviewable_type')->nullable()->after('id');
            $table->unsignedBigInteger('reviewable_id')->nullable()->after('reviewable_type');
        });

        // 2. Migrar los datos existentes: toda review actual apunta a un
        // business_id -> se rellena reviewable_type/reviewable_id equivalentes.
        // (Se usa el FQCN del modelo Business tal como está en rm360.)
        DB::table('reviews')->whereNotNull('business_id')->update([
            'reviewable_type' => 'App\\Models\\Business',
        ]);
        DB::statement('UPDATE reviews SET reviewable_id = business_id WHERE business_id IS NOT NULL');

        // 3. Índice para el nuevo par polimórfico.
        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['reviewable_type', 'reviewable_id']);
        });

        // Nota: business_id y su FK NO se eliminan en esta migración.
        // Se deja como columna legacy hasta confirmar en producción que
        // el backfill fue correcto (0 reviews con reviewable_id NULL).
        // El drop se hace en una migración separada más adelante.
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['reviewable_type', 'reviewable_id']);
            $table->dropColumn(['reviewable_type', 'reviewable_id']);
        });
    }
};
