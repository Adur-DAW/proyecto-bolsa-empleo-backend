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
            $table->unsignedBigInteger('id_demandante');
            $table->unsignedBigInteger('id_titulo');
            $table->foreign('id_demandante')->references('id_demandante')->on('demandantes')->onDelete('cascade');
            $table->foreign('id_titulo')->references('id_titulo')->on('titulos')->onDelete('cascade');
            $table->string('centro', 45)->nullable();
            $table->string('año', 45)->nullable();
            $table->boolean('cursando')->default(false);
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
