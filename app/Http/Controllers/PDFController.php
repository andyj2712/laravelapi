<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta; 
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log; 

class PDFController extends Controller
{
    public function generarFactura($id)
    {
        try {

            $venta = Venta::with(['detalleVentas.producto', 'empleado'])->findOrFail($id);
            
            $data = [
                'venta' => $venta
            ];

            $pdf = Pdf::loadView('pdf.factura', $data);
            
            $pdf->setPaper('letter', 'portrait');

            $pdf->render();

            return $pdf->stream('factura-venta-'.$venta->id_venta.'.pdf');

        } catch (\Throwable $e) {

            return response()->json([
                'STATUS' => 'ERROR FATAL AL GENERAR PDF',
                'MENSAJE_CORTO' => $e->getMessage(),
                'ARCHIVO_CULPABLE' => $e->getFile(),
                'LINEA_DEL_ERROR' => $e->getLine(),
            ], 400); 
        }
    }
}
