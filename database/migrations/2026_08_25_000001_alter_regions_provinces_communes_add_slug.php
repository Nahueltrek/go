<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });
        Schema::table('provinces', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });
        Schema::table('communes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        foreach (DB::table('regions')->whereNull('slug')->get() as $row) {
            DB::table('regions')->where('id', $row->id)->update(['slug' => Str::slug($row->name)]);
        }
        foreach (DB::table('provinces')->whereNull('slug')->get() as $row) {
            DB::table('provinces')->where('id', $row->id)->update(['slug' => Str::slug($row->name)]);
        }
        foreach (DB::table('communes')->whereNull('slug')->get() as $row) {
            DB::table('communes')->where('id', $row->id)->update(['slug' => Str::slug($row->name)]);
        }
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
        Schema::table('communes', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};