<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre_producto',
        'descripcion_producto',
        'precio_compra',
        'precio_venta',
        'stock_disponible',
        'categoria_producto',
        'fecha_ingreso',
        'valor_comision',
    ];

    public function ventas()
    {
        return $this->belongsToMany(Venta::class, 'detalles_venta', 'producto_id', 'venta_id')
                    ->withPivot('cantidad', 'precio_unitario');
    }
}