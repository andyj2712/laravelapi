<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')->constrained('ventas', 'id_venta')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos', 'id_producto')->onDelete('cascade');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalles_venta');
    }
};
