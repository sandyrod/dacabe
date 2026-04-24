<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\ArtDepos;
use App\Models\PedidoAjuste;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            // Obtener los detalles del pedido
            $detalles = DB::connection('company')
                ->table('pedido_detalle')
                ->where('pedido_id', $request->order_id)
                ->get();

            $productosInvalidos = [];
            $productosActualizados = [];

            // Validar y actualizar la reserva para cada producto
            foreach ($detalles as $detalle) {
                // Buscar el producto en ARTDEPOS
                $artdepos = DB::connection('company')
                    ->table('ARTDEPOS')
                    ->where('CODIGO', $detalle->codigo_inven)
                    ->first();

                if (!$artdepos) {
                    $productosInvalidos[] = [
                        'codigo' => $detalle->codigo_inven,
                        'motivo' => 'Producto no encontrado en ARTDEPOS'
                    ];
                    continue;
                }

                // Validar si hay suficiente reserva
                if ($artdepos->RESERVA < $detalle->cantidad) {
                    $productosInvalidos[] = [
                        'codigo' => $detalle->codigo_inven,
                        'motivo' => 'Reserva insuficiente. Disponible: ' . $artdepos->RESERVA . ', Requerido: ' . $detalle->cantidad
                    ];
                    continue;
                }

                // Descontar de la reserva
                DB::connection('company')
                    ->table('ARTDEPOS')
                    ->where('CODIGO', $detalle->codigo_inven)
                    ->decrement('RESERVA', $detalle->cantidad);

                $productosActualizados[] = [
                    'codigo' => $detalle->codigo_inven,
                    'cantidad_anulada' => $detalle->cantidad,
                    'reserva_anterior' => $artdepos->RESERVA,
                    'reserva_nueva' => $artdepos->RESERVA - $detalle->cantidad
                ];
            }

            // Si hay productos inválidos, verificar si son solo de reserva insuficiente
            if (!empty($productosInvalidos)) {
                // Verificar si todos los errores son de reserva insuficiente
                $todosReservaInsuficiente = true;
                $productosConReservaInsuficiente = [];

                foreach ($productosInvalidos as $producto) {
                    if (strpos($producto['motivo'], 'Reserva insuficiente') !== false) {
                        $productosConReservaInsuficiente[] = $producto;
                    } else {
                        $todosReservaInsuficiente = false;
                        break;
                    }
                }

                // Si todos son de reserva insuficiente, permitir opción de continuar
                if ($todosReservaInsuficiente) {
                    DB::connection('company')->rollBack();

                    $mensajeError = 'Los siguientes productos tienen reserva insuficiente:<br><br>';
                    foreach ($productosConReservaInsuficiente as $producto) {
                        $mensajeError .= '<strong>' . $producto['codigo'] . ':</strong> ' . $producto['motivo'] . '<br>';
                    }
                    $mensajeError .= '<br>¿Desea continuar y anular el pedido sin modificar las reservas?';

                    return response()->json([
                        'type' => 'warning',
                        'message' => $mensajeError,
                        'productos_invalidos' => $productosConReservaInsuficiente,
                        'permitir_continuar' => true,
                        'pedido_id' => $request->order_id
                    ], 200);
                } else {
                    // Si hay otros errores (productos no encontrados), no permitir continuar
                    DB::connection('company')->rollBack();

                    $mensajeError = 'No se puede anular el pedido por los siguientes productos:<br><br>';
                    foreach ($productosInvalidos as $producto) {
                        $mensajeError .= '<strong>' . $producto['codigo'] . ':</strong> ' . $producto['motivo'] . '<br>';
                    }

                    return response()->json([
                        'type' => 'error',
                        'message' => $mensajeError,
                        'productos_invalidos' => $productosInvalidos
                    ], 400);
                }
            }

            // Cambiar el estatus del pedido a RECHAZADO
            DB::connection('company')
                ->table('pedidos')
                ->where('id', $request->order_id)
                ->update(['estatus' => 'RECHAZADO']);

            DB::connection('company')->commit();

            Log::info('Pedido anulado exitosamente', [
                'pedido_id' => $request->order_id,
                'usuario' => auth()->user()->name,
                'productos_actualizados' => $productosActualizados
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Pedido #' . $request->order_id . ' anulado exitosamente. Se descontó la reserva de ' . count($productosActualizados) . ' productos.',
                'data' => [
                    'pedido_id' => $request->order_id,
                    'productos_actualizados' => $productosActualizados,
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
     * Anular un pedido aprobado sin modificar reservas
     */
    public function anularPedidoSinReserva(Request $request)
    {
        try {
            $pedido = DB::connection('company')
                ->table('pedidos')
                ->where('id', $request->order_id)
                ->first();

            if (!$pedido) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Pedido no encontrado'
                ], 404);
            }

            if ($pedido->estatus !== 'APROBADO') {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Solo se pueden anular pedidos con estatus APROBADO'
                ], 400);
            }

            // Cambiar el estatus del pedido a RECHAZADO sin modificar reservas
            DB::connection('company')
                ->table('pedidos')
                ->where('id', $request->order_id)
                ->update(['estatus' => 'RECHAZADO']);

            Log::info('Pedido anulado sin modificar reservas', [
                'pedido_id' => $request->order_id,
                'usuario' => auth()->user()->name,
                'motivo' => 'Reserva insuficiente en productos'
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Pedido #' . $request->order_id . ' anulado exitosamente. Las reservas no fueron modificadas.',
                'data' => [
                    'pedido_id' => $request->order_id,
                    'nuevo_estatus' => 'RECHAZADO',
                    'reservas_modificadas' => false
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al anular pedido sin reserva: ' . $e->getMessage(), [
                'pedido_id' => $request->order_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Error al anular el pedido: ' . $e->getMessage()
            ], 500);
        }
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
}
