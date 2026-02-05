<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('titulos', function (Blueprint $table) {
            $table->foreignId('id_familia_profesional')
                  ->nullable()
                  ->after('nombre')
                  ->constrained('familias_profesionales');
        });
    }

    public function down(): void
    {
        Schema::table('titulos', function (Blueprint $table) {
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
        });
    }
};
