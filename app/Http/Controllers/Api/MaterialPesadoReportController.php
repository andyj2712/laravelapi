<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaterialPesadoReport; // <-- FIX: import que faltaba (causaba el error fatal al guardar)
use Illuminate\Http\Request;

class MaterialPesadoReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Últimos reportes primero, útil para un futuro historial
        return response()->json(
            MaterialPesadoReport::orderByDesc('created_at')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // FIX: validación explícita. Antes, si el frontend mandaba
        // un campo vacío o mal nombrado, Laravel intentaba insertar
        // NULL en columnas NOT NULL y tronaba con un error de SQL
        // poco claro en vez de un 422 legible.
        $validated = $request->validate([
            'materialInputs.bronce'   => ['nullable', 'numeric', 'min:0'],
            'materialInputs.rac'      => ['nullable', 'numeric', 'min:0'],
            'materialInputs.acero'    => ['nullable', 'numeric', 'min:0'],
            'materialInputs.aluminio' => ['nullable', 'numeric', 'min:0'],
            'materialInputs.cobre'    => ['nullable', 'numeric', 'min:0'],

            'materialPrices.bronce'   => ['required', 'numeric', 'min:0'],
            'materialPrices.rac'      => ['required', 'numeric', 'min:0'],
            'materialPrices.acero'    => ['required', 'numeric', 'min:0'],
            'materialPrices.aluminio' => ['required', 'numeric', 'min:0'],
            'materialPrices.cobre'    => ['required', 'numeric', 'min:0'],

            'totals.bronce'   => ['required', 'numeric', 'min:0'],
            'totals.rac'      => ['required', 'numeric', 'min:0'],
            'totals.acero'    => ['required', 'numeric', 'min:0'],
            'totals.aluminio' => ['required', 'numeric', 'min:0'],
            'totals.cobre'    => ['required', 'numeric', 'min:0'],

            'grandTotal' => ['required', 'numeric', 'min:0'],
        ]);

        $report = MaterialPesadoReport::create([
            'cliente' => 'Ronald',
            'bronce_lb'   => $validated['materialInputs']['bronce'] ?? 0,
            'rac_lb'      => $validated['materialInputs']['rac'] ?? 0,
            'acero_lb'    => $validated['materialInputs']['acero'] ?? 0,
            'aluminio_lb' => $validated['materialInputs']['aluminio'] ?? 0,
            'cobre_lb'    => $validated['materialInputs']['cobre'] ?? 0,

            'bronce_precio'   => $validated['materialPrices']['bronce'],
            'rac_precio'      => $validated['materialPrices']['rac'],
            'acero_precio'    => $validated['materialPrices']['acero'],
            'aluminio_precio' => $validated['materialPrices']['aluminio'],
            'cobre_precio'    => $validated['materialPrices']['cobre'],

            'total_bronce'   => $validated['totals']['bronce'],
            'total_rac'      => $validated['totals']['rac'],
            'total_acero'    => $validated['totals']['acero'],
            'total_aluminio' => $validated['totals']['aluminio'],
            'total_cobre'    => $validated['totals']['cobre'],

            'total_general' => $validated['grandTotal'],
        ]);

        return response()->json([
            'message' => 'Reporte guardado exitosamente',
            'data' => $report
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $report = MaterialPesadoReport::find($id);

        if (! $report) {
            return response()->json(['message' => 'Reporte no encontrado'], 404);
        }

        return response()->json($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
