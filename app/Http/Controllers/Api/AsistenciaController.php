<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\Asistencia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    /**
     * Recupera la asistencia. Puede consultar un día específico o un rango de fechas.
     */
    public function index(Request $request)
    {
        $fechaUnica = $request->query('fecha');
        $fechaInicio = $request->query('fecha_inicio');
        $fechaFin = $request->query('fecha_fin');
        
        // 1. Lógica de RANGO DE FECHAS (Historial)
        if ($fechaInicio && $fechaFin) {
            
            // 🚨 CAMBIO CLAVE: Consultamos la tabla EMPLEADOS y anidamos sus registros de asistencia en el rango.
            // Esto asegura que todos los 5 empleados sean devueltos, incluso si no tienen registros.
            $empleadosConAsistencias = Empleado::with(['asistencias' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereDate('fecha', '>=', $fechaInicio)
                      ->whereDate('fecha', '<=', $fechaFin)
                      ->orderBy('fecha', 'asc'); // Ordenamos para mejor visualización del historial
            }])
            ->orderBy('nombre_empleado', 'asc')
            ->get();

            // Devolver los empleados con sus asistencias anidadas
            return response()->json([
                'tipo_consulta' => 'RANGO_EMPLEADOS', // Indicamos a Vue que es el modo RANGO
                'data' => $empleadosConAsistencias
            ]);
        } 
        
        // 2. Lógica de DÍA ÚNICO (Tu lógica original de registro diario)
        
        $fechaConsulta = $fechaUnica ?: Carbon::now()->toDateString();
        
        $empleados = Empleado::whereDate('created_at', '<=', $fechaConsulta)
            ->with(['asistencias' => function($query) use ($fechaConsulta) {
                $query->where('fecha', $fechaConsulta);
            }])
            ->orderBy('nombre_empleado', 'asc')
            ->get();

        return response()->json([
            'tipo_consulta' => 'DIA_UNICO',
            'fecha_consultada' => $fechaConsulta,
            'es_hoy' => $fechaConsulta === Carbon::now()->toDateString(),
            'data' => $empleados
        ]);
    }

    // El método store permanece sin cambios
    public function store(Request $request)
    {
        $datos = $request->validate([
            'asistencias' => 'required|array',
            'asistencias.*.empleado_id' => 'required|exists:empleados,id_empleado',
            'asistencias.*.estado' => 'required|in:asistio,medio_dia,falta',
            'asistencias.*.observacion' => 'nullable|string'
        ]);

        $fechaHoy = Carbon::now()->toDateString();

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