<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('category')->nullable(); // mirador, laguna, glaciar, termas, etc.

            $table->string('source')->default('admin');
            $table->string('source_url')->nullable();
            $table->string('source_type')->nullable();
            $table->string('source_record_id')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // NOT NULL requerido por MariaDB para poder indexar (ver nota en businesses)
            $table->geography('location', subtype: 'point', srid: 4326);

            $table->spatialIndex('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attractions');
    }
};
