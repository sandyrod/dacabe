<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo Pedido {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 4mm;
            size: 80mm auto;
        }

        body {
            margin: 0;
            font-family: "Courier New", Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
        }

        @media print {

            html,
            body {
                height: auto;
                margin: 0;
                padding: 0;
            }

            .ticket {
                height: auto;
                page-break-inside: avoid;
            }
        }

        .ticket {
            width: 72mm;
            margin: 0 auto;
            padding: 4mm 0;
        }

        .center {
            text-align: center;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .row {
            margin-bottom: 3px;
            word-break: break-word;
        }

        .products {
            width: 100%;
            border-collapse: collapse;
        }

        .products th,
        .products td {
            padding: 2px 0;
            vertical-align: top;
        }

        .products th:last-child,
        .products td:last-child {
            text-align: right;
            width: 18%;
        }

        .muted {
            font-size: 11px;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="center">
            <strong>{{ strtoupper(optional($company)->name ?? 'DACABE') }}</strong><br>
            @if(optional($company)->code)
                <span>RIF: {{ $company->code }}</span><br>
            @endif
            @if(optional($company)->phone)
                <span>Tel: {{ $company->phone }}</span><br>
            @endif
            @if(optional($company)->email)
                <span>{{ $company->email }}</span><br>
            @endif
            @if(optional($company)->location)
                <div class="muted">{!! nl2br(e(trim(strip_tags($company->location)))) !!}</div>
            @endif
        </div>

        <div class="divider"></div>

        <!--<div class="center">
            <strong>RECIBO DE PEDIDO</strong>
        </div>-->

        <!--<div class="divider"></div>-->

        <div class="row"><strong>Nro:</strong> {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
        <div class="row"><strong>Fecha:</strong> {{ formatoFechaDMASimple($order->fecha ?? $order->created_at) }}</div>

        <div class="divider"></div>
        <div class="row"><strong>DATOS DEL CLIENTE</strong></div>
        <div class="row"><strong>Nombre:</strong> {{ optional($client)->NOMBRE ?: ($order->descripcion ?: 'N/D') }}
        </div>
        <div class="row"><strong>Rif:</strong> {{ optional($client)->RIF ?: ($order->rif ?: 'N/D') }}</div>
        <div class="row"><strong>Dirección:</strong> {{ optional($client)->DIRECCION ?: 'N/D' }}</div>
        <div class="row"><strong>Zona:</strong> {{ optional($client)->DESZONA ?: 'N/D' }}</div>

        <div class="divider"></div>
        <div class="row"><strong>DATOS DEL VENDEDOR</strong></div>
        <div class="row"><strong>Nombre y zona:</strong>
            {{ $seller ?: ($order->seller_code ?: 'N/D') }}{{ $sellerZone ? ' - ' . $sellerZone : '' }}</div>

        <div class="divider"></div>
        <div class="row"><strong>ARTICULO</strong></div>

        <table class="products">
            <thead>
                <tr>
                    <th>Cod.</th>
                    <th>Descripción</th>
                    <th>Cant.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->pedido_detalle as $item)
                    <tr>
                        <td>{{ $item->codigo_inven }}</td>
                        <td>{{ $item->inven_descr }}</td>
                        <td>{{ number_format((float) $item->cantidad, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="center">Sin productos</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="center muted">
            Impreso: {{ now()->format('d/m/Y h:i A') }}
        </div>
    </div>

    @if (isset($print))
        <script type="text/javascript">
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
</body>

</html>