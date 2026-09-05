<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 2 — Ownership. Agrega 'pending_review' y 'rejected' al enum de
 * status de experiences. Operación no destructiva: MariaDB permite ampliar
 * un ENUM sin tocar las filas existentes (siguen siendo draft/published/
 * unpublished exactamente como estaban). Se usa DB::statement en vez de
 * $table->enum(...)->change() para no depender de doctrine/dbal, que no
 * es una dependencia del proyecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE experiences MODIFY status ENUM('draft', 'pending_review', 'published', 'unpublished', 'rejected') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE experiences MODIFY status ENUM('draft', 'published', 'unpublished') NOT NULL DEFAULT 'draft'"
        );
    }
};
