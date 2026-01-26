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


        $titulos = DB::table('titulos')->select('familia_profesional')->distinct()->whereNotNull('familia_profesional')->get();
        foreach ($titulos as $t) {
            if ($t->familia_profesional) {

                if (!DB::table('familias_profesionales')->where('nombre', $t->familia_profesional)->exists()) {
                    DB::table('familias_profesionales')->insert([
                        'nombre' => $t->familia_profesional,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $ofertas = DB::table('ofertas')->select('tipo_contrato')->distinct()->whereNotNull('tipo_contrato')->get();
        foreach ($ofertas as $o) {
            if ($o->tipo_contrato) {
                if (!DB::table('tipos_contrato')->where('nombre', $o->tipo_contrato)->exists()) {
                    DB::table('tipos_contrato')->insert([
                        'nombre' => $o->tipo_contrato,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }


        Schema::table('titulos', function (Blueprint $table) {
            $table->foreignId('id_familia_profesional')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->foreignId('id_tipo_contrato')->nullable()->after('tipo_contrato')->constrained('tipos_contrato');
        });


        DB::table('titulos')->whereNotNull('familia_profesional')->get()->each(function ($titulo) {
            $id = DB::table('familias_profesionales')->where('nombre', $titulo->familia_profesional)->value('id');
            if ($id) {
                DB::table('titulos')->where('id', $titulo->id)->update(['id_familia_profesional' => $id]);
            }
        });

        DB::table('ofertas')->whereNotNull('tipo_contrato')->get()->each(function ($oferta) {
            $id = DB::table('tipos_contrato')->where('nombre', $oferta->tipo_contrato)->value('id');
            if ($id) {
                DB::table('ofertas')->where('id', $oferta->id)->update(['id_tipo_contrato' => $id]);
            }
        });


        Schema::table('titulos', function (Blueprint $table) {
            $table->dropColumn('familia_profesional');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn('tipo_contrato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('titulos', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });
        Schema::table('ofertas', function (Blueprint $table) {
            $table->string('tipo_contrato', 45)->nullable();
        });


        DB::table('titulos')->whereNotNull('id_familia_profesional')->get()->each(function ($t) {
            $nombre = DB::table('familias_profesionales')->where('id', $t->id_familia_profesional)->value('nombre');
            if ($nombre) {
                DB::table('titulos')->where('id', $t->id)->update(['familia_profesional' => $nombre]);
            }
        });

        DB::table('ofertas')->whereNotNull('id_tipo_contrato')->get()->each(function ($o) {
            $nombre = DB::table('tipos_contrato')->where('id', $o->id_tipo_contrato)->value('nombre');
            if ($nombre) {
                DB::table('ofertas')->where('id', $o->id)->update(['tipo_contrato' => $nombre]);
            }
        });


        Schema::table('titulos', function (Blueprint $table) {
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropForeign(['id_tipo_contrato']);
            $table->dropColumn('id_tipo_contrato');
        });
    }
};
