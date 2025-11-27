<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
        }
        .header {
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #004d40; /* Un verde oscuro elegante */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #004d40;
            font-size: 24px;
        }
        .info-seccion {
            width: 100%;
            margin-bottom: 20px;
        }
        .cliente-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #004d40;
            color: #ffffff;
            padding: 10px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
        }
        td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totales {
            margin-top: 20px;
            float: right;
            width: 40%;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        .gran-total {
            font-size: 18px;
            font-weight: bold;
            color: #004d40;
            border-top: 2px solid #004d40;
            padding-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>RECICLADORA FERNÁNDEZ</h1>
        <p>Dirección de la Recicladora, El Salvador</p>
        <p>Teléfono: 2222-0000 | Email: info@recicladora.com</p>
    </div>

    <div class="info-seccion">
        <div class="cliente-box">
            <table style="margin: 0; width: 100%;">
                <tr>
                    <td style="border: none; padding: 2px;"><strong>Cliente:</strong> {{ $venta->nombre_cliente }}</td>
                    <td style="border: none; padding: 2px; text-align: right;"><strong>N° Factura:</strong> #{{ str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px;">
                        <strong>Vendedor:</strong> 
                        {{ $venta->empleado ? $venta->empleado->nombre_empleado : 'N/A' }}
                    </td>
                    <td style="border: none; padding: 2px; text-align: right;">
                        <strong>Fecha:</strong> 
                        {{ \Carbon\Carbon::parse($venta->fecha_venta)->format('d/m/Y H:i A') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Producto</th>
                <th class="text-center" style="width: 15%;">Cant.</th>
                <th class="text-right" style="width: 15%;">Precio Unit.</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- 
               OJO AQUI: Usamos 'detalleVentas' que es el nombre correcto de tu relación 
            --}}
            @foreach($venta->detalleVentas as $detalle)
            <tr>
                <td>
                    {{-- Usamos '??' por si el producto fue eliminado de la BD --}}
                    {{ $detalle->producto->nombre_producto ?? 'Producto (Eliminado)' }}
                </td>
                <td class="text-center">{{ $detalle->cantidad }}</td>
                <td class="text-right">${{ number_format($detalle->precio_unitario, 2) }}</td>
                <td class="text-right">
                    ${{ number_format($detalle->cantidad * $detalle->precio_unitario, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <table style="border: none;">
            <tr>
                <td style="border: none; text-align: right;"><strong>Subtotal:</strong></td>
                <td style="border: none; text-align: right;">
                    ${{ number_format($venta->monto_total + $venta->descuento, 2) }}
                </td>
            </tr>
            @if($venta->descuento > 0)
            <tr>
                <td style="border: none; text-align: right; color: #d32f2f;">Descuento:</td>
                <td style="border: none; text-align: right; color: #d32f2f;">
                    -${{ number_format($venta->descuento, 2) }}
                </td>
            </tr>
            @endif
            <tr>
                <td style="border: none; text-align: right;" class="gran-total">TOTAL:</td>
                <td style="border: none; text-align: right;" class="gran-total">
                    ${{ number_format($venta->monto_total, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Gracias por su preferencia. Este documento es un comprobante válido de compra.</p>
        <p>Generado por Sistema de Gestión de Reciclaje</p>
    </div>

</body>
</html>