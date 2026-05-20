<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use App\Models\OrderClient;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PedidoHistorialController extends Controller
{
    private function buildBaseQuery(Request $request, int $userId)
    {
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');
        $cliente = trim((string) $request->input('cliente'));

        $query = Pedido::with(['pedido_detalle', 'deposito'])
            ->where('user_id', $userId);

        if (!empty($estado)) {
            $query->whereRaw('UPPER(estatus) = ?', [strtoupper($estado)]);
        }

        if (!empty($fecha)) {
            $query->whereDate('created_at', $fecha);
        }

        if ($cliente !== '') {
            $rifs = OrderClient::where(function ($q) use ($cliente) {
                $q->where('NOMBRE', 'like', "%{$cliente}%")
                    ->orWhere('RIF', 'like', "%{$cliente}%");
            })->pluck('RIF');

            if ($rifs->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('rif', $rifs->toArray());
            }
        }

        return $query;
    }

    private function mapClientesByPedido($pedidos): array
    {
        $rifs = $pedidos->pluck('rif')->filter()->unique()->values();
        $clientesByRif = [];

        if ($rifs->isNotEmpty()) {
            $clientesByRif = OrderClient::whereIn('RIF', $rifs->toArray())
                ->get(['RIF', 'NOMBRE', 'TELEFONO', 'EMAIL'])
                ->keyBy('RIF')
                ->toArray();
        }

        $clientes = [];
        foreach ($pedidos as $pedido) {
            $clientes[$pedido->id] = $clientesByRif[$pedido->rif] ?? null;
        }

        return $clientes;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $this->buildBaseQuery($request, $user->id);
        $pedidos = $query->orderByDesc('created_at')->paginate(20)->appends($request->query());
        $clientes = $this->mapClientesByPedido($pedidos->getCollection());

        $kpi_total = Pedido::where('user_id', $user->id)->count();
        $kpi_monto = (float) Pedido::where('user_id', $user->id)->sum(DB::raw('COALESCE(base,0) + COALESCE(iva_bs,0)'));
        $kpi_pendientes = Pedido::where('user_id', $user->id)->whereIn(DB::raw('UPPER(estatus)'), ['PENDIENTE', 'CARGANDO', 'REVISION', 'EN REVISION'])->count();
        $kpi_entregados = Pedido::where('user_id', $user->id)->whereIn(DB::raw('UPPER(estatus)'), ['ENTREGADO', 'FACTURADO'])->count();

        return view('vendedor.pedidos.historial', compact('pedidos', 'kpi_total', 'kpi_monto', 'kpi_pendientes', 'kpi_entregados', 'clientes'));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $pedidos = $this->buildBaseQuery($request, $user->id)
            ->orderByDesc('created_at')
            ->get();
        $clientes = $this->mapClientesByPedido($pedidos);

        $fileName = 'historial_pedidos_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        return response()->stream(function () use ($pedidos, $clientes) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'Pedido', 'Fecha', 'Estatus', 'Cliente', 'RIF', 'Telefono', 'Email',
                'Productos', 'Monto', 'Condiciones', 'Observaciones', 'Fecha despacho'
            ], ';');

            foreach ($pedidos as $pedido) {
                $cli = $clientes[$pedido->id] ?? null;
                $productos = $pedido->pedido_detalle->map(function ($d) {
                    return ($d->inven_descr ?? $d->codigo_inven) . ' x' . ($d->cantidad ?? 0);
                })->implode(' | ');

                fputcsv($out, [
                    $pedido->id,
                    optional($pedido->created_at)->format('d/m/Y H:i'),
                    $pedido->estatus,
                    $cli['NOMBRE'] ?? '',
                    $pedido->rif,
                    $cli['TELEFONO'] ?? $pedido->telefono,
                    $cli['EMAIL'] ?? $pedido->email,
                    $productos,
                    number_format(((float) $pedido->base + (float) $pedido->iva_bs), 2, ',', '.'),
                    $pedido->conditions,
                    $pedido->observations,
                    $pedido->fecha_despacho,
                ], ';');
            }

            fclose($out);
        }, 200, $headers);
    }
}
