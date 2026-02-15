<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulos', function (Blueprint $table) {
            if (!Schema::hasColumn('titulos', 'id_familia_profesional')) {
                $table->foreignId('id_familia_profesional')
                      ->nullable()
                      ->after('nombre')
                      ->constrained('familias_profesionales', 'id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('titulos', function (Blueprint $table) {
            if (Schema::hasColumn('titulos', 'id_familia_profesional')) {
                $table->dropForeign(['id_familia_profesional']);
                $table->dropColumn('id_familia_profesional');
            }
        });
    }
};
