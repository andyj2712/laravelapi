<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto'); // Clave primaria autoincrementable.
            $table->string('nombre_producto', 100);
            $table->text('descripcion_producto')->nullable(); // Campo de texto opcional.
            $table->decimal('precio_venta', 8, 2); // Decimal para precios, 8 dígitos en total, 2 decimales.
            $table->decimal('precio_compra', 8, 2);
            $table->date('fecha_ingreso');
            $table->integer('stock_disponible')->unsigned()->default(0); // Campo para la cantidad
            $table->string('categoria_producto', 50)->nullable(); // **¡Este campo es el que faltaba!**
            
            $table->timestamps(); // Agrega `created_at` y `updated_at`.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
}