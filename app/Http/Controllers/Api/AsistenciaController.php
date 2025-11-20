<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Asistencia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    // Obtener lista de empleados y su asistencia en una fecha específica
    public function index(Request $request)
    {
        $fecha = $request->query('fecha', Carbon::now()->toDateString());

        // MODIFICACIÓN: Agregamos whereDate para filtrar por fecha de creación
        $empleados = Empleado::whereDate('created_at', '<=', $fecha) // <--- ESTA ES LA MAGIA
            ->with(['asistencias' => function($query) use ($fecha) {
                $query->where('fecha', $fecha);
            }])
            ->orderBy('nombre_empleado', 'asc')
            ->get();

        return response()->json([
            'fecha_consultada' => $fecha,
            'es_hoy' => $fecha === Carbon::now()->toDateString(),
            'data' => $empleados
        ]);
    }

    // Guardar asistencia (Solo permite guardar con fecha de HOY por seguridad)
    public function store(Request $request)
    {
        $datos = $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.empleado_id' => 'required|exists:empleados,id_empleado',
            'asistencias.*.estado' => 'required|in:asistio,medio_dia,falta',
            'asistencias.*.observacion' => 'nullable|string'
        ]);

        $fechaHoy = Carbon::now()->toDateString(); // SEGURIDAD: Fecha servidor inmutable

        foreach ($datos['asistencias'] as $registro) {
            Asistencia::updateOrCreate(
                [
                    'empleado_id' => $registro['empleado_id'],
                    'fecha' => $fechaHoy
                ],
                [
                    'estado' => $registro['estado'],
                    'observacion' => $registro['observacion'] ?? null
                ]
            );
        }

        return response()->json(['message' => 'Asistencia registrada correctamente para el día ' . $fechaHoy]);
    }
}