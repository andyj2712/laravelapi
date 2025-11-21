<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 'this' es el modelo Producto
        return [
            'id' => $this->id_producto,
            'nombre' => $this->nombre_producto,
            'descripcion' => $this->descripcion_producto,
            'precioVenta' => (float) $this->precio_venta,
            'precioCompra' => (float) $this->precio_compra,
            'stock' => (int) $this->stock_disponible,
            'categoria' => $this->categoria_producto,
            'comision' => (float) $this->valor_comision,
            'fechaIngreso' => $this->fecha_ingreso,
            'creadoEn' => $this->created_at,
        ];
    }
}