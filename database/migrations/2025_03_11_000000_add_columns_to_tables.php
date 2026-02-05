<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->string('dias_descanso', 100)->nullable()->after('horario');
            $table->foreignId('id_tipo_contrato')
                  ->nullable()
                  ->after('tipo_contrato')
                  ->constrained('tipos_contrato');
        });

        Schema::table('demandantes', function (Blueprint $table) {
            $table->string('cv_path', 255)->nullable()->after('email');
            $table->foreignId('id_familia_profesional')
                  ->nullable()
                  ->after('email')
                  ->constrained('familias_profesionales');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('id_familia_profesional')
                  ->nullable()
                  ->after('nombre')
                  ->constrained('familias_profesionales');

            $table->string('imagen_url')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn('dias_descanso');
            $table->dropForeign(['id_tipo_contrato']);
            $table->dropColumn('id_tipo_contrato');
        });

        Schema::table('demandantes', function (Blueprint $table) {
            $table->dropColumn('cv_path');
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
            $table->dropColumn('imagen_url');
        });
    }
};
