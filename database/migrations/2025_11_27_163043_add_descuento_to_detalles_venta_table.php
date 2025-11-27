<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
        {
            Schema::table('detalles_venta', function (Blueprint $table) {
                // Agregamos la columna descuento después del precio unitario
                $table->decimal('descuento', 10, 2)->default(0)->after('precio_unitario');
            });
        }

    public function down()
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropColumn('descuento');
        });
    }
};
