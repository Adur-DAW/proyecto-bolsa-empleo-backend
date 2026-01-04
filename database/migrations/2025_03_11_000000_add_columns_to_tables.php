<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->string('dias_descanso', 100)->nullable()->after('horario');
        });

        Schema::table('demandantes', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable()->after('email');
            $table->string('cv_path', 255)->nullable()->after('familia_profesional');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable()->after('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn('dias_descanso');
        });

        Schema::table('demandantes', function (Blueprint $table) {
            $table->dropColumn(['familia_profesional', 'cv_path']);
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('familia_profesional');
        });
    }
};
