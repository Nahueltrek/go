<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 2 — Ownership. Si quien postula en /colaboradores está logueado,
 * se captura su user_id acá (ver CollaborationController::store()). Sigue
 * siendo nullable: el formulario público sigue funcionando 100% anónimo
 * como hasta ahora, solo que en ese caso la Organization resultante queda
 * sin dueño hasta que un admin lo asigne a mano (ver Admin\OrganizationController).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('collaboration_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
