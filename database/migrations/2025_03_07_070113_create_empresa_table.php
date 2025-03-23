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
        Schema::create('empresas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empresa')->primary();
            $table->foreign('id_empresa')->references('id')->on('usuarios')->onDelete('cascade');
            $table->tinyInteger('validado');
            $table->string('cif', 11)->unique();
            $table->string('nombre', 45);
            $table->string('localidad', 45);
            $table->string('telefono', 9);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
