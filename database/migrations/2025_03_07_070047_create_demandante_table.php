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
        Schema::create('demandantes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_demandante')->primary();
            $table->foreign('id_demandante')->references('id')->on('usuarios')->onDelete('cascade');
            $table->string('dni', 9)->unique();
            $table->string('nombre', 45);
            $table->string('apellido1', 45);
            $table->string('apellido2', 45);
            $table->string('telefono_movil', 9);
            $table->string('email', 45);
            $table->tinyInteger('situacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandante');
    }
};
