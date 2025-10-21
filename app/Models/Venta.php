<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';

    protected $fillable = [
        'fecha_venta',
        'monto_total',
        'descuento',
        'nombre_cliente',
        'empleado_id',
        'comision_total',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id', 'id_empleado');
    }

    public function productos()
    {
        return $this->belongsToMany(Productos::class, 'detalles_venta', 'venta_id', 'producto_id')
                    ->withPivot('cantidad', 'precio_unitario', 'comision_item');
    }

    // Relación para los reportes
    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id', 'id_venta');
    }
}