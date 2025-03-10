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
        Schema::create('titulos_demandante', function (Blueprint $table) {
            $table->foreignId('id_demandante')->constrained('demandante');
            $table->foreignId('id_titulo')->constrained('titulos');
            $table->string('centro', 45)->nullable();
            $table->string('año', 45)->nullable();
            $table->string('cursando', 45)->nullable();
            $table->primary(['id_demandante', 'id_titulo']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titulos_demandante');
    }
};
