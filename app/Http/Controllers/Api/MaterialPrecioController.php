<?php
// Coloca este archivo en: app/Http/Controllers/Api/MaterialPrecioController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialPrecioActual;
use Illuminate\Http\Request;

class MaterialPrecioController extends Controller
{
    /**
     * Devuelve el precio vigente de cada material.
     * Como es una tabla "singleton", si por alguna razón la fila
     * no existe (ej. no se corrió el seed de la migración), la
     * creamos con los valores por defecto en vez de tronar.
     */
    public function show()
    {
        $precios = MaterialPrecioActual::firstOrCreate(
            ['id' => 1],
            [
                'bronce' => 1.65,
                'rac' => 1.30,
                'acero' => 0.40,
                'aluminio' => 0.60,
                'cobre' => 3.10,
            ]
        );

        return response()->json($precios);
    }

    /**
     * Actualiza el precio vigente de cada material.
     * Pensado para el diálogo del engranaje en el frontend.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bronce'   => ['required', 'numeric', 'min:0'],
            'rac'      => ['required', 'numeric', 'min:0'],
            'acero'    => ['required', 'numeric', 'min:0'],
            'aluminio' => ['required', 'numeric', 'min:0'],
            'cobre'    => ['required', 'numeric', 'min:0'],
        ]);

        $precios = MaterialPrecioActual::firstOrCreate(['id' => 1]);
        $precios->update($validated);

        return response()->json([
            'message' => 'Precios actualizados exitosamente',
            'data' => $precios,
        ]);
    }
}
