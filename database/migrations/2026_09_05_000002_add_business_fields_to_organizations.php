<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sprint 7 — fusión de Business dentro de Organization (Business queda
 * deprecado, ver App\Models\Business). Solo se traen los campos de
 * `businesses` que aportan algo que Organization no tiene ya:
 * verification_status, claim_status, opening_hours.
 *
 * Deliberadamente NO se traen sernatur_status/sernatur_record_id: son
 * específicos del sistema de importación SERNATUR de rm360 (fichas
 * catastradas automáticamente), que no aplica al modelo de colaboradores
 * curados manualmente de GO Chile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->enum('verification_status', ['unverified', 'pending', 'verified'])
                ->default('unverified')->after('status');
            $table->enum('claim_status', ['unclaimed', 'pending', 'claimed'])
                ->default('unclaimed')->after('verification_status');
            $table->json('opening_hours')->nullable()->after('claim_status');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'claim_status', 'opening_hours']);
        });
    }
};
