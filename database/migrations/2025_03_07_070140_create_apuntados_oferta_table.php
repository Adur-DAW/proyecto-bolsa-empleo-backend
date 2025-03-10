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
        Schema::create('apuntados_oferta', function (Blueprint $table) {
            $table->foreignId('id_oferta')->constrained('oferta');
            $table->foreignId('id_demandante')->constrained('demandante');
            $table->string('adjudicada', 45)->nullable();
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
        Schema::dropIfExists('apuntados_oferta');
    }
};
