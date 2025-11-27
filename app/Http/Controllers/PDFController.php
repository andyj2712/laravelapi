<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venta; 
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function generarFactura($id)
    {
        
        $venta = Venta::with(['detalleVentas.producto', 'empleado'])->findOrFail($id);
        
        $data = [
            'venta' => $venta
        ];

        $pdf = Pdf::loadView('pdf.factura', $data);

        return $pdf->download('factura-venta-'.$venta->id_venta.'.pdf');
    }
}