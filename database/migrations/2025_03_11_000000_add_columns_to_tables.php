<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            if (!Schema::hasColumn('ofertas', 'dias_descanso')) {
                $table->string('dias_descanso', 100)->nullable()->after('horario');
            }
            if (!Schema::hasColumn('ofertas', 'id_tipo_contrato')) {
                $table->foreignId('id_tipo_contrato')
                      ->nullable()
                      ->after('id_empresa')
                      ->constrained('tipos_contrato');
            }
        });

        Schema::table('demandantes', function (Blueprint $table) {
            if (!Schema::hasColumn('demandantes', 'cv_path')) {
                $table->string('cv_path', 255)->nullable()->after('email');
            }
            if (!Schema::hasColumn('demandantes', 'id_familia_profesional')) {
                $table->foreignId('id_familia_profesional')
                      ->nullable()
                      ->after('cv_path')
                      ->constrained('familias_profesionales', 'id');
            }
        });

        Schema::table('empresas', function (Blueprint $table) {
            if (!Schema::hasColumn('empresas', 'id_familia_profesional')) {
                $table->foreignId('id_familia_profesional')
                      ->nullable()
                      ->after('nombre')
                      ->constrained('familias_profesionales', 'id');
            }
            if (!Schema::hasColumn('empresas', 'imagen_url')) {
                $table->string('imagen_url', 191)->nullable()->after('localidad');
            }
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
