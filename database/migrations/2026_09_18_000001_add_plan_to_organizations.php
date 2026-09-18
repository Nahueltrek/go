<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo comercial GO Chile (GO_CHILE_MODELO_COMERCIAL.md, secciones 3-5):
 * distingue perfil GO Free (default) de GO Pro/Pro+, para poder mostrar el
 * sello "GO Pro" en la landing pública y filtrar en el admin durante el
 * programa de 20 fundadores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->enum('plan', ['free', 'pro', 'pro_plus'])
                ->default('free')->after('claim_status');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
