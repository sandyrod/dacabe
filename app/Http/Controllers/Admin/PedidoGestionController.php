<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ArtDepos;
use App\Models\PedidoAjuste;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PedidoGestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Pedido::query()
            ->with(['deposito', 'pedido_factura'])
            ->select('pedidos.*')
            ->orderBy('pedidos.id', 'desc');

        // Filtering
        if ($request->filled('nro')) {
            $query->where('pedidos.id', 'like', '%' . $request->nro . '%');
        }

        if ($request->filled('cliente')) {
            $query->where('pedidos.descripcion', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('vendedor')) {
            $query->where(function ($q) use ($request) {
                $q->where('pedidos.email', 'like', '%' . $request->vendedor . '%')
                    ->orWhere('pedidos.seller_code', 'like', '%' . $request->vendedor . '%');
            });
        }

        if ($request->filled('estatus') && $request->estatus != 'TODOS') {
            $query->where('pedidos.estatus', $request->estatus);
        }

        if ($request->filled('desde')) {
            $query->whereDate('pedidos.fecha', '>=', $request->desde);
        }

        if ($request->filled('hasta')) {
            $query->whereDate('pedidos.fecha', '<=', $request->hasta);
        }

        // Statistics
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $stats = [
            'total_orders_month' => Pedido::whereDate('fecha', '>=', $startOfMonth)->count(),
            'pending_orders' => Pedido::whereIn('estatus', ['CARGANDO', 'REVISION'])->count(),
            'approved_orders' => Pedido::where('estatus', 'APROBADO')->count(),
            'active_sellers' => Pedido::whereDate('fecha', '>=', $startOfMonth)->distinct('seller_code')->count('seller_code'),
        ];

        $pedidos = $query->paginate(20);

        return view('admin.pedidos.index', compact('pedidos', 'stats'));
    }

    public function toggleFactura(Request $request)
    {
        $pedido = Pedido::find($request->order_id);
        if (!$pedido) {
            return response()->json(['type' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }

        $pedido->factura = $pedido->factura == 'SI' ? 'NO' : 'SI';
        $pedido->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Estado de factura actualizado correctamente',
        ]);
    }

    public function updateRetention(Request $request)
    {
        $pedido = Pedido::find($request->order_id);
        if (!$pedido) {
            return response()->json(['type' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }

        $retencion_porc = (float) $request->retencion;
        $pedido->porc_retencion = $retencion_porc;
        $pedido->cliageret = $retencion_porc > 0 ? 1 : 0;

        // Recalcular el monto absoluto de la retención
        $ivaTotal = DB::connection('company')->table('pedido_detalle')
            ->where('pedido_id', $pedido->id)
            ->selectRaw('SUM(cantidad * precio_dolar * (iva / 100)) as total_iva')
            ->value('total_iva') ?? 0;

        //$pedido->retencion = $ivaTotal * ($retencion_porc / 100);
        $pedido->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Porcentaje de retención actualizado correctamente a ' . $retencion_porc . '%',
            'data' => [
                'porc_retencion' => $pedido->porc_retencion,
                'retencion' => $pedido->retencion,
                'cliageret' => $pedido->cliageret
            ]
        ]);
    }

    /**
     * Anular un pedido aprobado
     */
    /**
     * Anular un pedido aprobado.
     *
     * La reserva de este pedido ya fue liberada al aprobarlo (ver
     * PedidosController::updateEstatusOrder), así que aquí no se toca
     * RESERVA ni EUNIDAD — volver a descontar RESERVA robaría reserva a
     * otros pedidos pendientes del mismo producto. La reversión física del
     * stock (EUNIDAD) es responsabilidad del proceso de devolución/nota de
     * crédito en el sistema externo (FoxPro); esta acción sólo deja
     * constancia del evento en el historial de auditoría.
     */
    public function anularPedido(Request $request)
    {
        try {
            DB::connection('company')->beginTransaction();

            $pedido = DB::connection('company')
                ->table('pedidos')
                ->where('id', $request->order_id)
                ->first();

            if (!$pedido) {
                DB::connection('company')->rollBack();
                return response()->json([
                    'type' => 'error',
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            if ($pedido->estatus !== 'APROBADO') {
                DB::connection('company')->rollBack();
                return response()->json([
                    'type' => 'error',
                    'message' => 'Solo se pueden anular pedidos con estatus APROBADO'
                ], 400);
            }

            $detalles = DB::connection('company')
                ->table('pedido_detalle')
                ->where('pedido_id', $request->order_id)
                ->get();

            DB::connection('company')
                ->table('pedidos')
                ->where('id', $request->order_id)
                ->update(['estatus' => 'RECHAZADO']);

            foreach ($detalles as $detalle) {
                ArtDepos::logManual(
                    $detalle->codigo_inven,
                    $pedido->cdepos,
                    'PEDIDO_ANULADO',
                    (int) $request->order_id,
                    'Anulado por ' . auth()->user()->name . '. Reserva y stock no modificados.'
                );
            }

            DB::connection('company')->commit();

            Log::info('Pedido anulado exitosamente', [
                'pedido_id' => $request->order_id,
                'usuario' => auth()->user()->name,
                'productos' => $detalles->pluck('codigo_inven'),
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Pedido #' . $request->order_id . ' anulado exitosamente. La reserva y el stock no fueron modificados.',
                'data' => [
                    'pedido_id' => $request->order_id,
                    'nuevo_estatus' => 'RECHAZADO'
                ]
            ]);

        } catch (\Exception $e) {
            DB::connection('company')->rollBack();
            Log::error('Error al anular pedido: ' . $e->getMessage(), [
                'pedido_id' => $request->order_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Error al anular el pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    // ─── Ajustes de pedido ───────────────────────────────────────────────────

    public function getAjustes($pedidoId)
    {
        $ajustes = PedidoAjuste::where('pedido_id', $pedidoId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'tipo' => $a->tipo,
                    'concepto' => $a->concepto,
                    'monto' => $a->monto,
                    'pagado' => $a->pagado,
                    'fecha' => $a->created_at ? $a->created_at->format('d/m/Y') : '',
                ];
            });

        $neto = $ajustes->reduce(function ($carry, $a) {
            return $carry + ($a['tipo'] === 'cargo' ? $a['monto'] : -$a['monto']);
        }, 0);

        return response()->json(['success' => true, 'ajustes' => $ajustes, 'neto' => round($neto, 2)]);
    }

    public function storeAjuste(Request $request, $pedidoId)
    {
        $request->validate([
            'tipo' => 'required|in:cargo,descuento',
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
        ]);

        $ajuste = PedidoAjuste::create([
            'pedido_id' => $pedidoId,
            'tipo' => $request->tipo,
            'concepto' => $request->concepto,
            'monto' => $request->monto,
            'registrado_por' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'ajuste' => [
                'id' => $ajuste->id,
                'tipo' => $ajuste->tipo,
                'concepto' => $ajuste->concepto,
                'monto' => $ajuste->monto,
                'pagado' => $ajuste->pagado,
                'fecha' => $ajuste->created_at->format('d/m/Y'),
            ]
        ]);
    }

    public function destroyAjuste($ajusteId)
    {
        $ajuste = PedidoAjuste::find($ajusteId);
        if (!$ajuste) {
            return response()->json(['success' => false, 'message' => 'Ajuste no encontrado'], 404);
        }
        if ($ajuste->pagado) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar un ajuste ya pagado'], 422);
        }
        $ajuste->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Marcar manualmente un pedido como PAGADO
     */
    public function marcarPagado(Request $request)
    {
        $pedido = Pedido::find($request->order_id);
        if (!$pedido) {
            return response()->json(['type' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }

        if ($pedido->estatus === 'PAGADO') {
            return response()->json(['type' => 'error', 'message' => 'El pedido ya se encuentra marcado como PAGADO'], 400);
        }

        $nota = '(REPORTADO COMO PAGADO POR USUARIO ' . auth()->user()->name . ')';

        $pedido->estatus = 'PAGADO';
        $pedido->saldo_base = 0;
        $pedido->saldo_iva_bs = 0;
        $pedido->saldo_ajustes = 0;
        $pedido->observations = trim(($pedido->observations ? $pedido->observations . ' ' : '') . $nota);
        $pedido->save();

        Log::info('Pedido marcado manualmente como PAGADO', [
            'pedido_id' => $pedido->id,
            'usuario' => auth()->user()->name,
        ]);

        return response()->json([
            'type' => 'success',
            'message' => 'Pedido #' . $pedido->id . ' marcado como PAGADO correctamente',
        ]);
    }

    /**
     * Actualizar días de crédito de un pedido
     */
    public function updateDiasCredito(Request $request)
    {
        $pedido = Pedido::find($request->order_id);
        if (!$pedido) {
            return response()->json(['type' => 'error', 'message' => 'Pedido no encontrado'], 404);
        }

        $diasCredito = $request->dias_credito;

        // Validar que sea un número entero no negativo
        if ($diasCredito !== null && (!is_numeric($diasCredito) || $diasCredito < 0 || floor($diasCredito) != $diasCredito)) {
            return response()->json([
                'type' => 'error',
                'message' => 'Los días de crédito deben ser un número entero no negativo'
            ], 400);
        }

        $pedido->dias_credito = $diasCredito;
        $pedido->save();

        return response()->json([
            'type' => 'success',
            'message' => 'Días de crédito actualizados correctamente',
            'data' => [
                'dias_credito' => $pedido->dias_credito
            ]
        ]);
    }

    public function subirFacturaPdf(Request $request, $pedidoId)
    {
        $request->validate([
            'factura_pdf' => 'required|file|mimes:pdf|max:10240',
        ], [
            'factura_pdf.required' => 'Debe seleccionar un archivo PDF.',
            'factura_pdf.mimes'    => 'El archivo debe ser un PDF.',
            'factura_pdf.max'      => 'El archivo no puede superar los 10 MB.',
        ]);

        $file      = $request->file('factura_pdf');
        $carpeta   = 'facturas_pdf';
        $extension = $file->getClientOriginalExtension() ?: 'pdf';
        $nombre    = now()->format('Ymd_His') . '_' . Str::uuid() . '.' . $extension;
        $ruta      = $carpeta . '/' . $nombre;

        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($carpeta)) {
                $disk->makeDirectory($carpeta);
            }
            $contenido = file_get_contents($file->getRealPath());
            if ($contenido !== false) {
                $disk->put($ruta, $contenido);
            }
        } catch (\Throwable $e) {
            Log::warning('Fallo disco public para factura_pdf, usando fallback.', ['error' => $e->getMessage()]);
            $directorio = public_path('imgs/' . $carpeta);
            if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
                return response()->json(['type' => 'error', 'message' => 'No se pudo crear el directorio de destino.'], 500);
            }
            $file->move($directorio, $nombre);
        }

        $pedido = Pedido::findOrFail($pedidoId);
        $pedido->factura_pdf = $ruta;
        $pedido->save();

        return response()->json([
            'type'    => 'success',
            'message' => 'Factura PDF adjuntada correctamente.',
            'url'     => asset('imgs/' . $ruta),
        ]);
    }
}
