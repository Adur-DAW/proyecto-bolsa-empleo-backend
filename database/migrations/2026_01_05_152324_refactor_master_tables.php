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
        // Use DB Facade to avoid model issues during migration
        $titulos = DB::table('titulos')->select('familia_profesional')->distinct()->whereNotNull('familia_profesional')->get();
        foreach ($titulos as $t) {
            if ($t->familia_profesional) {
                // Check if exists to avoid errors if run multiple times or potential race conditions
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

        // 2. Add ID columns
        Schema::table('titulos', function (Blueprint $table) {
            $table->foreignId('familia_profesional_id')->nullable()->after('familia_profesional')->constrained('familias_profesionales');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->foreignId('tipo_contrato_id')->nullable()->after('tipo_contrato')->constrained('tipos_contrato');
        });

        // 3. Update IDs
        DB::table('titulos')->whereNotNull('familia_profesional')->get()->each(function ($titulo) {
            $id = DB::table('familias_profesionales')->where('nombre', $titulo->familia_profesional)->value('id');
            if ($id) {
                DB::table('titulos')->where('id', $titulo->id)->update(['familia_profesional_id' => $id]);
            }
        });

        DB::table('ofertas')->whereNotNull('tipo_contrato')->get()->each(function ($oferta) {
            $id = DB::table('tipos_contrato')->where('nombre', $oferta->tipo_contrato)->value('id');
            if ($id) {
                DB::table('ofertas')->where('id', $oferta->id)->update(['tipo_contrato_id' => $id]);
            }
        });

        // 4. Drop old columns
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
        // Add back columns
        Schema::table('titulos', function (Blueprint $table) {
            $table->string('familia_profesional', 100)->nullable();
        });
        Schema::table('ofertas', function (Blueprint $table) {
            $table->string('tipo_contrato', 45)->nullable();
        });
        
        // Restore data
        DB::table('titulos')->whereNotNull('familia_profesional_id')->get()->each(function($t) {
            $nombre = DB::table('familias_profesionales')->where('id', $t->familia_profesional_id)->value('nombre');
            if ($nombre) {
                DB::table('titulos')->where('id', $t->id)->update(['familia_profesional' => $nombre]);
            }
        });
        
        DB::table('ofertas')->whereNotNull('tipo_contrato_id')->get()->each(function($o) {
            $nombre = DB::table('tipos_contrato')->where('id', $o->tipo_contrato_id)->value('nombre');
            if ($nombre) {
                DB::table('ofertas')->where('id', $o->id)->update(['tipo_contrato' => $nombre]);
            }
        });

        // Drop FKs and ID columns
        Schema::table('titulos', function (Blueprint $table) {
            $table->dropForeign(['familia_profesional_id']);
            $table->dropColumn('familia_profesional_id');
        });

        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropForeign(['tipo_contrato_id']);
            $table->dropColumn('tipo_contrato_id');
        });
    }
};
