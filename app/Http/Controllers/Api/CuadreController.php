<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venta;
use Carbon\Carbon;

class CuadreController extends Controller
{
    // Función para mostrar los datos en la pantalla
    public function consultar(Request $request)
    {
        $fecha = $request->query('fecha');
        
        if (!$fecha) {
            $fecha = Carbon::now()->toDateString();
        }

        $ventas = Venta::with('productos')
            ->whereDate('fecha_venta', $fecha)
            ->get();

        $bolsaISSS = 0;
        $bolsaMSelecto = 0;
        $totalGeneral = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->productos as $producto) {
                $cantidad = $producto->pivot->cantidad;
                $precio = $producto->pivot->precio_unitario;
                $subtotal = $cantidad * $precio;

                // Usamos el nombre real de la columna en tu BD
                $categoria = strtoupper(trim($producto->categoria_producto));

                if ($categoria === 'ISSS') {
                    $bolsaISSS += $subtotal;
                } 
                elseif ($categoria === 'M-SELECTO') {
                    $bolsaMSelecto += $subtotal;
                }
                
                $totalGeneral += $subtotal;
            }
        }

        return response()->json([
            'fecha_consultada' => $fecha,
            'sistema' => [
                'total_isss' => round($bolsaISSS, 2),
                'total_m_selecto' => round($bolsaMSelecto, 2),
                'total_ventas_dia' => round($totalGeneral, 2)
            ]
        ]);
    }

    // Función para descargar el Excel
    public function exportar(Request $request)
    {
        $fecha = $request->input('fecha');
        $cajaChica = $request->input('caja_chica', 0);
        $gastos = $request->input('gastos', 0);
        $realIsss = $request->input('real_isss', 0);
        $realMSelecto = $request->input('real_m_selecto', 0);

        $ventas = Venta::with('productos')
            ->whereDate('fecha_venta', $fecha)
            ->get();

        $bolsaISSS = 0;
        $bolsaMSelecto = 0;
        $totalVentas = 0;

        foreach ($ventas as $venta) {
            foreach ($venta->productos as $producto) {
                $subtotal = $producto->pivot->cantidad * $producto->pivot->precio_unitario;
                $categoria = strtoupper(trim($producto->categoria_producto));

                if ($categoria === 'ISSS') $bolsaISSS += $subtotal;
                elseif ($categoria === 'M-SELECTO') $bolsaMSelecto += $subtotal;
                
                $totalVentas += $subtotal;
            }
        }

        $totalFisico = $realIsss + $realMSelecto;
        $deberiaHaber = $totalVentas + $cajaChica - $gastos;
        $diferencia = $totalFisico - $deberiaHaber;
        
        $colorDiferencia = $diferencia < 0 ? '#FFCCCC' : '#CCFFCC'; 
        $textoDiferencia = $diferencia < 0 ? 'FALTANTE' : ($diferencia > 0 ? 'SOBRANTE' : 'CUADRE PERFECTO');

        $html = "
        <table border='1'>
            <tr>
                <td colspan='2' style='background-color: #003366; color: white; font-weight: bold; font-size: 16px; text-align: center;'>
                    CIERRE DIARIO - RECICLADORA FERNANDEZ
                </td>
            </tr>
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>$fecha</td>
            </tr>
            <tr><td colspan='2'></td></tr>

            <tr>
                <td style='background-color: #E6F3FF;'><strong>Ventas ISSS (Sistema)</strong></td>
                <td>$" . number_format($bolsaISSS, 2) . "</td>
            </tr>
            <tr>
                <td style='background-color: #E6F3FF;'><strong>Ventas M-SELECTO (Sistema)</strong></td>
                <td>$" . number_format($bolsaMSelecto, 2) . "</td>
            </tr>
            <tr>
                <td style='background-color: #CCE5FF;'><strong>TOTAL VENTAS</strong></td>
                <td style='background-color: #CCE5FF;'><strong>$" . number_format($totalVentas, 2) . "</strong></td>
            </tr>
            
            <tr><td colspan='2'></td></tr>

            <tr>
                <td>Efectivo ISSS (Mano)</td>
                <td>$" . number_format($realIsss, 2) . "</td>
            </tr>
            <tr>
                <td>Efectivo M-SELECTO (Mano)</td>
                <td>$" . number_format($realMSelecto, 2) . "</td>
            </tr>
            <tr>
                <td style='background-color: #FFFFCC;'><strong>TOTAL DINERO EN BOLSA</strong></td>
                <td style='background-color: #FFFFCC;'><strong>$" . number_format($totalFisico, 2) . "</strong></td>
            </tr>

            <tr><td colspan='2'></td></tr>

            <tr>
                <td>(+) Caja Chica / Base</td>
                <td>$" . number_format($cajaChica, 2) . "</td>
            </tr>
            <tr>
                <td>(-) Gastos / Salidas</td>
                <td>$" . number_format($gastos, 2) . "</td>
            </tr>
            <tr>
                <td><strong>DEBERÍA HABER</strong></td>
                <td><strong>$" . number_format($deberiaHaber, 2) . "</strong></td>
            </tr>
            
            <tr><td colspan='2'></td></tr>

            <tr>
                <td style='background-color: $colorDiferencia; font-size: 14px;'><strong>DIFERENCIA ($textoDiferencia)</strong></td>
                <td style='background-color: $colorDiferencia; font-size: 14px;'><strong>$" . number_format($diferencia, 2) . "</strong></td>
            </tr>
        </table>
        ";

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=Cierre_Diario_$fecha.xls",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}