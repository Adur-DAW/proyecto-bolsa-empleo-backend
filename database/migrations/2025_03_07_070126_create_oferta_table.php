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
        Schema::create('ofertas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 45)->nullable();
            $table->date('fecha_publicacion')->nullable();
            $table->tinyInteger('numero_puesto')->nullable();
            $table->string('tipo_cont', 45)->nullable();
            $table->string('horario', 45)->nullable();
            $table->string('obs', 45)->nullable();
            $table->tinyInteger('abierta')->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->foreignId('id_empresa')->constrained('empresa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta');
    }
};
