<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Empleado;

class VentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            // Vue espera 'id_venta', no 'id'
            'id_venta' => $this->id_venta, 
            
            // Vue espera 'monto_total', no 'montoTotal' (snake_case vs camelCase)
            'monto_total' => (float) $this->monto_total,
            
            'descuento' => (float) $this->descuento,
            
            // Vue espera 'nombre_cliente', no 'cliente'
            'nombre_cliente' => $this->nombre_cliente,

            // --- CORRECCIÓN CRÍTICA DE FECHA ---
            // Tu Vue está usando item.created_at para la fecha y la hora
            'created_at' => $this->created_at, // O usa $this->fecha_venta si prefieres

            // --- Tus otros campos (están bien) ---
            'comision_total' => (float) $this->comision_total,
            'fecha_venta' => $this->fecha_venta, // Lo dejamos por si lo usas en otro lado
            'empleado' => new EmpleadoResource($this->whenLoaded('empleado')),
            'productos' => $this->whenLoaded('productos', function () {
                return $this->productos->map(function ($producto) {
                    return [
                        'id_producto' => $producto->id_producto,
                        'nombre_producto' => $producto->nombre_producto,
                        'cantidad' => $producto->pivot->cantidad, // <-- El dato que faltaba
                        'precio_unitario' => $producto->pivot->precio_unitario, // <-- Dato extra
                    ];
                });

            }),
        ];
    }
}