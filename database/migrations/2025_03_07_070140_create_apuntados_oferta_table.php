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
        Schema::create('demandantes_oferta', function (Blueprint $table) {
            $table->foreignId('id_oferta')->constrained('ofertas');
            $table->foreignId('id_demandante')->constrained('demandantes', 'id_demandante');
            $table->boolean('adjudicada')->default(false);
            $table->date('fecha')->nullable();
            $table->primary(['id_oferta', 'id_demandante']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandantes_oferta');
    }
};
