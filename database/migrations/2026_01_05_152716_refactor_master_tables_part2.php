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

        $demandantes = DB::table('demandantes')->select('familia_profesional')->distinct()->whereNotNull('familia_profesional')->get();
        foreach ($demandantes as $d) {
            if ($d->familia_profesional) {
                if (!DB::table('familias_profesionales')->where('nombre', $d->familia_profesional)->exists()) {
                    DB::table('familias_profesionales')->insert([
                        'nombre' => $d->familia_profesional,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        $empresas = DB::table('empresas')->select('familia_profesional')->distinct()->whereNotNull('familia_profesional')->get();
        foreach ($empresas as $e) {
            if ($e->familia_profesional) {
                if (!DB::table('familias_profesionales')->where('nombre', $e->familia_profesional)->exists()) {
                    DB::table('familias_profesionales')->insert([
                        'nombre' => $e->familia_profesional,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }


        Schema::table('demandantes', function (Blueprint $table) {
            $table->foreignId('id_familia_profesional')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('id_familia_profesional')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });


        DB::table('demandantes')->whereNotNull('familia_profesional')->get()->each(function ($d) {
            $id = DB::table('familias_profesionales')->where('nombre', $d->familia_profesional)->value('id');
            if ($id) {
                DB::table('demandantes')->where('id_demandante', $d->id_demandante)->update(['id_familia_profesional' => $id]);
            }
        });

        DB::table('empresas')->whereNotNull('familia_profesional')->get()->each(function ($e) {
            $id = DB::table('familias_profesionales')->where('nombre', $e->familia_profesional)->value('id');
            if ($id) {
                DB::table('empresas')->where('id_empresa', $e->id_empresa)->update(['id_familia_profesional' => $id]);
            }
        });


        Schema::table('demandantes', function (Blueprint $table) {
            $table->dropColumn('familia_profesional');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('familia_profesional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('demandantes', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });


        DB::table('demandantes')->whereNotNull('id_familia_profesional')->get()->each(function ($d) {
            $nombre = DB::table('familias_profesionales')->where('id', $d->id_familia_profesional)->value('nombre');
            if ($nombre) {
                DB::table('demandantes')->where('id_demandante', $d->id_demandante)->update(['familia_profesional' => $nombre]);
            }
        });

        DB::table('empresas')->whereNotNull('id_familia_profesional')->get()->each(function ($e) {
            $nombre = DB::table('familias_profesionales')->where('id', $e->id_familia_profesional)->value('nombre');
            if ($nombre) {
                DB::table('empresas')->where('id_empresa', $e->id_empresa)->update(['familia_profesional' => $nombre]);
            }
        });


        Schema::table('demandantes', function (Blueprint $table) {
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['id_familia_profesional']);
            $table->dropColumn('id_familia_profesional');
        });
    }
};
