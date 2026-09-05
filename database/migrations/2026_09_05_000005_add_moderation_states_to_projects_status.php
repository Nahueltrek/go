<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 2 — Ownership. Mismo cambio que en experiences (ver migración
 * hermana add_moderation_states_to_experiences_status): agrega
 * 'pending_review' y 'rejected' sin tocar filas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE projects MODIFY status ENUM('draft', 'pending_review', 'published', 'unpublished', 'rejected') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE projects MODIFY status ENUM('draft', 'published', 'unpublished') NOT NULL DEFAULT 'draft'"
        );
    }
};
