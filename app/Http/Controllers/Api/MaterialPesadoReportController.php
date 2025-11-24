<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaterialPesadoReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // En producción, deberías validar aquí que todos los campos existan
    // y recalcular los totales para mayor seguridad.

    $report = MaterialPesadoReport::create([
        'cliente' => 'Ronald',
        'bronce_lb' => $request->input('materialInputs.bronce'),
        'rac_lb' => $request->input('materialInputs.rac'),
        'acero_lb' => $request->input('materialInputs.acero'),
        'aluminio_lb' => $request->input('materialInputs.aluminio'),
        'cobre_lb' => $request->input('materialInputs.cobre'),

        'bronce_precio' => $request->input('materialPrices.bronce'),
        'rac_precio' => $request->input('materialPrices.rac'),
        'acero_precio' => $request->input('materialPrices.acero'),
        'aluminio_precio' => $request->input('materialPrices.aluminio'),
        'cobre_precio' => $request->input('materialPrices.cobre'),

        'total_bronce' => $request->input('totals.bronce'),
        'total_rac' => $request->input('totals.rac'),
        'total_acero' => $request->input('totals.acero'),
        'total_aluminio' => $request->input('totals.aluminio'),
        'total_cobre' => $request->input('totals.cobre'),

        'total_general' => $request->input('grandTotal'),
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
        //
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
