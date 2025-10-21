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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->decimal('monto_total', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0.00);
            $table->string('nombre_cliente', 150);
            $table->dateTime('fecha_venta');
            
            $table->foreignId('empleado_id')->nullable()->constrained('empleados', 'id_empleado')->onDelete('set null');
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
        Schema::dropIfExists('ventas');
    }
};
