<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_venta,
            'montoTotal' => (float) $this->monto_total,
            'descuento' => (float) $this->descuento,
            'comisionTotal' => (float) $this->comision_total,
            'cliente' => $this->nombre_cliente,
            'fecha' => $this->fecha_venta,
            // Cargamos el empleado SOLO si ya está cargado (para optimizar)
            'empleado' => new EmpleadoResource($this->whenLoaded('empleado')),
            // Cargamos los productos (detalles) SOLO si ya están cargados
            'productos' => ProductoResource::collection($this->whenLoaded('productos')),
        ];
    }
}