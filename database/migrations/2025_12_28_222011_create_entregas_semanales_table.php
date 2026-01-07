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
        Schema::create('entregas_semanales', function (Blueprint $table) {
            $table->id('id_entrega'); //
            $table->date('fecha'); //

            // Información del transporte y materiales
            $table->json('camion_info'); //
            $table->json('detalle_materiales'); //

            // Totales monetarios
            $table->decimal('subtotal', 10, 2); //
            $table->decimal('iva', 10, 2); //
            
            // CORRECCIÓN: Se define la columna sin el método ->after()
            $table->decimal('retencion', 10, 2)->default(0); //
            
            $table->decimal('total_final', 10, 2); //

            $table->timestamps(); //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_semanales'); //
    }
};