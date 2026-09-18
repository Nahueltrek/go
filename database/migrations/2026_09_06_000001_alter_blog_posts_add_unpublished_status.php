<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sprint Bitácora GO 1.0 — agrega el estado "unpublished" al enum de
 * blog_posts.status (hoy solo draft/published). Alteración aditiva del
 * enum, no destructiva: no toca filas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE blog_posts MODIFY COLUMN status ENUM('draft', 'published', 'unpublished') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE blog_posts MODIFY COLUMN status ENUM('draft', 'published') NOT NULL DEFAULT 'draft'");
    }
};
