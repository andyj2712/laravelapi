<?php

namespace App\Http\Controllers\Api;

use App\Models\EntregaSemanal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EntregaSemanalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // CORRECCIÓN AQUÍ: Devolver los datos ordenados por fecha
        return response()->json(EntregaSemanal::orderBy('fecha', 'desc')->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'camion_info' => 'required|array',
            'detalle_materiales' => 'required|array',
            'subtotal' => 'required|numeric',
            'iva' => 'required|numeric',
            'total_final' => 'required|numeric',
        ]);

        $entrega = EntregaSemanal::create($request->all());

        return response()->json([
            'message' => 'Entrega registrada exitosamente',
            'data' => $entrega
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(EntregaSemanal $entregaSemanal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EntregaSemanal $entregaSemanal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EntregaSemanal $entregaSemanal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $entrega = EntregaSemanal::find($id);
        if($entrega){
            $entrega->delete();
            return response()->json(['message' => 'Eliminado']);
        }
        return response()->json(['message' => 'No encontrado'], 404);
    }
}
