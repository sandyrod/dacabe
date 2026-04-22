<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Pago;
use App\Models\PagoPedido;
use App\Models\OrderTpago;
use App\Models\Bank;
use App\Models\PagoDestino;
use App\Models\OrderClient;
use App\Models\Descuento;
use App\Models\Vendedor;
use App\Models\PagoGrupo;
use App\Models\PedidoDetalle;
use App\Services\PaymentDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tasa;
use Carbon\Carbon;

class VendedorPagoReplicadoController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentDistributionService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function clientes(Request $request)
    {
        $user = auth()->user();
        $clientes = OrderClient::select('CLIENTE.RIF', 'CLIENTE.NOMBRE')
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('pedidos as p')
                    ->whereRaw('BINARY p.rif = BINARY CLIENTE.RIF')
                    ->where('p.user_id', $user->id)
                    ->whereIn('p.estatus', ['APROBADO', 'EN REVISION', 'PAGADO'])
                    ->where(function ($q) {
                        $q->where('p.saldo_base', '>', 0)
                            ->orWhere('p.saldo_iva_bs', '>', 0);
                    });
            })
            ->get();

        // Obtener la tasa del día para sugerirla
        $todayTasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
        $tasaSugerida = $todayTasa ? $todayTasa->valor : 1;

        return view('vendedor.pagos_new.clientes', compact('clientes', 'tasaSugerida'));
    }

    public function getPedidosCliente(Request $request, $rif)
    {
        $user = auth()->user();
        $pedidos = Pedido::select([
            'pedidos.id',
            'pedidos.fecha',
            'pedidos.rif',
            'pedidos.estatus',
            'pedidos.base as total',
            'pedidos.saldo_base',
            'pedidos.saldo_iva_bs',
        ])
            ->where('rif', $rif)
            ->where('user_id', $user->id)
            ->whereIn('estatus', ['APROBADO', 'EN REVISION', 'PAGADO'])
            ->where(function ($q) {
                $q->where('saldo_base', '>', 0)
                    ->orWhere('saldo_iva_bs', '>', 0);
            })
            ->get();

        $result = $pedidos->map(function ($pedido) {
            return [
                'id' => $pedido->id,
                'numero' => $pedido->id,
                'fecha' => Carbon::parse($pedido->fecha)->format('d/m/Y'),
                'total' => $pedido->total,
                'monto_pagado' => round($pedido->total - $pedido->saldo_base, 2),
                'saldo_pendiente' => $pedido->saldo_base,
                'saldo_iva_bs' => $pedido->saldo_iva_bs,
                'estatus' => $pedido->estatus,
                // Mantener campos para compatibilidad con la vista
                'descuento_aplicado' => 0,
                'monto_descuento' => 0,
                'saldo_con_descuento' => $pedido->saldo_base,
            ];
        });

        return response()->json($result);
    }

    public function metodoPago(Request $request, $rif)
    {
        $cliente = OrderClient::select('RIF', 'NOMBRE')
            ->where('RIF', $rif)
            ->firstOrFail();

        // Obtener los IDs de los pedidos seleccionados
        $pedidosIds = array_filter(explode(',', $request->query('pedidos', '')));
        $pedidosSeleccionados = collect();

        if (count($pedidosIds) > 0) {
            // Obtener los detalles de los pedidos seleccionados
            $pedidosSeleccionados = Pedido::select([
                'pedidos.id',
                'pedidos.fecha',
                'pedidos.fecha_despacho',
                'pedidos.estatus',
                'pedidos.base as total',
                'pedidos.saldo_base',
                'pedidos.saldo_iva_bs',
                'pedidos.factura',
                'pedidos.porc_retencion',
            ])
                ->whereIn('pedidos.id', $pedidosIds)
                ->where('pedidos.rif', $cliente->RIF)
                ->where('pedidos.estatus', '!=', 'CANCELADO')
                ->get()
                ->map(function ($pedido) {
                    $pedido->saldo_pendiente = $pedido->saldo_base;
                    $pedido->fecha_formateada = Carbon::parse($pedido->fecha)->format('d/m/Y');

                    // Para compatibilidad con la vista
                    $pedido->descuento_aplicado = 0;
                    $pedido->monto_descuento = 0;
                    $pedido->saldo_con_descuento = $pedido->saldo_base;
                    $pedido->porcentaje_descuento = 0;

                    return $pedido;
                });

            if ($pedidosSeleccionados->count() !== count($pedidosIds)) {
                return redirect()->route('vendedores.pagos_new.clientes')
                    ->with('error', 'Algunos pedidos seleccionados no son válidos');
            }
        } else {
            return redirect()->route('vendedores.pagos_new.clientes')
                ->with('error', 'No se han seleccionado pedidos para pagar');
        }

        $formasPago = OrderTpago::select('CPAGO as id', 'DPAGO as descripcion')
            ->orderBy('DPAGO')
            ->get();

        $bancos = Bank::select('id', 'codigo', 'nombre')
            ->orderBy('nombre')
            ->get();

        // Obtener la tasa del día para sugerirla
        $tasaSugerida = 1;
        $tasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
        if ($tasa) {
            $tasaSugerida = $tasa->tasa;
        }

        return view('vendedor.pagos_new.metodo', [
            'cliente' => $cliente,
            'formasPago' => $formasPago,
            'bancos' => $bancos,
            'pedidosSeleccionados' => $pedidosSeleccionados,
            'totalPagar' => $pedidosSeleccionados->sum('saldo_base'),
            'totalDescuento' => 0,
            'totalIva' => $pedidosSeleccionados->sum('saldo_iva_bs'),
            'totalRetencion' => 0,
            'tasaSugerida' => $tasaSugerida,
            'pedidosIds' => $pedidosIds
        ]);
    }

    public function index(Request $request)
    {
        // Verificar si se recibieron los parámetros del formulario de método de pago
        $clienteRif = $request->input('rif');
        $clienteNombre = $request->input('cliente_nombre');
        $tipoPago = $request->input('tipo_pago');
        $total_bolivares = $request->input('total_bolivares');
        $total_iva = $request->input('total_iva');
        $total_descuento_pago = $request->input('total_descuento_pago');
        $total_retencion = $request->input('total_retencion');

        if (!$clienteRif || !$tipoPago) {
            return redirect()->route('vendedores.pagos_new.clientes')
                ->with('error', 'Datos de pago incompletos');
        }

        // Store the payment type in the session
        session(['pago_v2_cliente.tipo_pago' => $tipoPago]);
        $formaPago = null;
        if ($tipoPago == 'divisa_total') {
            $formaPago = OrderTpago::select('CPAGO', 'DPAGO')
                ->where('DPAGO', 'like', '%$%')
                ->first();
        } else {
            $formaPago = OrderTpago::select('CPAGO', 'DPAGO')
                ->first();
        }

        session(['pago_v2_cliente.forma_pago_desc' => $formaPago->DPAGO ?? '']);

        $bancos = Bank::all();
        $pago_destinos = PagoDestino::all();

        if ($tipoPago == 'divisa_total') {
            $tipos_pago = OrderTpago::where('DPAGO', 'like', '%$%')->get();
            $pago_destinos = PagoDestino::where('nombre', 'like', '%$%')->orWhere('nombre', 'like', '%Zelle%')->get();
        } elseif ($tipoPago == 'bs') {
            $tipos_pago = OrderTpago::where('DPAGO', 'not like', '%$%')->get();
            $pago_destinos = PagoDestino::where('nombre', 'not like', '%$%')->andWhere('nombre', 'not like', '%Zelle%')->get();
        } else {
            $tipos_pago = OrderTpago::all();
        }

        // Determinar el total a pagar según el tipo de pago
        $total_pagar_divisa_parcial = 0;
        if ($tipoPago === 'divisa_total') {
            $total_pagar = $request->total_pagar_divisa;
        } else {
            $total_pagar = $request->total_pagar;
            if ($tipoPago === 'divisa_parcial') {
                $total_pagar = $request->monto_divisa;
                $total_pagar_divisa_parcial = $request->monto_bolivares_parcial;
            }
        }

        $tasa_bcv = $request->input('tasa_bcv', session('tasa_bcv', 1));
        $pedidos_seleccionados = $request->input('pedidos');
        $detallePedidos = $request->input('detalle_pedidos', '');

        return view('vendedor.pagos_new.index', compact(
            'clienteRif',
            'pedidos_seleccionados',
            'total_pagar',
            'tasa_bcv',
            'bancos',
            'pago_destinos',
            'tipos_pago',
            'total_iva',
            'total_retencion',
            'total_descuento_pago',
            'detallePedidos',
            'total_pagar_divisa_parcial'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // El RIF y los pedidos ahora vienen en el request
        $clienteRif = $request->input('rif');
        $pedidosIdsRaw = $request->input('pedidos_seleccionados');
        $pedidosIds = array_filter(explode(',', $pedidosIdsRaw));
        $tasa_bcv = (float) $request->input('tasa_bcv', 1);
        $moneda_pago = $request->input('moneda_pago', 'Divisa Total');

        if (!$clienteRif || empty($pedidosIds)) {
            return redirect()->route('vendedores.pagos_new.clientes')
                ->with('error', 'Datos de pago insuficientes.');
        }

        DB::beginTransaction();
        try {
            $seller_id = (new Vendedor)->where('email', $user->email)->value('id');

            // 1. Crear el grupo de pago
            $pagoGrupo = PagoGrupo::create([
                'moneda_pago' => $moneda_pago,
                'fecha_pago' => now(),
                'user_id' => $user->id,
                'seller_id' => $seller_id,
            ]);

            // 2. Procesar los pagos individuales del JSON
            $pagosJson = json_decode($request->input('pagos_json'), true) ?? [];
            $pagosCreated = collect();

            foreach ($pagosJson as $pagoItem) {
                // Determinar montos según la moneda de pago
                $monto_usd = 0;
                $monto_bs = 0;

                if (str_contains($moneda_pago, 'Bolívares')) {
                    $monto_bs = (float) $pagoItem['monto'];
                    $monto_usd = round($monto_bs / $tasa_bcv, 2);
                } else {
                    $monto_usd = (float) $pagoItem['monto'];
                    $monto_bs = round($monto_usd * $tasa_bcv, 2);
                }

                $pago = Pago::create([
                    'pago_grupo_id' => $pagoGrupo->id,
                    'monto' => $monto_usd,
                    'monto_bs' => $monto_bs,
                    'rate' => $tasa_bcv,
                    'descripcion' => $pagoItem['descripcion'] ?? 'Pago múltiple v2',
                    'tipo_pago' => $pagoItem['tipo_pago_desc'] ?? 'EFECTIVO',
                    'tpago_id' => $pagoItem['tpago_id'] ?? null,
                    'banco_codigo' => $pagoItem['banco_codigo'] ?? null,
                    'pago_destino_id' => $pagoItem['pago_destino_id'] ?? null,
                    'user_id' => $user->id,
                    'seller_id' => $seller_id,
                    'estatus' => 'EN REVISION',
                    'cliente_id' => $clienteRif,
                    'referencia' => $pagoItem['referencia'] ?? null,
                    'fecha' => $pagoItem['fecha_pago'] ?? now(),
                    'moneda_pago' => $moneda_pago,
                ]);

                // Manejo de archivos (comprobantes) - Opcional para v2 por ahora
                // Si se quisiera replicar la lógica de archivos, se haría aquí

                $pagosCreated->push($pago);
            }

            // 3. Obtener los pedidos y preparar el detalle para la distribución
            $pedidos = Pedido::whereIn('id', $pedidosIds)->get();

            // Usar el detallePedidos que viene del request si está disponible, sino calcularlo
            $detallePedidosInput = $request->input('detallePedidos');
            $detallePedidos = [];

            if ($detallePedidosInput) {
                $detallePedidos = json_decode($detallePedidosInput, true);
            } else {
                // Cálculo de respaldo si no viene en el request
                foreach ($pedidos as $pedido) {
                    $detallePedidos[] = [
                        'pedido_id' => $pedido->id,
                        'saldo' => $pedido->saldo_pendiente,
                        'iva' => 0, // El service lo recalcula o usa el valor base
                        'retencion' => 0,
                        'descuento' => 0
                    ];
                }
            }

            // 4. Distribuir los pagos entre los pedidos
            $this->paymentService->distributePaymentsAcrossOrders(
                $pagoGrupo,
                $pagosCreated,
                $pedidos,
                $detallePedidos,
                $moneda_pago
            );

            DB::commit();

            // Limpiar sesiones si existen
            session()->forget('pago_v2_cliente');

            return redirect()->route('vendedores.pagos_new.clientes')
                ->with('success', 'Pago registrado exitosamente. Los pedidos están ahora EN REVISIÓN.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in VendedorPagoReplicadoController@store: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function getPedidosClienteRevision($rif)
    {
        try {
            $user = auth()->user();
            $descuentos = Descuento::orderBy('dias_despacho', 'asc')->get();

            $pedidos = Pedido::select([
                'pedidos.id',
                'pedidos.id as numero',
                'pedidos.fecha',
                'pedidos.rif',
                'pedidos.estatus',
                'pedidos.fecha_despacho',
                DB::raw('(
                        SELECT SUM(COALESCE(pp.base-pp.descuento+(pp.iva-pp.retencion), 0))
                        FROM pago_grupo_detalles pp
                        WHERE pp.pedido_id = pedidos.id
                    ) as total')
            ])
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->where('pedidos.estatus', 'EN REVISION')
                ->groupBy('pedidos.id', 'pedidos.fecha', 'pedidos.rif', 'pedidos.estatus', 'pedidos.fecha_despacho')
                ->orderBy('pedidos.fecha', 'asc')
                ->havingRaw('(SELECT COUNT(*) FROM pedido_detalle WHERE pedido_id = pedidos.id) > 0')
                ->get()
                ->map(function ($pedido) use ($descuentos) {
                    $pedido->pago_grupo_id = $this->getPagoGrupoId($pedido->id);
                    $pedido->saldo_pendiente = $pedido->total - 0; // Assuming 0 for now as it's in revision
                    $pedido->fecha = Carbon::parse($pedido->fecha)->format('d/m/Y');

                    $pedido->descuento_aplicado = 0;
                    $pedido->monto_descuento = 0;
                    $pedido->saldo_con_descuento = $pedido->saldo_pendiente;

                    return $pedido;
                })
                ->values();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPedidosClienteRevision: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    public function getPedidosClientePagadas($rif)
    {
        try {
            $user = auth()->user();
            $pedidos = Pedido::select([
                'pedidos.id',
                'pedidos.id as numero',
                'pedidos.fecha',
                'pedidos.rif',
                'pedidos.estatus',
                'pedidos.fecha_despacho',
                DB::raw('(SELECT COALESCE(SUM(pp.monto), 0) FROM pagos_pedidos pp JOIN pagos p ON pp.pago_id = p.id WHERE pp.pedido_id = pedidos.id AND p.estatus = "APROBADO") as monto_pagado'),
                DB::raw('(SELECT ROUND(SUM((pd.cantidad * pd.precio_dolar) * (1 + (COALESCE(pd.iva, 0)/100))), 2) FROM pedido_detalle pd WHERE pd.pedido_id = pedidos.id) as total')
            ])
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->where('pedidos.estatus', 'PAGADO')
                ->groupBy('pedidos.id', 'pedidos.fecha', 'pedidos.rif', 'pedidos.estatus', 'pedidos.fecha_despacho')
                ->orderBy('pedidos.fecha', 'asc')
                ->get()
                ->map(function ($pedido) {
                    $pedido->pago_grupo_id = $this->getPagoGrupoId($pedido->id);
                    $pedido->fecha = Carbon::parse($pedido->fecha)->format('d/m/Y');
                    return $pedido;
                })
                ->values();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPedidosClientePagadas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    public function getPagoGrupoId($pedidoId)
    {
        return DB::connection('company')
            ->table('pago_grupos')
            ->select('pago_grupos.id')
            ->join('pagos', 'pagos.pago_grupo_id', '=', 'pago_grupos.id')
            ->join('pagos_pedidos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
            ->where('pagos_pedidos.pedido_id', $pedidoId)
            ->value('pago_grupos.id');
    }
}
