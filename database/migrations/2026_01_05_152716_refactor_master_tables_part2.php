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
        // 1. Insert data into master tables
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

        // 2. Add ID columns
        Schema::table('demandantes', function (Blueprint $table) {
            $table->foreignId('familia_profesional_id')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->foreignId('familia_profesional_id')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });

        // 3. Update IDs using JOINS/Iteration
        DB::table('demandantes')->whereNotNull('familia_profesional')->get()->each(function ($d) {
            $id = DB::table('familias_profesionales')->where('nombre', $d->familia_profesional)->value('id');
            if ($id) {
                DB::table('demandantes')->where('id_demandante', $d->id_demandante)->update(['familia_profesional_id' => $id]);
            }
        });

        DB::table('empresas')->whereNotNull('familia_profesional')->get()->each(function ($e) {
            $id = DB::table('familias_profesionales')->where('nombre', $e->familia_profesional)->value('id');
            if ($id) {
                DB::table('empresas')->where('id_empresa', $e->id_empresa)->update(['familia_profesional_id' => $id]);
            }
        });

        // 4. Drop old columns
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
         // Add back columns
         Schema::table('demandantes', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });
        
        // Restore data
        DB::table('demandantes')->whereNotNull('familia_profesional_id')->get()->each(function($d) {
            $nombre = DB::table('familias_profesionales')->where('id', $d->familia_profesional_id)->value('nombre');
            if ($nombre) {
                DB::table('demandantes')->where('id_demandante', $d->id_demandante)->update(['familia_profesional' => $nombre]);
            }
        });
        
        DB::table('empresas')->whereNotNull('familia_profesional_id')->get()->each(function($e) {
            $nombre = DB::table('familias_profesionales')->where('id', $e->familia_profesional_id)->value('nombre');
            if ($nombre) {
                DB::table('empresas')->where('id_empresa', $e->id_empresa)->update(['familia_profesional' => $nombre]);
            }
        });

        // Drop FKs and ID columns
        Schema::table('demandantes', function (Blueprint $table) {
            $table->dropForeign(['familia_profesional_id']);
            $table->dropColumn('familia_profesional_id');
        });

        Schema::table('empresas', function (Blueprint $table) {
            $table->dropForeign(['familia_profesional_id']);
            $table->dropColumn('familia_profesional_id');
        });
    }
};
