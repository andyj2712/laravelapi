<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EmpleadoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_empleado,
            'nombre' => $this->nombre_empleado,
            'edad' => (int) $this->edad_empleado,
            'telefono' => $this->telefono_empleado,
            'salarioBase' => (float) $this->salario_base,
            'cargo' => $this->cargo_empleado,
            // Si 'foto_empleado' no es una URL completa, la construimos
            'fotoUrl' => $this->foto_empleado ? Storage::url($this->foto_empleado) : null,
        ];
    }
}