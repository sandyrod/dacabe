<?php

namespace App\Http\Controllers;

use App\Models\{Pedido, Pago, PagoPedido, OrderTpago, Descuento, Bank, PagoDestino, OrderClient, Vendedor, Tasa, PagoArchivo, PedidoAjuste};
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Traits\HasRoles;

class VendedorPagoController extends Controller
{
    private function uploadComprobante($file): string
    {
        $name = Storage::disk('local')->put('public/comprobantes', $file);
        return substr($name, 7);
    }

    private function guardarArchivoPublico($file, string $carpeta = 'comprobantes'): string
    {
        $carpeta = trim($carpeta, '/');
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $nombreArchivo = now()->format('Ymd_His') . '_' . Str::uuid() . ($extension ? '.' . $extension : '');
        $ruta = $carpeta . '/' . $nombreArchivo;

        // Intento principal: disco "public" (storage/app/public)
        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($carpeta)) {
                $disk->makeDirectory($carpeta);
            }

            $contenido = file_get_contents($file->getRealPath());
            $guardado = $contenido !== false ? $disk->put($ruta, $contenido) : false;
            if ($guardado !== false && $disk->exists($ruta)) {
                return $ruta;
            }
        } catch (\Throwable $e) {
            Log::warning('Fallo guardado en disk public, se intentará fallback en public/imgs.', [
                'carpeta' => $carpeta,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback Linux: guardar directo en public/imgs/{carpeta}
        $directorioPublico = public_path('imgs/' . $carpeta);
        if (!is_dir($directorioPublico) && !mkdir($directorioPublico, 0775, true) && !is_dir($directorioPublico)) {
            throw new \RuntimeException('No se pudo crear el directorio de destino en public/imgs.');
        }

        $file->move($directorioPublico, $nombreArchivo);
        if (!file_exists($directorioPublico . DIRECTORY_SEPARATOR . $nombreArchivo)) {
            throw new \RuntimeException('No se pudo guardar físicamente el archivo en storage/public ni en public/imgs.');
        }

        return $ruta;
    }

    public function clientes(Request $request)
    {
        $user = auth()->user();

        // Primero obtenemos los clientes con pedidos aprobados
        $clientes = OrderClient::select(
            'CLIENTE.RIF',
            'CLIENTE.NOMBRE'
        )
            ->whereExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('pedidos as p')
                    ->whereRaw('BINARY p.rif = BINARY CLIENTE.RIF')
                    ->where('p.user_id', $user->id)
                    ->whereIn('p.estatus', ['APROBADO', 'EN REVISION'])
                    ->where(function ($q) {
                        $q->where('p.saldo_base', '>', 0)
                            ->orWhere('p.saldo_iva_bs', '>', 0)
                            ->orWhere('p.saldo_ajustes', '>', 0);
                    });
            })
            ->selectSub(function ($query) use ($user) {
                $query->select(DB::raw('COUNT(DISTINCT p2.id)'))
                    ->from('pedidos as p2')
                    ->whereRaw('BINARY p2.rif = BINARY CLIENTE.RIF')
                    ->where('p2.user_id', $user->id)
                    ->where('p2.estatus', 'APROBADO')
                    ->where(function ($q) {
                        $q->where('p2.saldo_base', '>', 0)
                            ->orWhere('p2.saldo_iva_bs', '>', 0)
                            ->orWhere('p2.saldo_ajustes', '>', 0);
                    });
            }, 'pedidos_pendientes')
            ->addSelect([
                'total_pendiente' => function ($query) use ($user) {
                    $query->select(DB::raw('ROUND(COALESCE(SUM(p3.saldo_base + p3.saldo_ajustes), 0), 2)'))
                        ->from('pedidos as p3')
                        ->whereRaw('BINARY p3.rif = BINARY CLIENTE.RIF')
                        ->where('p3.user_id', $user->id)
                        ->where('p3.estatus', 'APROBADO')
                        ->where(function ($q) {
                            $q->where('p3.saldo_base', '>', 0)
                                ->orWhere('p3.saldo_iva_bs', '>', 0)
                                ->orWhere('p3.saldo_ajustes', '>', 0);
                        });
                }
            ])
            ->addSelect([
                'tiene_pedidos_vencidos' => function ($query) use ($user) {
                    $query->select(DB::raw('COUNT(DISTINCT p4.id)'))
                        ->from('pedidos as p4')
                        ->whereRaw('BINARY p4.rif = BINARY CLIENTE.RIF')
                        ->where('p4.user_id', $user->id)
                        ->where('p4.estatus', 'APROBADO')
                        ->whereNotNull('p4.dias_credito')
                        ->where('p4.dias_credito', '>', 0)
                        ->whereNotNull('p4.fecha_despacho')
                        ->whereRaw('TRIM(COALESCE(p4.fecha_despacho, "")) != ""')
                        ->where(function ($q) {
                            $q->whereRaw("DATE_ADD(p4.fecha_despacho, INTERVAL p4.dias_credito DAY) < CURDATE()");
                        });
                }
            ])
            ->orderBy('CLIENTE.NOMBRE')
            ->get();

        // Obtener todos los descuentos ordenados por días de despacho
        $descuentos = Descuento::orderBy('dias_despacho', 'asc')->get();

        // Si es una solicitud POST, obtener los datos del pago
        $pagoData = null;
        if ($request->isMethod('post')) {
            $pagoData = [
                'rif' => $request->input('rif'),
                'pedidos' => $request->input('pedidos'),
                'tipo_pago' => $request->input('tipo_pago'),
                'total_pagar' => $request->input('total_pagar'),
                'total_descuento' => $request->input('total_descuento'),
                'monto_divisa' => $request->input('monto_divisa'),
                'banco_id' => $request->input('banco_id'),
                'referencia' => $request->input('referencia'),
                'fecha' => $request->input('fecha'),
                'comprobante' => $request->file('comprobante')
            ];
        }

        // Obtener la tasa del día para sugerirla
        $todayTasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
        $tasaSugerida = $todayTasa ? $todayTasa->valor : null;

        return view('vendedor.pagos.clientes', compact('clientes', 'descuentos', 'pagoData', 'tasaSugerida'));
    }

    /**
     * Obtiene los pedidos pendientes de pago de un cliente especÃ­fico
     *
     * @param string $rif RIF del cliente
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPedidosCliente($rif)
    {
        try {
            $user = auth()->user();

            // Obtener todos los descuentos ordenados por días de despacho
            $descuentos = Descuento::orderBy('dias_despacho', 'asc')->get();

            /*
            // Obtener pedidos del cliente con sus pagos y calcular el total desde pedido_detalle
            $pedidos = Pedido::select([
                'pedidos.id',
                'pedidos_facturas.factura as factura_numero',
                'pedidos.id as numero',
                'pedidos.fecha',
                'pedidos.rif',
                'pedidos.estatus',
                'pedidos.fecha_despacho',
                DB::raw('(SELECT COALESCE(SUM(pp.monto), 0)
                             FROM pagos_pedidos pp
                             JOIN pagos p ON pp.pago_id = p.id
                             WHERE pp.pedido_id = pedidos.id
                             AND p.estatus = "APROBADO") as monto_pagado'),
                DB::raw('(
                        SELECT
                            ROUND(
                                SUM(
                                    (pd.cantidad * pd.precio_dolar) * (1 + (COALESCE(pd.iva, 0)/100))
                                ), 2
                            )
                            -
                            CASE
                                WHEN pedidos.porc_retencion > 0 THEN
                                    ROUND(
                                        SUM(
                                            (pd.cantidad * pd.precio_dolar) * (COALESCE(pd.iva, 0)/100)
                                        ) * (pedidos.porc_retencion / 100), 2
                                    )
                                ELSE 0
                            END
                        FROM pedido_detalle pd
                        WHERE pd.pedido_id = pedidos.id
                    ) as total')
            ])
                ->leftJoin('pedidos_facturas', 'pedidos.id', '=', 'pedidos_facturas.pedido_id')
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->where('pedidos.estatus', 'APROBADO')
                ->groupBy('pedidos.id', 'pedidos.fecha', 'pedidos.rif', 'pedidos.estatus', 'pedidos.fecha_despacho', 'pedidos_facturas.factura')
                ->havingRaw('(SELECT ROUND(SUM((pd.cantidad * pd.precio_dolar) * (1 + (COALESCE(pd.iva, 0)/100))), 2)
                             FROM pedido_detalle pd
                             WHERE pd.pedido_id = pedidos.id) >
                             (SELECT COALESCE(SUM(pp.monto), 0)
                              FROM pagos_pedidos pp
                              JOIN pagos p ON pp.pago_id = p.id
                              WHERE pp.pedido_id = pedidos.id
                              AND p.estatus = "APROBADO")')
                ->orderBy('pedidos.fecha', 'asc')
                ->havingRaw('(SELECT COUNT(*) FROM pedido_detalle WHERE pedido_id = pedidos.id) > 0')
                ->get()
                ->map(function ($pedido) use ($descuentos) {
                    $pedido->saldo_pendiente = $pedido->total - $pedido->monto_pagado;
                    $pedido->fecha = Carbon::parse($pedido->fecha)->format('d/m/Y');

                    // Inicializar variables de descuento
                    $pedido->descuento_aplicado = 0;
                    $pedido->monto_descuento = 0;
                    $pedido->saldo_con_descuento = $pedido->saldo_pendiente;

                    // Verificar si hay fecha de despacho para aplicar descuentos
                    if ($pedido->fecha_despacho) {
                        $fechaDespacho = Carbon::parse($pedido->fecha_despacho);
                        $hoy = Carbon::now();
                        $diasDiferencia = $fechaDespacho->diffInDays($hoy, false);

                        // Buscar el descuento que aplique según los días de diferencia
                        foreach ($descuentos as $descuento) {
                            if ($diasDiferencia <= $descuento->dias_despacho) {
                                $pedido->descuento_aplicado = $descuento->porcentaje;
                                $pedido->monto_descuento = $pedido->saldo_pendiente * ($descuento->porcentaje / 100);
                                $pedido->saldo_con_descuento = $pedido->saldo_pendiente - $pedido->monto_descuento;
                                break;
                            }
                        }
                    }

                    return $pedido;
                })
                ->filter(function ($pedido) {
                    // Filtrar solo pedidos con saldo pendiente mayor a 0
                    return $pedido->saldo_pendiente > 0.01;
                })
                ->values();
            */

            // Nuevo select a partir de la tabla pedidos
            $pedidos = Pedido::select([
                'pedidos.id',
                'pedidos_facturas.factura as factura_numero',
                'pedidos.id as numero',
                'pedidos.fecha',
                'pedidos.rif',
                'pedidos.estatus',
                'pedidos.fecha_despacho',
                'pedidos.base as total',
                'pedidos.saldo_base',
                'pedidos.saldo_iva_bs',
                'pedidos.saldo_ajustes',
                'pedidos.total_ajustes',
                'pedidos.porc_retencion',
                'pedidos.comprobante_retencion',
                'pedidos.dias_credito',
                DB::raw('(pedidos.base - pedidos.saldo_base) as monto_pagado'),
                DB::raw('CASE 
                    WHEN pedidos.dias_credito IS NOT NULL 
                         AND pedidos.dias_credito > 0 
                        AND pedidos.fecha_despacho IS NOT NULL
                        AND TRIM(COALESCE(pedidos.fecha_despacho, "")) != ""
                        AND DATE_ADD(pedidos.fecha_despacho, INTERVAL pedidos.dias_credito DAY) < CURDATE() 
                    THEN 1 ELSE 0 END as esta_vencido')
            ])
                ->leftJoin('pedidos_facturas', 'pedidos.id', '=', 'pedidos_facturas.pedido_id')
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->whereIn('pedidos.estatus', ['APROBADO', 'EN REVISION'])
                ->where(function ($query) {
                    $query->where('pedidos.saldo_base', '>', 0)
                        ->orWhere('pedidos.saldo_iva_bs', '>', 0)
                        ->orWhere('pedidos.saldo_ajustes', '>', 0);
                })
                ->orderBy('pedidos.fecha', 'asc')
                ->get();

            // Los pagos en bolívares reducen los saldos al registrarse. Los pagos en divisa
            // los reducen al aprobarse, por lo que sus asignaciones EN REVISION deben
            // descontarse para exponer únicamente el saldo todavía disponible para abonos.
            $compromisosDivisa = PagoPedido::query()
                ->join('pagos', 'pagos.id', '=', 'pagos_pedidos.pago_id')
                ->whereIn('pagos_pedidos.pedido_id', $pedidos->pluck('id'))
                ->where('pagos.estatus', 'EN REVISION')
                ->where(function ($query) {
                    $query->where('pagos.moneda_pago', '!=', 'Bolívares')
                        ->orWhereNull('pagos.moneda_pago');
                })
                ->groupBy('pagos_pedidos.pedido_id')
                ->selectRaw('pagos_pedidos.pedido_id,
                    COALESCE(SUM(pagos_pedidos.monto + COALESCE(pagos_pedidos.descuento, 0)), 0) as saldo_base_comprometido,
                    COALESCE(SUM(pagos_pedidos.iva), 0) as saldo_iva_comprometido,
                    COALESCE(SUM(pagos_pedidos.ajustes_monto), 0) as saldo_ajustes_comprometido')
                ->get()
                ->keyBy('pedido_id');

            $pedidos = $pedidos
                ->map(function ($pedido) use ($compromisosDivisa) {
                    $compromiso = $compromisosDivisa->get($pedido->id);
                    $pedido->saldo_base = max(
                        (float) $pedido->saldo_base - (float) ($compromiso->saldo_base_comprometido ?? 0),
                        0
                    );
                    $pedido->saldo_iva_bs = max(
                        (float) $pedido->saldo_iva_bs - (float) ($compromiso->saldo_iva_comprometido ?? 0),
                        0
                    );
                    $pedido->saldo_ajustes = max(
                        (float) ($pedido->saldo_ajustes ?? 0) - (float) ($compromiso->saldo_ajustes_comprometido ?? 0),
                        0
                    );

                    $pedido->ajustes_neto = $pedido->saldo_ajustes;
                    $pedido->ajustes_detalle = PedidoAjuste::where('pedido_id', $pedido->id)
                        ->where('pagado', false)
                        ->get(['tipo', 'concepto', 'monto'])
                        ->map(fn($a) => ['tipo' => $a->tipo, 'concepto' => $a->concepto, 'monto' => (float) $a->monto])
                        ->values()
                        ->toArray();

                    $pedido->saldo_pendiente    = round((float) $pedido->saldo_base + (float) $pedido->saldo_ajustes, 2);
                    $pedido->fecha              = Carbon::parse($pedido->fecha)->format('d/m/Y');
                    $pedido->descuento_aplicado = 0;
                    $pedido->monto_descuento    = 0;
                    $pedido->saldo_con_descuento = $pedido->saldo_pendiente;

                    return $pedido;
                })
                ->filter(function ($pedido) {
                    return (float) $pedido->saldo_base > 0.01
                        || (float) $pedido->saldo_iva_bs > 0.01
                        || (float) $pedido->saldo_ajustes > 0.01;
                })
                ->values();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener pedidos del cliente:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los pedidos del cliente',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function getPedidosClienteRevision($rif)
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
                'pedidos.base as total',
                'pedidos.saldo_base',
                'pedidos.saldo_iva_bs',
                'pedidos.saldo_ajustes',
            ])
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->where('pedidos.estatus', 'EN REVISION')
                ->orderBy('pedidos.fecha', 'asc')
                ->get()
                ->map(function ($pedido) {
                    $pedido->fecha = Carbon::parse($pedido->fecha)->format('d/m/Y');
                    return $pedido;
                })
                ->values();

            return response()->json([
                'success' => true,
                'pedidos' => $pedidos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener pedidos del cliente (Revision):', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los pedidos del cliente',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function getPagoGrupoId($pedidoId)
    {
        $pagoGrupoId = DB::connection('company')
            ->table('pago_grupos')
            ->select('pago_grupos.id')
            ->join('pagos', 'pagos.pago_grupo_id', '=', 'pago_grupos.id')
            ->join('pagos_pedidos', 'pagos_pedidos.pago_id', '=', 'pagos.id')
            ->where('pagos_pedidos.pedido_id', $pedidoId)
            ->value('pago_grupos.id');

        return $pagoGrupoId;
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
                'pedidos.base as total',
                DB::raw('(pedidos.base - pedidos.saldo_base) as monto_pagado'),
                'pedidos.saldo_base',
                'pedidos.saldo_iva_bs',
            ])
                ->where('pedidos.rif', $rif)
                ->where('pedidos.user_id', $user->id)
                ->where('pedidos.estatus', 'PAGADO')
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
            \Log::error('Error al obtener pedidos del cliente (Pagadas):', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los pedidos del cliente',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }


    public function metodoPago($clienteId, Request $request)
    {
        $cliente = OrderClient::select('RIF', 'NOMBRE')
            ->where('RIF', $clienteId)
            ->first();

        if (!$cliente) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Cliente no encontrado');
        }

        // Obtener los IDs de los pedidos seleccionados
        $pedidosIds = $request->query('pedidos');
        $ajustesData = $request->query('ajustes');
        $pedidosSeleccionados = collect();

        if ($pedidosIds) {
            $pedidosIds = array_map('trim', explode(',', $pedidosIds));
            
            // Procesar los ajustes si existen
            $ajustesMap = [];
            if ($ajustesData) {
                $ajustesArray = json_decode(urldecode($ajustesData), true);
                if (is_array($ajustesArray)) {
                    foreach ($ajustesArray as $ajuste) {
                        $ajustesMap[$ajuste['id']] = $ajuste['ajustes_neto'];
                    }
                }
            }

            // Obtener los detalles de los pedidos seleccionados directamente de la tabla pedidos
            $pedidosSeleccionados = Pedido::select([
                'pedidos.id',
                'pedidos.fecha',
                'pedidos.fecha_despacho',
                'pedidos.estatus',
                'pedidos.base as total',
                'pedidos.saldo_base',
                'pedidos.saldo_iva_bs',
                'pedidos.factura',
                'pedidos_facturas.factura as factura_numero',
                'pedidos.porc_retencion',
                'pedidos.descuento',
                'pedidos.iva_bs',
                'pedidos.total_ajustes',
                'pedidos.saldo_ajustes',
            ])
                ->leftJoin('pedidos_facturas', 'pedidos.id', '=', 'pedidos_facturas.pedido_id')
                ->whereIn('pedidos.id', $pedidosIds)
                ->where('pedidos.rif', $cliente->RIF)
                ->where('pedidos.estatus', '!=', 'CANCELADO')
                ->get()
                ->map(function ($pedido) use ($ajustesMap) {
                    $pedido->saldo_pendiente = $pedido->saldo_base;
                    $pedido->fecha_formateada = Carbon::parse($pedido->fecha)->format('d/m/Y');

                    // netoPendiente() es la fuente canónica: suma registros pedido_ajustes con pagado=false.
                    // saldo_ajustes puede estar desactualizado si se crearon ajustes sin actualizar ese campo.
                    $pedido->ajustes_neto = PedidoAjuste::netoPendiente((int) $pedido->id);
                    $pedido->saldo_con_ajustes = $pedido->saldo_pendiente + $pedido->ajustes_neto;

                    // Obtener detalles de ajustes para mostrar
                    $pedido->ajustes_detalle = PedidoAjuste::where('pedido_id', $pedido->id)
                        ->where('pagado', false)
                        ->get(['tipo', 'concepto', 'monto'])
                        ->map(fn($a) => [
                            'tipo' => $a->tipo, 
                            'concepto' => $a->concepto, 
                            'monto' => (float) $a->monto,
                            'neto' => $a->tipo === 'cargo' ? (float) $a->monto : -(float) $a->monto
                        ])
                        ->values()
                        ->toArray();

                    // Para compatibilidad con la vista
                    $pedido->descuento_aplicado = abs($pedido->descuento);
                    $pedido->porcentaje_descuento = abs($pedido->descuento);
                    $pedido->monto_descuento = $pedido->saldo_base * ($pedido->descuento_aplicado / 100);
                    $pedido->saldo_con_descuento = $pedido->saldo_base - $pedido->monto_descuento;
                    $pedido->porcentaje_retencion_pago = (float) ($pedido->saldo_iva_bs ?? 0) > 0.01
                        ? ((float) $pedido->porc_retencion > 0 ? (float) $pedido->porc_retencion : 75)
                        : 0;
                    $pedido->retencion = min(
                        (float) ($pedido->saldo_iva_bs ?? 0),
                        (float) $pedido->iva_bs * ($pedido->porcentaje_retencion_pago / 100)
                    );

                    return $pedido;
                });
        }

        // Obtener la tasa del día para sugerirla
        $tasaSugerida = 1;
        $tasa = Tasa::where('fecha', now()->format('Y-m-d'))->first();
        if ($tasa) {
            $tasaSugerida = $tasa->valor;
        }
        $bancos = (new Bank)->getData();

        $retencionPorcentajes = $pedidosSeleccionados
            ->pluck('porcentaje_retencion_pago', 'id')
            ->filter(fn ($porcentaje) => (float) $porcentaje > 0)
            ->all();

        return view('vendedor.pagos.metodo', compact(
            'cliente',
            'pedidosSeleccionados',
            'tasaSugerida',
            'bancos',
            'retencionPorcentajes'
        ));
    }

    public function mostrarConfirmacion(Request $request)
    {
        $request->validate([
            'rif' => 'required|string',
            'pedidos' => 'required|string',
            'tipo_pago' => 'required|in:bs,divisa_total,divisa_parcial',
            'total_pagar' => 'required|numeric|min:0',
            'total_descuento' => 'required|numeric|min:0',
            'monto_divisa' => 'nullable|numeric|min:0',
        ]);

        // Obtener datos del cliente
        $cliente = DB::table('CLIENTE')
            ->where('RIF', $request->rif)
            ->first();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener información de los pedidos directamente de la tabla pedidos
        $pedidosIds = explode(',', $request->pedidos);
        $pedidos = Pedido::select([
            'pedidos.id',
            'pedidos.fecha',
            'pedidos.rif',
            'pedidos.estatus',
            'pedidos.base as total',
            'pedidos.saldo_base',
            'pedidos.saldo_iva_bs',
        ])
            ->whereIn('id', $pedidosIds)
            ->get();

        // Calcular totales
        $totalPagar = (float)$request->total_pagar;
        $totalDescuento = (float)$request->total_descuento;
        $tipoPago = $request->tipo_pago;
        $montoDivisa = $tipoPago === 'divisa_parcial' ? (float)$request->monto_divisa : null;

        return view('vendedor.pagos.confirmar', compact(
            'cliente',
            'pedidos',
            'totalPagar',
            'totalDescuento',
            'tipoPago',
            'montoDivisa',
            'pedidosIds'
        ));
    }

    public function procesarPago(Request $request, $rif)
    {
        // Validar los datos del formulario
        $validated = $request->validate([
            'tipo_pago' => 'required|in:bs,divisa_total,divisa_parcial',
            'total_pagar' => 'required|numeric|min:0.01',
            'total_descuento' => 'required|numeric|min:0',
            'monto_divisa' => 'nullable|required_if:tipo_pago,divisa_parcial|numeric|min:0',
            'pedidos' => 'required|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Obtener el usuario autenticado
        $user = auth()->user();

        // Obtener el cliente
        $cliente = OrderClient::where('RIF', $rif)->first();

        if (!$cliente) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Cliente no encontrado');
        }

        // Obtener los IDs de los pedidos
        $pedidosIds = explode(',', $request->pedidos);

        // Obtener los pedidos con sus saldos directamente de la tabla pedidos
        $pedidos = Pedido::select([
            'pedidos.id',
            'pedidos.fecha',
            'pedidos.rif',
            'pedidos.estatus',
            'pedidos.base as total',
            'pedidos.saldo_base',
            'pedidos.saldo_iva_bs',
            'pedidos.saldo_ajustes',
            'pedidos.total_ajustes',
        ])
            ->whereIn('id', $pedidosIds)
            ->where('rif', $cliente->RIF)
            ->where('user_id', $user->id)
            ->where('estatus', '!=', 'CANCELADO')
            ->get()
            ->map(function ($pedido) {
                // El saldo pendiente total incluye ajustes
                $pedido->saldo_pendiente = $pedido->saldo_base + $pedido->saldo_ajustes;
                $pedido->monto_pagado = $pedido->total - $pedido->saldo_pendiente;

                return $pedido;
            });

        // Verificar que todos los pedidos pertenezcan al cliente
        if ($pedidos->count() !== count($pedidosIds)) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Algunos pedidos seleccionados no son vÃ¡lidos');
        }

        // Obtener los totales del formulario
        $totalPagar = (float) $request->total_pagar;
        $totalDescuentoSolicitado = 0; //(float) $request->total_descuento;
        $tipoPago = $request->tipo_pago;
        $montoDivisa = $tipoPago === 'divisa_parcial' ? (float) $request->monto_divisa : null;
        $observaciones = $request->input('observaciones');
        $tasaBcv = (float) $request->input('tasa_bcv', 0);
        if ($tasaBcv <= 0) {
            $tasaHoy = Tasa::where('fecha', now()->format('Y-m-d'))->first();
            $tasaBcv = (float) ($tasaHoy->tasa ?? 1);
        }

        $esPagoDivisa = in_array($tipoPago, ['divisa_total', 'divisa_parcial']);
        $cumpleCriterioDescuento = $esPagoDivisa && $pedidos->every(function ($pedido) {
            return abs(((float) $pedido->saldo_base) - ((float) $pedido->total)) <= 0.01;
        });
        $totalDescuento = $cumpleCriterioDescuento ? $totalDescuentoSolicitado : 0;

        // Validar que el total a pagar sea válido
        $saldoTotal = $pedidos->sum('saldo_pendiente');
        $saldoConDescuento = $saldoTotal - $totalDescuento;

        if (abs($totalPagar - $saldoConDescuento) > 0.01) { // Permitir pequeñas diferencias por redondeo
            return redirect()->back()
                ->with('error', 'El monto total a pagar no coincide con el saldo pendiente')
                ->withInput();
        }

        // Determinar si se deben saldar los pedidos
        $esUnSoloPedido = count($pedidosIds) === 1;
        $pagoCubreTotal = $totalPagar >= ($saldoConDescuento - 0.01); // Permitir pequeña tolerancia

        // Saldar pedidos solo si: 
        // 1. Es más de un pedido, O
        // 2. Es un solo pedido Y el pago no cubre el total
        $deberSaldarPedidos = !$esUnSoloPedido || $pagoCubreTotal;

        // Iniciar transacción
        DB::beginTransaction();

        try {
            // Crear el pago
            $pago = new Pago();
            $pago->fecha = now();
            $pago->monto = $totalPagar;
            $pago->metodo_pago = 'efectivo'; // Por defecto, ya que ahora solo manejamos efectivo
            $pago->tipo_pago = $tipoPago;
            $pago->estatus = 'aprobado'; // Aprobado automáticamente ya que es en efectivo
            $pago->user_id = $user->id;
            $pago->cliente_rif = $cliente->RIF;
            $pago->cliente_nombre = $cliente->NOMBRE;
            $pago->observaciones = $observaciones;
            $pago->descuento_aplicado = $totalDescuento > 0 ? 1 : 0;
            $pago->monto_descuento = $totalDescuento;

            // Guardar información de la divisa si aplica
            if (in_array($tipoPago, ['divisa_total', 'divisa_parcial'])) {
                $pago->moneda = 'USD';
                $pago->tasa_cambio = 1; // Asumiendo que el monto ya está en la moneda de destino

                if ($tipoPago === 'divisa_parcial') {
                    $pago->monto_divisa = $montoDivisa;
                    $pago->monto_bs = $totalPagar - $montoDivisa;
                } else {
                    $pago->monto_divisa = $totalPagar;
                    $pago->monto_bs = 0;
                }
            }

            $pago->save();

            // Asignar el pago a los pedidos seleccionados
            $montoRestante = $totalPagar;
            $descuentoRestante = $totalDescuento;

            // Ordenar los pedidos por fecha (los más antiguos primero)
            $pedidos = $pedidos->sortBy('fecha');

            foreach ($pedidos as $pedido) {
                if ($montoRestante <= 0.01) break; // Usar una pequeña tolerancia para comparaciones de punto flotante

                // Aplicar descuento solo cuando cumple el criterio (pago en divisa y saldo_base == base).
                if ($descuentoRestante > 0.01) {
                    $descuentoParaPedido = min($descuentoRestante, (float) $pedido->saldo_base);
                    $pedido->saldo_base = max(((float) $pedido->saldo_base) - (float) $descuentoParaPedido, 0);
                    $descuentoRestante -= $descuentoParaPedido;
                }

                // Calcular el monto total pendiente (base + ajustes)
                $saldoTotalPendiente = (float) $pedido->saldo_base + (float) $pedido->saldo_ajustes;
                $montoParaEstePedido = min($montoRestante, $saldoTotalPendiente);

                // Crear el registro en la tabla de pagos_pedidos
                $pagoPedido = new PagoPedido();
                $pagoPedido->pago_id = $pago->id;
                $pagoPedido->pedido_id = $pedido->id;
                $pagoPedido->monto = $montoParaEstePedido;
                $pagoPedido->save();

                $montoRestante -= $montoParaEstePedido;

                // Actualizar saldos
                $pedido->saldo_base = max(((float) $pedido->saldo_base) - (float) $montoParaEstePedido, 0);
                
                // Si el pago es en bolívares, también descontar saldo_iva_bs con su equivalente en Bs.
                if ($tipoPago === 'bs') {
                    $montoAplicadoBs = (float) $montoParaEstePedido * (float) $tasaBcv;
                    $pedido->saldo_iva_bs = max(((float) $pedido->saldo_iva_bs) - $montoAplicadoBs, 0);
                }

                // Si el pago cubre el total, saldar los ajustes también
                if ($deberSaldarPedidos && $saldoTotalPendiente <= $montoParaEstePedido + 0.01) {
                    $pedido->saldo_ajustes = 0;
                    PedidoAjuste::marcarPagados((int) $pedido->id);
                }

                // Determinar el nuevo estado del pedido.
                // Solo PAGADO si base, IVA y ajustes quedaron en cero; en otro caso queda APROBADO.
                if ($deberSaldarPedidos && $pedido->saldo_base <= 0.01 && (float) $pedido->saldo_iva_bs <= 0.01 && (float) ($pedido->saldo_ajustes ?? 0) <= 0.01) {
                    $pedido->saldo_base = 0;
                    $pedido->saldo_iva_bs = 0;
                    $pedido->saldo_ajustes = 0;
                    $pedido->estatus = 'PAGADO';
                    PedidoAjuste::marcarPagados((int) $pedido->id);
                } else {
                    $pedido->estatus = 'APROBADO';
                }

                $pedido->save();
            }

            // Confirmar la transacción
            DB::commit();

            // Redirigir con mensaje de éxito
            return redirect()->route('vendedores.pagos.comprobante', $pago->id)
                ->with('success', 'Pago registrado exitosamente');
        } catch (\Exception $e) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            \Log::error('Error al procesar el pago: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al procesar el pago. Por favor, intente nuevamente.')
                ->withInput();
        }
    }

    /**
     * Muestra el comprobante de pago
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function mostrarComprobante($id)
    {
        $pagoGrupo = new \App\Models\PagoGrupo();
        $pago = $pagoGrupo->with([
            'pagos'
        ])->findOrFail($id);

        return view('vendedor.pagos.comprobante', compact('pago'));
    }

    /**
     * Muestra un comprobante completo por pedido con toda la traza de pagos asociados.
     *
     * @param int $pedidoId
     * @return \Illuminate\Http\Response
     */
    public function mostrarComprobantePedido($pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $cliente = OrderClient::select('RIF', 'NOMBRE')->where('RIF', $pedido->rif)->first();

        $pagosPedido = PagoPedido::with([
            'pago.tipo_pago',
            'pago.banco',
            'pago.pago_destino'
        ])
            ->where('pedido_id', $pedido->id)
            ->get()
            ->filter(function ($pp) {
                return !is_null($pp->pago);
            })
            ->sortBy(function ($pp) {
                return $pp->pago->fecha ?? $pp->pago->created_at;
            })
            ->values();

        $totalPagadoUsd = (float) $pagosPedido->sum('monto');
        $totalIvaBsAplicado = (float) $pagosPedido->sum('iva');
        $totalDescuentoUsd = (float) $pagosPedido->sum('descuento');

        return view('vendedor.pagos.comprobante_pedido', compact(
            'pedido',
            'cliente',
            'pagosPedido',
            'totalPagadoUsd',
            'totalIvaBsAplicado',
            'totalDescuentoUsd'
        ));
    }


    public function index(Request $request)
    {
        // Verificar si se recibieron los parÃ¡metros del formulario de mÃ©todo de pago
        $clienteRif = $request->input('rif');
        $clienteNombre = $request->input('cliente_nombre');
        $tipoPago = $request->input('tipo_pago');
        $total_bolivares = $request->input('total_bolivares');
        $total_iva = $request->input('total_iva');
        $total_descuento_pago = $request->input('total_descuento_pago');
        $total_retencion = $request->input('total_retencion');
        $saldo_iva_total = $request->input('saldo_iva_total', 0);
        $retencionPorcentajes = json_decode($request->input('retencion_porcentajes', '{}'), true);
        $retencionPorcentajes = is_array($retencionPorcentajes) ? $retencionPorcentajes : [];
        
        $total_ajustes_netos = $request->input('total_ajustes_netos', 0);
        $detallePedidos = $request->input('detalle_pedidos', '');
        
        // Obtener los detalles de ajustes de cada pedido seleccionado
        $detallesAjustes = [];
        if (!empty($detallePedidos)) {
            $detallesArray = json_decode($detallePedidos, true);
            if (is_array($detallesArray)) {
                foreach ($detallesArray as $detalle) {
                    $pedidoId = $detalle['pedido_id'];
                    // Obtener ajustes del pedido desde la tabla pedido_ajustes
                    $ajustesPedido = \Illuminate\Support\Facades\DB::connection('company')
                        ->table('pedido_ajustes')
                        ->where('pedido_id', $pedidoId)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    $detallesAjustes[$pedidoId] = $ajustesPedido;
                }
            }
        }
        
        // Obtener el total a pagar y la base sin ajustes (base_real viene desde la vista metodo).
        $total_pagar = (float) $request->input('total_pagar', 0);
        $base_sin_ajustes = (float) $request->input('base_real', 0);
        if ($base_sin_ajustes <= 0) {
            $base_sin_ajustes = $total_pagar - (float) $total_ajustes_netos;
        }

        if (!$clienteRif || !$tipoPago) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Datos de pago incompletos '.$clienteRif.' - '.$tipoPago);
        }

        // Store the payment type and IVA en divisa options in the session
        session([
            'pago_cliente.tipo_pago'       => $tipoPago,
            'pago_cliente.iva_en_divisa'    => $request->input('iva_en_divisa', 0),
            'pago_cliente.opcion_iva_divisa' => $request->input('opcion_iva_divisa', 'completo'),
        ]);
        $formaPago = null;
        if ($tipoPago == 'divisa_total') {
            // Obtener la descripción de la forma de pago para divisa_total
            $formaPago = OrderTpago::select('CPAGO', 'DPAGO')
                ->where('DPAGO', 'like', '%$%')
                ->first();
        } else {
            // Obtener la descripción de la forma de pago para otros tipos
            $formaPago = OrderTpago::select('CPAGO', 'DPAGO')
                ->first();
        }

        // Obtener los pedidos pendientes del cliente.
        // Usar saldos canónicos persistidos en pedidos para evitar falsos "sin saldo"
        // cuando existen pagos en revisión y/o descuentos ya reflejados en saldo_base.
        $pedidos = Pedido::on('company')
            ->where('rif', $clienteRif)
            ->where('estatus', '!=', 'CANCELADO')
            ->with(['pedido_detalle', 'deposito'])
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function ($pedido) {
                $saldoBase = (float) ($pedido->saldo_base ?? 0);
                $saldoAjustes = (float) ($pedido->saldo_ajustes ?? 0);
                $pedido->saldo_pendiente = round($saldoBase + $saldoAjustes, 2);

                return $pedido;
            });

        // Filtrar pedidos con cualquier saldo pendiente real.
        // Permitir abonos cuando exista saldo_base > 0 aunque haya ajustes negativos.
        $pedidosPendientes = $pedidos->filter(function ($pedido) {
            $saldoBase = (float) ($pedido->saldo_base ?? 0);
            $saldoIva = (float) ($pedido->saldo_iva_bs ?? 0);
            $saldoAjustes = (float) ($pedido->saldo_ajustes ?? 0);

            return $saldoBase > 0.001 || $saldoIva > 0.001 || $saldoAjustes > 0.001;
        });

        if ($pedidosPendientes->isEmpty()) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'No hay saldos pendientes para este cliente');
        }

        $bancos = null;
        if ($tipoPago == 'divisa_total') {
            // Filtrar tipos de pago que contengan el símbolo de dólar
            $tipos_pago = OrderTpago::where('DPAGO', 'like', '%$%')->get();
            $pago_destinos = (new PagoDestino)->where('nombre', 'like', '%$%')->orWhere('nombre', 'like', '%Zelle%')->get();
        } else {
            if ($tipoPago == 'bs') {
                $tipos_pago = OrderTpago::where('DPAGO', 'not like', '%$%')->get();
                $pago_destinos = (new PagoDestino)->where('nombre', 'not like', '%$%')->orWhere('nombre', 'not like', '%Zelle%')->get();
            } else {
                $tipos_pago = OrderTpago::all();
                $pago_destinos = (new PagoDestino)->get();
            }
            $bancos = (new Bank)->getData();
        }

        // Pasar los datos a la vista
        // Determinar el total a pagar según el tipo de pago
        $total_pagar_divisa_parcial = 0;
        if ($tipoPago === 'divisa_total') {
            $total_pagar = $request->total_pagar_divisa;
        } else {
            $total_pagar = $request->total_pagar;
            if ($tipoPago === 'divisa_parcial') {
                //dd($request);
                $total_pagar = $request->monto_divisa;
                $total_pagar_divisa_parcial = $request->total_bolivares_parcial;
            }
        }
        $tasa_bcv = $request->tasa_bcv;
        $monto_divisa = $request->monto_divisa;
        $pedidos_seleccionados = $request->pedidos;
        // Convertir el JSON de detalle_pedidos a array asociativo
        $detallePedidos = '';
        if ($request->filled('detalle_pedidos')) {
            $detallePedidos = $request->input('detalle_pedidos');
        }

        // Si es pago en bolívares, usar el total_bolivares y sumar ajustes netos convertidos a Bs.
        if ($tipoPago === 'bs' && $total_bolivares) {
            $totalBolivaresBase = (float) $total_bolivares;
            $tasaReferencia = (float) ($request->tasa_bcv ?: $request->input('tasa_cambio_request', 0));
            $ajustesNetosUsd = (float) $total_ajustes_netos;
            $ajustesNetosBs = ($tasaReferencia > 0)
                ? ($ajustesNetosUsd * $tasaReferencia)
                : 0;

            $total_pagar = $totalBolivaresBase + $ajustesNetosBs;
            $total_bolivares = $total_pagar;
        }

        // Obtener pedidos seleccionados con desglose de saldos para la distribución
        $pedidosIdsArray = [];
        if ($pedidos_seleccionados) {
            if (is_array($pedidos_seleccionados)) {
                $pedidosIdsArray = $pedidos_seleccionados;
            } else {
                $pedidosIdsArray = array_filter(array_map('trim', explode(',', $pedidos_seleccionados)));
            }
        }

        $pedidosDistribucion = Pedido::on('company')
            ->whereIn('id', $pedidosIdsArray)
            ->where('rif', $clienteRif)
            ->with(['pedido_factura'])
            ->orderBy('fecha', 'asc')
            ->get()
            ->map(function ($p) use ($tasa_bcv, $retencionPorcentajes) {
                $tasa = (float) $tasa_bcv > 0 ? (float) $tasa_bcv : 1;
                $saldoBase = (float) ($p->saldo_base ?? 0);
                $saldoIvaBs = (float) ($p->saldo_iva_bs ?? 0);
                $ajustesNeto = PedidoAjuste::netoPendiente((int) $p->id);
                $saldoAjustes = abs($ajustesNeto) > 0.001 ? $ajustesNeto : (float) ($p->saldo_ajustes ?? 0);

                $porcentajeRetencion = array_key_exists($p->id, $retencionPorcentajes)
                    ? (float) $retencionPorcentajes[$p->id]
                    : null;
                $retencionPend = $this->retencionPendiente($p, $porcentajeRetencion);
                $ivaNetoBs = max($saldoIvaBs - $retencionPend, 0);

                $p->saldo_base_num = $saldoBase;
                $p->saldo_base_bs = round($saldoBase * $tasa, 2);
                $p->saldo_iva_bs_num = $saldoIvaBs;
                $p->iva_neto_bs_num = $ivaNetoBs;
                $p->retencion_bs_num = $retencionPend;
                $p->saldo_ajustes_num = $saldoAjustes;
                $p->saldo_ajustes_bs = round($saldoAjustes * $tasa, 2);
                $p->fecha_formateada = $p->fecha ? Carbon::parse($p->fecha)->format('d/m/Y') : 'N/A';
                $p->factura_numero = $p->pedido_factura->factura ?? null;

                return $p;
            });

        $data = [
            'pedidosPendientes' => $pedidosPendientes,
            'pedidosDistribucion' => $pedidosDistribucion,
            'tipos_pago' => $tipos_pago,
            'bancos' => $bancos,
            'pago_destinos' => $pago_destinos,
            'total_pagar' => $total_pagar,
            'tasa_bcv' => $tasa_bcv,
            'monto_divisa' => $monto_divisa,
            'pedidos_seleccionados' => $pedidos_seleccionados,
            'total_bolivares' => $total_bolivares,
            'tipo_pago_actual' => $tipoPago,
            'clienteRif' => $clienteRif,
            'clienteNombre' => $clienteNombre,
            'total_iva' => $total_iva,
            'total_descuento_pago' => $total_descuento_pago,
            'total_retencion' => $total_retencion,
            'retencionPorcentajes' => $retencionPorcentajes,
            'saldo_iva_total' => $saldo_iva_total,
            'detallePedidos' => $detallePedidos,
            'total_pagar_divisa_parcial' => $total_pagar_divisa_parcial,
            'detallesAjustes' => $detallesAjustes,
            'total_ajustes_netos' => $total_ajustes_netos,
            'base_sin_ajustes' => $base_sin_ajustes,
            'opcion_iva' => session('pago_cliente.opcion_iva', $request->input('pago_iva_opcion', 'retencion')),
        ];

        return view('vendedor.pagos.index', $data);
    }

    public function show($pedidoId)
    {
        $user = auth()->user();

        // Verificar que el pedido pertenece al usuario actual
        $pedido = Pedido::on('company')
            ->where('user_id', $user->id)
            ->with(['pedido_detalle', 'deposito'])
            ->findOrFail($pedidoId);

        // Calcular el saldo pendiente
        $montoTotal = $pedido->pedido_detalle->sum(function ($detalle) {
            return $detalle->precio * $detalle->cantidad;
        });

        // Calcular el saldo pendiente
        $montoTotal = $pedido->pedido_detalle->sum(function ($detalle) {
            return $detalle->precio * $detalle->cantidad;
        });

        // Verificar si aplica descuento por pronto pago
        $montoDescuento = 0;
        $descuento = null;
        if ($pedido->fecha_despacho) {
            $fechaDespacho = Carbon::parse($pedido->fecha_despacho);
            $hoy = Carbon::now();
            $descuento = Descuento::first();
            if ($descuento && $fechaDespacho->diffInDays($hoy) <= $descuento->dias_despacho) {
                $montoDescuento = $montoTotal * ($descuento->porcentaje / 100);
            }
        }

        $pagosRealizados = Pago::on('company')
            ->whereHas('pago_pedidos', function ($query) use ($pedido) {
                $query->where('pedido_id', $pedido->id);
            })
            ->sum('monto');

        $saldoPendiente = round($montoTotal - $montoDescuento - $pagosRealizados, 2);

        return view('vendedor.pagos.show', compact('pedido', 'saldoPendiente', 'tipos_pago', 'bancos', 'pago_destinos'));
    }

    public function print()
    {
        $user = auth()->user();
        $pedidos = Pedido::on('company')
            ->where('user_id', $user->id)
            ->whereNotIn('estatus', ['PAGADO', 'CANCELADO'])
            ->with(['pedido_detalle', 'deposito'])
            ->get();

        $pedidosConSaldo = $pedidos->map(function ($pedido) {
            $montoTotal = $pedido->pedido_detalle->sum(function ($detalle) {
                return $detalle->precio * $detalle->cantidad;
            });
            $pagosRealizados = Pago::on('company')
                ->whereHas('pago_pedidos', function ($query) use ($pedido) {
                    $query->where('pedido_id', $pedido->id);
                })
                ->sum('monto');
            $pedido->saldo_pendiente = $montoTotal - $pagosRealizados;
            return $pedido;
        });

        $pedidosPendientes = $pedidosConSaldo->filter(function ($pedido) {
            return $pedido->saldo_pendiente > 0;
        });

        return view('vendedor.pagos.print', compact('pedidosPendientes'));
    }

    public function getData()
    {
        $user = auth()->user();
        $pedidos = Pedido::on('company')
            ->where('user_id', $user->id)
            ->whereNotIn('estatus', ['PAGADO', 'CANCELADO'])
            ->with(['pedido_detalle', 'deposito'])
            ->get();

        $pedidosConSaldo = $pedidos->map(function ($pedido) {
            $montoTotal = $pedido->pedido_detalle->sum(function ($detalle) {
                return $detalle->precio * $detalle->cantidad;
            });
            $pagosRealizados = Pago::on('company')
                ->whereHas('pago_pedidos', function ($query) use ($pedido) {
                    $query->where('pedido_id', $pedido->id);
                })
                ->sum('monto');
            $pedido->saldo_pendiente = $montoTotal - $pagosRealizados;
            return $pedido;
        });

        $pedidosPendientes = $pedidosConSaldo->filter(function ($pedido) {
            return $pedido->saldo_pendiente > 0;
        });

        return datatables()->of($pedidosPendientes)
            ->addColumn('fecha', function ($pedido) {
                return $pedido->fecha->format('d/m/Y H:i');
            })
            ->addColumn('descripcion', function ($pedido) {
                return $pedido->descripcion;
            })
            ->addColumn('saldo_pendiente', function ($pedido) {
                return $pedido->saldo_pendiente;
            })
            ->addColumn('action', function ($pedido) {
                return '<a href="' . route('vendedores.pagos.show', $pedido->id) . '" class="btn btn-primary btn-sm">Registrar Pago</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function pagosList(Request $request)
    {
        $user = auth()->user();

        if (!$user->vendedor) {
            abort(403, 'No tienes acceso a esta funcionalidad');
        }

        $pagos = Pago::on('company')
            ->where('vendedor_id', $user->vendedor->id)
            ->with(['vendedor', 'pago_pedidos.pedido'])
            ->get();

        return datatables()->of($pagos)
            ->addColumn('fecha', function ($pago) {
                return Carbon::parse($pago->created_at)->format('d/m/Y');
            })
            ->addColumn('descripcion', function ($pago) {
                return $pago->descripcion ?? '';
            })
            ->addColumn('monto', function ($pago) {
                $currency = ($pago->moneda_pago === 'bs' || $pago->monto_bs > 0) ? 'Bs. ' : '$';
                return $currency . number_format($pago->moneda_pago === 'bs' ? $pago->monto_bs : $pago->monto, 2, ',', '.');
            })
            ->addColumn('estatus', function ($pago) {
                $badgeClass = 'badge-secondary';
                if ($pago->estatus === 'PENDIENTE') {
                    $badgeClass = 'badge-warning';
                } else if ($pago->estatus === 'APROBADO') {
                    $badgeClass = 'badge-success';
                } else if ($pago->estatus === 'RECHAZADO') {
                    $badgeClass = 'badge-danger';
                }
                return '<span class="badge ' . $badgeClass . '">' . $pago->estatus . '</span>';
            })
            ->addColumn('action', function ($pago) {
                $rowClass = $pago->estatus === 'PENDIENTE' ? 'espera-aprobacion' : '';
                $verDetalleBtn = '<button type="button" class="btn btn-sm btn-info mr-2 ver-detalle" data-id="' . $pago->id . '">
                                    <i class="fas fa-eye"></i> Ver Detalle
                                </button>';
                $eliminarBtn = $pago->estatus === 'PENDIENTE' ?
                    '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarPago(' . $pago->id . ')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>' : '';
                return '<div class="btn-group ' . $rowClass . '" role="group" aria-label="Acciones">' .
                    $verDetalleBtn . $eliminarBtn . '</div>';
            })
            ->rawColumns(['estatus', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // Verificar si hay datos de pago en la sesión
        if (!session('pago_cliente')) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Sesión de pago no encontrada. Por favor, inicie el proceso de pago nuevamente.');
        }

        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'monto' => 'required|numeric|min:0.01',
            'tipo_pago' => 'required|in:efectivo,transferencia',
            'pago_destino_id' => 'required|exists:pago_destinos,id',
            'rate' => 'required|numeric|min:0.01',
            'monto_bs' => 'required|numeric|min:0.01',
            'comprobante' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = auth()->user();
        $pagoData = session('pago_cliente');

        DB::beginTransaction();

        try {
            // Verificar que el pedido pertenece al vendedor
            $pedido = Pedido::on('company')
                ->where('id', $validated['pedido_id'])
                ->where('user_id', $user->id)
                ->with(['pedido_detalle', 'deposito'])
                ->firstOrFail();

            // Calcular el saldo pendiente
            $montoTotal = $pedido->pedido_detalle->sum(function ($detalle) {
                return $detalle->precio * $detalle->cantidad;
            });

            // Verificar si aplica descuento por pronto pago
            $montoDescuento = 0;
            if ($pedido->fecha_despacho) {
                $fechaDespacho = Carbon::parse($pedido->fecha_despacho);
                $hoy = Carbon::now();
                $descuento = Descuento::first();
                if ($descuento && $fechaDespacho->diffInDays($hoy) <= $descuento->dias_despacho) {
                    $montoDescuento = $montoTotal * ($descuento->porcentaje / 100);
                }
            }

            // Calcular pagos realizados anteriormente
            $pagosRealizados = PagoPedido::on('company')
                ->where('pedido_id', $pedido->id)
                ->sum('monto');

            $saldoPendiente = round($montoTotal - $montoDescuento - $pagosRealizados, 2);

            // Validar que el monto no exceda el saldo pendiente
            if ($validated['monto'] > $saldoPendiente) {
                throw new \Exception('El monto ingresado excede el saldo pendiente del pedido.');
            }

            // Crear el pago
            $pago = new Pago();
            $pago->fill([
                'monto' => $validated['monto'],
                'monto_bs' => $validated['monto_bs'],
                'tasa_cambio' => $validated['rate'],
                'descripcion' => 'Pago de pedido del cliente',
                'tipo_pago' => $pagoData['metodo_pago'] === 'efectivo' ? 'EFECTIVO' : 'TRANSFERENCIA',
                'pago_destino_id' => $validated['pago_destino_id'],
                'user_id' => $user->id,
                'estatus' => 'PENDIENTE',
                'cliente_id' => $pagoData['cliente_id'],
                'banco_id' => $pagoData['banco_id'] ?? null,
                'referencia' => $pagoData['referencia'] ?? null,
                'fecha_pago' => $pagoData['fecha'] ?? now(),
            ]);

            // Manejar el comprobante si es transferencia
            if ($request->hasFile('comprobante')) {
                $path = $this->guardarArchivoPublico($request->file('comprobante'), 'comprobantes');
                $pago->comprobante = $path;
            }

            $pago->save();

            // Registrar el pago del pedido
            PagoPedido::create([
                'pago_id' => $pago->id,
                'pedido_id' => $pedido->id,
                'monto' => $validated['monto']
            ]);

            // Actualizar el estatus del pedido si se pagó en su totalidad
            $totalPagado = PagoPedido::where('pedido_id', $pedido->id)->sum('monto');
            $totalPedido = $pedido->pedido_detalle->sum(function ($detalle) {
                return $detalle->precio * $detalle->cantidad;
            });

            $totalPedidoConDescuento = $totalPedido - $montoDescuento;

            if ($totalPagado >= ($totalPedidoConDescuento - 0.01)) { // Tolerancia de 0.01 para errores de redondeo
                $pedido->estatus = 'PAGADO';
                $pedido->save();
                PedidoAjuste::marcarPagados((int) $pedido->id);
            }

            DB::commit();

            // Limpiar la sesión de pago
            session()->forget('pago_cliente');

            return redirect()->route('vendedores.pagos.clientes')
                ->with('success', 'Pago registrado exitosamente. EstÃ¡ pendiente de aprobación.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al procesar pago: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeMultipleOriginal(Request $request)
    {
        // Verificar si hay datos de pago en la sesión
        if (!session('pago_cliente')) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Sesión de pago no encontrada. Por favor, inicie el proceso de pago nuevamente.');
        }
        // Validar los datos principales del pago múltiple
        /*
        $validated = $request->validate([
            //'pago_destino_id' => 'required|exists:pago_destinos,id',
            'rate' => 'required|numeric|min:0.01',
            'monto_bs' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            ]);
            */
        // Decodificar pedidos_seleccionados para obtener los IDs
        $pedidosIds = [];
        if ($request->filled('pedidos_seleccionados')) {
            $pedidosSeleccionados = $request->input('pedidos_seleccionados');
            // Eliminar entidades HTML y comillas
            $pedidosSeleccionados = str_replace(['&quot;', '"'], '', $pedidosSeleccionados);
            // Separar por coma si hay varios
            $pedidosIds = array_filter(explode(',', $pedidosSeleccionados));
        }
        $pagos = [];
        if ($request->filled('pagos_json')) {
            $pagosJson = $request->input('pagos_json');
            // Limpiar entidades HTML y espacios
            $pagosJson = html_entity_decode(trim($pagosJson));
            // Si inicia con [, es un array JSON válido
            if (strpos($pagosJson, '[') === 0) {
                $pagos = json_decode($pagosJson, true);
            } else {
                // Si no, intentar decodificar como objeto o lista separada por comas
                $pagos = [];
            }
            // Si no es array, forzar array vacío
            if (!is_array($pagos)) {
                $pagos = [];
            }
        }

        /*
        // Validar que haya al menos un pedido y monto
        if (empty($pedidosMontos)) {
            return redirect()->back()->with('error', 'Debe seleccionar al menos un pedido y monto válido.')->withInput();
        }
        
        // Calcular el monto total
        $validated['monto_total'] = collect($pedidosMontos)->sum('monto');
        $validated['pedidos'] = $pedidosMontos;
        */

        $user = auth()->user();
        $pagoData = session('pago_cliente');
        $tasa_cambio = $request->input('rate_json');
        $moneda_pago = $request->input('moneda_pago');
        $total_iva = $request->input('total_iva');
        $total_descuento_pago = $request->input('total_descuento_pago');
        $total_retencion = $request->input('total_retencion');
        $pagar_monto_iva = $request->boolean('pagar_monto_iva');

        DB::beginTransaction();

        try {
            /*
            // Validar montos antes de procesar
            $montoTotalPedidos = collect($validated['pedidos'])->sum('monto');
            
            if (abs($montoTotalPedidos - $validated['monto_total']) > 0.01) {
                throw new \Exception('El monto total no coincide con la suma de los montos de los pedidos.');
            }
            
            if (abs($validated['monto_bs'] - ($validated['monto_total'] * $validated['rate'])) > 0.01) {
                throw new \Exception('El monto en bolÃ­vares no coincide con la tasa de cambio proporcionada.');
            }
            */
            // Obtener los pedidos con sus detalles y cliente
            // Obtener los IDs de los pedidos seleccionados desde el array de entrada
            /*
            $pedidosIds = [];
            if (isset($validated['pedidos'])) {
                // Si es un array de arrays con 'id'
                $pedidosIds = array_column($validated['pedidos'], 'id');
            } elseif ($request->has('pedidos_seleccionados')) {
                // Si viene como string tipo JSON o lista separada por comas
                $pedidosSeleccionados = $request->input('pedidos_seleccionados');
                if (is_string($pedidosSeleccionados)) {
                    // Limpiar comillas y decodificar si es necesario
                    $pedidosSeleccionados = trim($pedidosSeleccionados, '"');
                    if (strpos($pedidosSeleccionados, '[') !== false) {
                        $pedidosIds = json_decode(html_entity_decode($pedidosSeleccionados), true);
                    } else {
                        $pedidosIds = explode(',', str_replace(['&quot;', '"'], '', $pedidosSeleccionados));
                    }
                } elseif (is_array($pedidosSeleccionados)) {
                    $pedidosIds = $pedidosSeleccionados;
                }
            }
            */

            // Obtener el seller_id buscando por email del usuario logueado
            $seller_id = (new Vendedor)
                ->where('email', $user->email)
                ->value('id');

            // Crear el grupo de pago antes de registrar los pagos individuales
            $pago_grupo = new \App\Models\PagoGrupo();
            $pago_grupo->moneda_pago = $moneda_pago ?? 'No Indicado';
            $pago_grupo->fecha_pago = $pagos[0]['fecha_pago'] ?? now();
            $pago_grupo->user_id = $user->id;
            $pago_grupo->seller_id = $seller_id;
            $pago_grupo->save();

            $pedidos = Pedido::whereIn('id', $pedidosIds)
                ->with(['pedido_detalle'])
                ->get();

            // Verificar que todos los pedidos pertenecen al cliente
            /*
            foreach ($pedidos as $pedido) {
                if ($pedido->rif !== $pagoData['rif']) {
                    //throw new \Exception('Uno o más pedidos no pertenecen al cliente seleccionado');
                }
            }
                */
            $monto = 0;
            foreach ($pagos as $pagoItem) {
                $monto += $pagoItem['monto'];
            }

            foreach ($pagos as $pago) {
                $pagoModel = new Pago();

                $pagoModel->fill([
                    'pago_grupo_id' => $pago_grupo->id,
                    'monto' => $pago['monto'],
                    'monto_bs' => round($pago['monto'] * $tasa_cambio, 2),
                    'rate' => $tasa_cambio,
                    'descripcion' => trim((string) ($pago['descripcion'] ?? '')) !== ''
                        ? trim((string) ($pago['descripcion'] ?? ''))
                        : 'Pago de pedidos del cliente ' . ($pedidos->first()->descripcion ?? '') . ' - SIN OBSERVACIONES',
                    'tpago_id' => $pago['tpago_id'],
                    'banco_codigo' => $pago['banco_codigo'],
                    'pago_destino_id' => $pago['pago_destino_id'],
                    'user_id' => $user->id,
                    'estatus' => 'EN REVISION',
                    'cliente_id' => $pagoData['cliente_id'] ?? null,
                    'referencia' => $pago['referencia'],
                    'fecha' => $pago['fecha_pago'] ?? now(),
                    'seller_id' => $seller_id,
                    'moneda_pago' => $moneda_pago ?? 'No Indicado',
                ]);

                // Manejar los comprobantes si existen
                if ($request->hasFile('comprobantes')) {
                    $comprobantes = [];
                    foreach ($request->file('comprobantes') as $file) {
                        if ($file->isValid()) {
                            $path = $this->guardarArchivoPublico($file, 'comprobantes');
                            $comprobantes[] = [
                                'nombre' => $file->getClientOriginalName(),
                                'ruta' => $path,
                                'tipo' => $file->getClientMimeType(),
                                'tamano' => $file->getSize(),
                            ];
                        }
                    }
                    if (!empty($comprobantes)) {
                        $pagoModel->comprobante = json_encode($comprobantes);
                    }
                }
                //$pedido->estatus = 'PAGADO';
                $pagoModel->save();


                // Procesar cada pedido
                foreach ($pedidos as $pedido) {
                    // Calcular el total del pedido incluyendo IVA
                    // Obtener todos los pagos realizados en este grupo
                    $pagosDelGrupo = Pago::where('pago_grupo_id', $pago_grupo->id)->get();

                    /*
                    // Calcular el saldo pendiente de cada pedido
                    $pedidosConSaldo = $pedidos->map(function($pedido) {
                        $totalPedido = $pedido->pedido_detalle->sum(function($detalle) {
                            return ($detalle->cantidad * $detalle->precio_dolar) * (1 + ($detalle->iva / 100));
                        });
                        $pagadoPedido = PagoPedido::where('pedido_id', $pedido->id)->sum('monto');
                        $pedido->saldo_pendiente = $totalPedido - $pagadoPedido;
                        return $pedido;
                    });
                    */
                    $pedidosConSaldo = $pedidos;


                    // Distribuir los montos de los pagos entre los pedidos
                    foreach ($pedidosConSaldo as $pedido) {
                        Pedido::where('id', $pedido->id)->update(['estatus' => 'EN REVISION']);
                        /*
                        $saldoPendiente = $pedido->saldo_pendiente;
                        if ($saldoPendiente <= 0.01) {
                            \Illuminate\Support\Facades\Log::error('Pedido ya pagado: ' );
                            \Illuminate\Support\Facades\Log::error($pedido );
                            continue; // Pedido ya pagado
                        }
                        */
                        foreach ($pagosDelGrupo as $pago) {

                            // Calcular cuánto queda por asignar de este pago
                            $montoAsignado = PagoPedido::where('pago_id', $pago->id)->sum('monto');
                            $montoDisponible = $pago->monto - $montoAsignado;

                            if ($montoDisponible <= 0.01) {
                                \Illuminate\Support\Facades\Log::error('Este pago ya fue distribuido completamente: ' . $montoDisponible);
                                \Illuminate\Support\Facades\Log::error($pedido);
                                continue; // Este pago ya fue distribuido completamente
                            }

                            $montoParaAsignar = min($saldoPendiente, $montoDisponible);
                            if ($montoParaAsignar <= 0) {
                                \Illuminate\Support\Facades\Log::error('$montoParaAsignar <= 0 :' . $montoParaAsignar);
                                \Illuminate\Support\Facades\Log::error($pedido);
                                continue;
                            }

                            // Registrar el pago del pedido
                            $pagoPedido = new PagoPedido();
                            $pagoPedido->fill([
                                'pago_id' => $pago->id,
                                'pedido_id' => $pedido->id,
                                'iva' => $total_iva,
                                'retencion' => $total_retencion,
                                'descuento' => $total_descuento_pago,
                                'monto' => $montoParaAsignar,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $pagoPedido->save();

                            $saldoPendiente -= $montoParaAsignar;

                            if ($saldoPendiente <= 0.01) {
                                break; // Pedido cubierto
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Limpiar la sesión de pago
            session()->forget('pago_cliente');

            return redirect()->route('vendedores.pagos.mostrarComprobante', $pago_grupo->id)
                ->with('success', 'Pago registrado exitosamente. Pendiente de aprobación.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al procesar pago múltiple: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function storeMultiple(Request $request)
    {
        // Verificar si hay datos de pago en la sesión
        if (!session('pago_cliente')) {
            return redirect()->route('vendedores.pagos.clientes')
                ->with('error', 'Sesión de pago no encontrada. Por favor, inicie el proceso de pago nuevamente.');
        }

        // Validar los datos del formulario
        /*
        $validated = $request->validate([
            'pago_destino_id' => 'required|exists:company.pago_destinos,id',
            'monto_bs' => 'required|numeric|min:0.01',
        ], [
            'pago_destino_id.required' => 'Debe seleccionar un Banco Receptor.',
            'pago_destino_id.exists' => 'El banco receptor seleccionado no es válido.',
            'monto_bs.required' => 'El monto del pago es requerido.',
            'monto_bs.numeric' => 'El monto debe ser un valor numérico.',
            'monto_bs.min' => 'El monto debe ser mayor a 0.',
        ]);
        */

        // Convertir el JSON de detallePedidos a un array asociativo (necesario para calcular ajustes)
        $detallePedidos = [];
        if ($request->filled('detallePedidos')) {
            $detallePedidosRaw = $request->input('detallePedidos');
            // Si viene como string JSON, decodificarlo
            $detallePedidos = json_decode($detallePedidosRaw, true);
            if (!is_array($detallePedidos)) {
                $detallePedidos = [];
            }
        }

        // Calcular el total de ajustes netos incluidos en el pago
        $totalAjustesNetos = (float) $request->input('total_ajustes_netos', 0);
        $moneda_pago_temp = $request->input('moneda_pago', '');
        $tasa_cambio_temp = (float) $request->input('rate_json', 1);
        $totalAjustesNetosMonto = ($moneda_pago_temp === 'Bolívares' && $tasa_cambio_temp > 0)
            ? $totalAjustesNetos * $tasa_cambio_temp
            : $totalAjustesNetos;

        /*
        // Validar que el monto no exceda el total a pagar (incluye ajustes)
        $pagoData = session('pago_cliente');
        $totalAPagar = ($pagoData['monto'] ?? 0) + $totalAjustesNetosMonto;
        $montoIngresado = (float) $request->input('monto_bs');

        if ($totalAPagar > 0.01 && $montoIngresado > $totalAPagar + 0.01) {
            return redirect()->back()
                ->withInput()
                ->with('error', "El monto ingresado ({$montoIngresado}) excede el total a pagar ({$totalAPagar}).");
        }
        */

        // Decodificar pedidos_seleccionados para obtener los IDs
        $pedidosIds = [];
        if ($request->filled('pedidos_seleccionados')) {
            $pedidosSeleccionados = $request->input('pedidos_seleccionados');
            // Eliminar entidades HTML y comillas
            $pedidosSeleccionados = str_replace(['&quot;', '"'], '', $pedidosSeleccionados);
            // Separar por coma si hay varios
            $pedidosIds = array_filter(explode(',', $pedidosSeleccionados));
        }
        $pagos = [];
        if ($request->filled('pagos_json')) {
            $pagosJson = $request->input('pagos_json');
            // Limpiar entidades HTML y espacios
            $pagosJson = html_entity_decode(trim($pagosJson));
            // Si inicia con [, es un array JSON válido
            if (strpos($pagosJson, '[') === 0) {
                $pagos = json_decode($pagosJson, true);
            } else {
                // Si no, intentar decodificar como objeto o lista separada por comas
                $pagos = [];
            }
            // Si no es array, forzar array vacío
            if (!is_array($pagos)) {
                $pagos = [];
            }
        }

        $pedidosIds = array_values(array_unique(array_map('intval', $pedidosIds)));
        if (empty($pedidosIds) || empty($detallePedidos) || empty($pagos)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Debe seleccionar al menos un pedido y registrar al menos un abono válido.');
        }

        foreach ($pagos as $pago) {
            if (!is_array($pago) || (float) ($pago['monto'] ?? 0) <= 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Todos los abonos deben tener un monto mayor a cero.');
            }
        }

        $user = auth()->user();
        $pagoData = session('pago_cliente');
        $tasa_cambio = $request->input('rate_json');
        $moneda_pago = $request->input('moneda_pago');
        $total_descuento_pago = $request->input('total_descuento_pago');
        $total_retencion = $request->input('total_retencion');
        $retencionPorcentajes = json_decode($request->input('retencion_porcentajes', '{}'), true);
        $retencionPorcentajes = is_array($retencionPorcentajes) ? $retencionPorcentajes : [];

        // Capturar la distribución de saldos seleccionada por el usuario (si fue enviada)
        $distribucionRaw = $request->input('distribucion_saldos');
        $distribucionSaldos = [];
        if (is_string($distribucionRaw)) {
            $distribucionSaldos = json_decode($distribucionRaw, true) ?: [];
        } elseif (is_array($distribucionRaw)) {
            $distribucionSaldos = $distribucionRaw;
        }

        // Capturar la opción de IVA seleccionada (para pagos en Bolívares)
        // Default 'retencion': mismo default que usa la pantalla de distribución de saldos
        // para mostrar el "Saldo IVA" (ver index()/index.blade.php), de forma que lo mostrado
        // al vendedor coincida siempre con lo que realmente se cobra.
        $opcion_iva = $request->input('pago_iva_opcion', 'retencion');
        $porcentajeRetencionPedido = function (Pedido $pedido) use ($retencionPorcentajes): ?float {
            return array_key_exists($pedido->id, $retencionPorcentajes)
                ? (float) $retencionPorcentajes[$pedido->id]
                : null;
        };

        // IVA en divisa: el vendedor elige pagar saldo_iva_bs convertido a USD
        $iva_en_divisa     = $request->boolean('iva_en_divisa', false);
        $opcion_iva_divisa = $request->input('opcion_iva_divisa', 'retencion'); // 'completo' o 'retencion'

        DB::beginTransaction();

        try {
            // Obtener el seller_id buscando por email del usuario logueado
            $seller_id = (new Vendedor)
                ->where('email', $user->email)
                ->value('id');

            // Crear el grupo de pago antes de registrar los pagos individuales
            $pago_grupo = new \App\Models\PagoGrupo();
            $pago_grupo->moneda_pago = $moneda_pago ?? 'No Indicado';
            $pago_grupo->fecha_pago = $pagos[0]['fecha_pago'] ?? now();
            $pago_grupo->user_id = $user->id;
            $pago_grupo->seller_id = $seller_id;
            $pago_grupo->distribucion_saldos = !empty($distribucionSaldos)
                ? (is_string($distribucionRaw) ? $distribucionRaw : json_encode($distribucionSaldos))
                : null;
            $pago_grupo->save();

            $pedidos = Pedido::whereIn('id', $pedidosIds)
                ->with(['pedido_detalle'])
                ->get();

            if ($pedidos->count() !== count($pedidosIds)) {
                throw new \RuntimeException('Uno o más pedidos seleccionados no existen o ya no están disponibles.');
            }

            $ordenPedidos = array_flip($pedidosIds);
            $pedidos = $pedidos
                ->sortBy(fn ($pedido) => $ordenPedidos[(int) $pedido->id])
                ->values();

            $total = 0;
            foreach ($pagos as $pago) {
                $pagoModel = new Pago();
                $total += $pago['monto'];
                if ($moneda_pago == 'Bolívares') {
                    $pagoModel->fill([
                        'pago_grupo_id' => $pago_grupo->id,
                        'monto' => round($pago['monto'] / $tasa_cambio, 2),
                        'monto_bs' => $pago['monto'],
                        'rate' => $tasa_cambio,
                        'descripcion' => trim((string) ($pago['descripcion'] ?? '')) !== ''
                            ? trim((string) ($pago['descripcion'] ?? ''))
                            : 'Pago de pedidos del cliente ' . ($pedidos->first()->descripcion ?? ''),
                        'tpago_id' => $pago['tpago_id'],
                        'banco_codigo' => $pago['banco_codigo'],
                        'pago_destino_id' => $pago['pago_destino_id'],
                        'user_id' => $user->id,
                        'estatus' => 'EN REVISION',
                        'cliente_id' => $pagoData['cliente_id'] ?? null,
                        'referencia' => $pago['referencia'],
                        'fecha' => $pago['fecha_pago'] ?? now(),
                        'seller_id' => $seller_id,
                        'moneda_pago' => $moneda_pago ?? 'No Indicado',
                    ]);
                } else {
                    $pagoModel->fill([
                        'pago_grupo_id' => $pago_grupo->id,
                        'monto' => $pago['monto'],
                        'monto_bs' => round($pago['monto'] * $tasa_cambio, 2),
                        'rate' => $tasa_cambio,
                        'descripcion' => trim((string) ($pago['descripcion'] ?? '')) !== ''
                            ? trim((string) ($pago['descripcion'] ?? ''))
                            : 'Pago de pedidos del cliente ' . ($pedidos->first()->descripcion ?? ''),
                        'tpago_id' => $pago['tpago_id'],
                        'banco_codigo' => $pago['banco_codigo'],
                        'pago_destino_id' => $pago['pago_destino_id'],
                        'user_id' => $user->id,
                        'estatus' => 'EN REVISION',
                        'cliente_id' => $pagoData['cliente_id'] ?? null,
                        'referencia' => $pago['referencia'],
                        'fecha' => $pago['fecha_pago'] ?? now(),
                        'seller_id' => $seller_id,
                        'moneda_pago' => $moneda_pago ?? 'No Indicado',
                    ]);
                }

                $pagoModel->save();
            }

            // Guardar archivos comprobantes en pago_archivos vinculados al pago_grupo
            if ($request->hasFile('comprobantes')) {
                foreach ($request->file('comprobantes') as $file) {
                    if ($file->isValid()) {
                        $path = $this->uploadComprobante($file);
                        PagoArchivo::create([
                            'pago_grupo_id'    => $pago_grupo->id,
                            'nombre_original'  => $file->getClientOriginalName(),
                            'ruta'             => $path,
                            'tipo_mime'        => $file->getClientMimeType(),
                            'tamano'           => $file->getSize(),
                        ]);
                    }
                }
            }

            $pagos_finales = Pago::where('pago_grupo_id', $pago_grupo->id)->get();
            ///$esPagoBolivaresConIva = $pagar_monto_iva && $moneda_pago == 'Bolívares' && (float) $tasa_cambio > 0;
            $esPagoBolivaresConIva = $moneda_pago == 'Bolívares' && (float) $tasa_cambio > 0;

            // Rastrear pedidos con retención pendiente para notificaciones
            $pedidosConRetencionPendiente = [];

            // Recorrer los pagos y distribuir entre los pedidos
            foreach ($pagos_finales as $pago) {
                \Illuminate\Support\Facades\Log::info('Procesando pago: ');
                \Illuminate\Support\Facades\Log::info($pago);

                // Calcular cuánto queda disponible en este pago inicialmente
                if ($esPagoBolivaresConIva) {
                    $montoAsignadoPagoBs = (float) PagoPedido::where('pago_id', $pago->id)
                        ->selectRaw('COALESCE(SUM((monto * ?) + iva + (COALESCE(ajustes_monto, 0) * ?)), 0) as total_asignado_bs', [(float) $tasa_cambio, (float) $tasa_cambio])
                        ->value('total_asignado_bs');
                    $montoDisponiblePago = max((float) $pago->monto_bs - $montoAsignadoPagoBs, 0);
                } else {
                    $montoAsignadoPago = (float) PagoPedido::where('pago_id', $pago->id)
                        ->selectRaw(
                            'COALESCE(SUM(monto + COALESCE(ajustes_monto,0) + CASE WHEN ? > 0 THEN COALESCE(iva,0)/? ELSE 0 END), 0) as total_usd',
                            [(float) $tasa_cambio, (float) $tasa_cambio]
                        )
                        ->value('total_usd');
                    $montoDisponiblePago = max((float) $pago->monto - $montoAsignadoPago, 0);
                }

                \Illuminate\Support\Facades\Log::info('Monto disponible en pago: ' . $montoDisponiblePago);

                if ($montoDisponiblePago <= 0.01) {
                    continue; // Este pago ya está completamente asignado
                }

                // PRE-PASO (pagos Bolívares): reservar el IVA de todos los pedidos ANTES de asignar base.
                // Garantiza que saldo_iva_bs tiene prioridad GLOBAL sobre saldo_base en toda la distribución.
                $ivaReservadoPorPedido = [];
                $ivaReservadoSufijo    = [];
                if ($esPagoBolivaresConIva) {
                    $saldosIva = [];
                    foreach ($pedidos as $pedIva) {
                        $detPedIva = collect($detallePedidos)->firstWhere('pedido_id', $pedIva->id);
                        if (!$detPedIva) {
                            $saldosIva[$pedIva->id] = 0;
                            continue;
                        }
                        $opcionesPedIva = $distribucionSaldos[$pedIva->id] ?? null;
                        $incluyeIvaPed = $opcionesPedIva !== null ? !empty($opcionesPedIva['iva']) : true;
                        if (!$incluyeIvaPed) {
                            $saldosIva[$pedIva->id] = 0;
                            continue;
                        }

                        $pedActIva = Pedido::select('id', 'saldo_iva_bs', 'porc_retencion', 'iva_bs')->find($pedIva->id);
                        $saldoIvaPedIva = $pedActIva ? (float) ($pedActIva->saldo_iva_bs ?? 0) : 0;
                        if ($opcion_iva === 'retencion' && $pedActIva) {
                            $saldoIvaPedIva = $this->netoIvaPendiente(
                                $pedActIva,
                                $porcentajeRetencionPedido($pedActIva)
                            );
                        }
                        $saldosIva[$pedIva->id] = $saldoIvaPedIva;
                    }

                    [$ivaReservadoPorPedido, $ivaReservadoSufijo] = $this->reservarIvaPrioritario(
                        $saldosIva,
                        $montoDisponiblePago
                    );
                }

                // Distribuir este pago entre los pedidos que aún tienen saldo pendiente
                foreach ($pedidos as $pedido) {
                    // Buscar el detalle correspondiente a este pedido
                    $detalle = collect($detallePedidos)->firstWhere('pedido_id', $pedido->id);

                    if (!$detalle) {
                        continue; // Saltar si no hay detalle para este pedido
                    }

                    // Determinar qué saldos están marcados para este pedido
                    $opcionesPed = $distribucionSaldos[$pedido->id] ?? null;
                    $incluyeIva = $opcionesPed !== null ? !empty($opcionesPed['iva']) : true;
                    $incluyeBase = $opcionesPed !== null ? !empty($opcionesPed['base']) : true;
                    $incluyeAjustes = $opcionesPed !== null ? !empty($opcionesPed['ajustes']) : true;

                    // Releer el pedido para trabajar siempre con el saldo más reciente en base de datos.
                    $pedidoActual = Pedido::select('id', 'base', 'saldo_base', 'saldo_iva_bs', 'saldo_ajustes', 'total_ajustes', 'iva_bs', 'porc_retencion')->find($pedido->id);
                    if (!$pedidoActual) {
                        continue;
                    }

                    // Calcular cuánto se ha asignado ya a este pedido
                    $pagosIdsDelGrupo = $pagos_finales->pluck('id')->toArray();
                    $montoAsignadoPedido = PagoPedido::where('pedido_id', $pedido->id)
                        ->whereIn('pago_id', $pagosIdsDelGrupo)
                        ->sum('monto');

                    $descuentoDetalle = (float) ($detalle['descuento'] ?? 0);
                    $saldoBaseActual = (float) ($pedidoActual->saldo_base ?? 0);

                    // Para pagos en divisa: el descuento solo se registra una vez (primer pago).
                    // Si ya existe un pago previo con descuento > 0 para este pedido, no aplicar de nuevo.
                    $descuentoYaAplicado = $moneda_pago != 'Bolívares' && PagoPedido::where('pedido_id', $pedido->id)
                        ->where('descuento', '>', 0)
                        ->exists();
                    $aplicarDescuentoAhora = $moneda_pago != 'Bolívares' && !$descuentoYaAplicado;
                    $descuentoAplicadoAhora = $aplicarDescuentoAhora ? $descuentoDetalle : 0;

                    // Para pagos en divisa: saldo_base, saldo_iva_bs y saldo_ajustes no se reducen
                    // al registrar (solo al aprobar). Hay que descontar manualmente los montos ya
                    // asignados en pagos EN REVISION de este mismo grupo para calcular el saldo real
                    // disponible; de lo contrario cada pago posterior recontaría el saldo completo.
                    $montoPendienteDivisa       = 0;
                    $montoPendienteDivisaAjustes = 0;
                    $montoPendienteDivisaIva     = 0;
                    if ($moneda_pago != 'Bolívares') {
                        $ppEnRevision = PagoPedido::where('pedido_id', $pedido->id)
                            ->whereHas('pago', function ($q) {
                                $q->where('estatus', 'EN REVISION')
                                  ->where('moneda_pago', '!=', 'Bolívares');
                            });
                        $montoPendienteDivisa        = (float) (clone $ppEnRevision)->sum('monto');
                        $montoPendienteDivisaAjustes = (float) (clone $ppEnRevision)->sum('ajustes_monto');
                        $montoPendienteDivisaIva     = (float) (clone $ppEnRevision)->sum('iva');
                    }

                    $saldoIvaPendientePedido = $incluyeIva
                        ? max((float) ($pedidoActual->saldo_iva_bs ?? 0) - $montoPendienteDivisaIva, 0)
                        : 0;

                    // El saldo real considera si el usuario marcó abonar a Base y/o a Ajustes
                    $saldoAjustesActual   = (float) ($pedidoActual->saldo_ajustes ?? 0);
                    $descuentoParaSaldoReal = ($descuentoYaAplicado || $aplicarDescuentoAhora) ? $descuentoDetalle : 0;
                    $saldoReal            = $incluyeBase ? max($saldoBaseActual - $descuentoParaSaldoReal - $montoPendienteDivisa, 0) : 0;
                    $saldoRealAjustes     = $incluyeAjustes ? max($saldoAjustesActual - $montoPendienteDivisaAjustes, 0) : 0;
                    // saldoPendientePedido incluye base + ajustes según lo seleccionado
                    $saldoPendientePedido = $saldoReal + $saldoRealAjustes;

                    \Illuminate\Support\Facades\Log::info('Detalle ');
                    \Illuminate\Support\Facades\Log::info($detalle);
                    \Illuminate\Support\Facades\Log::info('Pedido ' . $pedido->id . ' - Saldo saldoReal: ' . $saldoReal);
                    \Illuminate\Support\Facades\Log::info('Pedido ' . $pedido->id . ' - Saldo pendiente: ' . $saldoPendientePedido);

                    // Para divisa con IVA: el IVA ya comprometido en otro pago EN REVISION de este
                    // mismo grupo se descuenta arriba (saldoIvaPendientePedido). Seguir aplicando
                    // IVA en los pagos siguientes mientras quede saldo neto pendiente, en vez de
                    // dejar de intentarlo por el solo hecho de que un pago previo ya aportó algo.
                    $aplicarIvaEnDivisaAhora = !$esPagoBolivaresConIva && $incluyeIva && $saldoIvaPendientePedido > 0.01;

                    if (!$esPagoBolivaresConIva && $saldoPendientePedido <= 0.01
                        && (!$aplicarIvaEnDivisaAhora || $saldoIvaPendientePedido <= 0.01)) {
                        continue; // Este pedido ya no tiene saldos seleccionados para cubrir
                    }

                    if ($esPagoBolivaresConIva && $saldoPendientePedido <= 0.01 && $saldoIvaPendientePedido <= 0.01) {
                        continue; // Este pedido ya no tiene saldos seleccionados para cubrir
                    }

                    // Recalcular el monto disponible en el pago usando el acumulador en memoria
                    if ($esPagoBolivaresConIva) {
                        $montoDisponiblePago = max((float) $pago->monto_bs - $montoAsignadoPagoBs, 0);
                    } else {
                        $montoDisponiblePago = max((float) $pago->monto - $montoAsignadoPago, 0);
                    }

                    if ($montoDisponiblePago <= 0.01) {
                        break; // Este pago ya está completamente asignado, pasar al siguiente pago
                    }

                    $montoParaAsignar = 0;
                    $montoParaAsignarBs = 0;
                    $montoPagadoUsd = 0;
                    $ivaAplicadoBs = 0;
                    $retencionPedido = 0;

                    if ($esPagoBolivaresConIva) {
                        \Illuminate\Support\Facades\Log::info('Opción IVA: ' . $opcion_iva);
                        \Illuminate\Support\Facades\Log::info('Saldo IVA pendiente: ' . $saldoIvaPendientePedido . ' | Monto disponible: ' . $montoDisponiblePago);

                        // ── PASO 1 (Prioridad 1): Calcular cuánto IVA se aplica ────────────────────────────────
                        if ($incluyeIva && $saldoIvaPendientePedido > 0.001) {
                            $retencionPedido = $opcion_iva === 'retencion'
                                ? $this->retencionPendiente($pedidoActual, $porcentajeRetencionPedido($pedidoActual))
                                : 0;
                            $ivaPendienteAPagar = $opcion_iva === 'retencion'
                                ? $this->netoIvaPendiente($pedidoActual, $porcentajeRetencionPedido($pedidoActual))
                                : $saldoIvaPendientePedido;
                            $ivaAplicadoBs = isset($ivaReservadoPorPedido[$pedido->id])
                                ? min($ivaReservadoPorPedido[$pedido->id], $montoDisponiblePago)
                                : min($ivaPendienteAPagar, $montoDisponiblePago);
                        } else {
                            $ivaAplicadoBs = 0;
                            $retencionPedido = 0;
                        }
                        \Illuminate\Support\Facades\Log::info('IVA completo a pagar: ' . $ivaAplicadoBs);

                        // ── PASO 2 (Prioridad 2 y 3): Con el resto, pagar Base y luego Ajustes ─
                        $ivaParaPedidosSiguientes = $ivaReservadoSufijo[$pedido->id] ?? 0;
                        $restantePagoBs = max($montoDisponiblePago - $ivaAplicadoBs - $ivaParaPedidosSiguientes, 0);
                        $maximoBaseEnBs = max($saldoPendientePedido, 0) * (float) $tasa_cambio;
                        $montoBaseAplicadoBs = min($restantePagoBs, $maximoBaseEnBs);

                        \Illuminate\Support\Facades\Log::info('IVA aplicado Bs: ' . $ivaAplicadoBs . ' | Base aplicada Bs: ' . $montoBaseAplicadoBs);

                        $montoParaAsignarBs = $ivaAplicadoBs + $montoBaseAplicadoBs;
                        $montoDisponibleUsd = (float) $tasa_cambio > 0 ? $montoBaseAplicadoBs / (float) $tasa_cambio : 0;

                        // Tolerancia de redondeo entre Bs y USD:
                        // Si la diferencia entre el saldo pendiente en USD y el disponible en USD es <= $0.02
                        // y el monto restante en Bs cubre prácticamente el total en Bs (diferencia <= 0.50 Bs),
                        // liquidar el saldo completo para evitar $0.01 residuales por imprecisión decimal.
                        if ($saldoPendientePedido > 0 && ($saldoPendientePedido - $montoDisponibleUsd) > 0 && ($saldoPendientePedido - $montoDisponibleUsd) <= 0.02) {
                            if ($restantePagoBs >= ($maximoBaseEnBs - 0.50)) {
                                $montoDisponibleUsd = $saldoPendientePedido;
                                $montoBaseAplicadoBs = $restantePagoBs;
                            }
                        }

                        [$montoPagadoUsd, $ajustesAsignadoDiv] = $this->distribuirBaseYAjustes(
                            $saldoReal,
                            $saldoRealAjustes,
                            $montoDisponibleUsd
                        );

                        // Actualizar consumo acumulado en Bs para este pago
                        $montoAsignadoPagoBs += ($ivaAplicadoBs + $montoBaseAplicadoBs);

                        $montoParaAsignar = $montoPagadoUsd + $ajustesAsignadoDiv;

                    } elseif ($aplicarIvaEnDivisaAhora && $saldoIvaPendientePedido > 0.01) {
                        // ── DIVISA + IVA: pagar IVA convertido a USD + Base + Ajustes ──────────────────────
                        $retencionPedido = $opcion_iva_divisa === 'retencion'
                            ? $this->retencionPendiente($pedidoActual, $porcentajeRetencionPedido($pedidoActual))
                            : 0;
                        $tasaDiv = (float) $tasa_cambio > 0 ? (float) $tasa_cambio : 1;

                        // Calcular IVA Bs a comprometer (respetando opción retención).
                        // netoIvaPendiente() (no saldoIvaPendientePedido - retencionPedido) evita volver a
                        // comprometer la porción ya retenida cuando un pago anterior de este mismo pedido
                        // ya cobró el neto completo y retencionPedido por tanto ya dio 0.
                        $ivaBsAComprometer = $opcion_iva_divisa === 'retencion'
                            ? $this->netoIvaPendiente($pedidoActual, $porcentajeRetencionPedido($pedidoActual))
                            : $saldoIvaPendientePedido;
                        $ivaUsdEquiv = round($ivaBsAComprometer / $tasaDiv, 2);

                        // Prioridad 1: IVA
                        $ivaUsdAsignado = min($ivaUsdEquiv, $montoDisponiblePago);
                        $ivaAplicadoBs = ($ivaUsdEquiv > 0.001)
                            ? round($ivaBsAComprometer * ($ivaUsdAsignado / $ivaUsdEquiv), 2)
                            : 0;

                        // Prioridad 2 y 3: Con el remanente, Base y luego Ajustes
                        $remanenteDivisaUsd = max($montoDisponiblePago - $ivaUsdAsignado, 0);

                        if ($saldoPendientePedido > 0 && ($saldoPendientePedido - $remanenteDivisaUsd) > 0 && ($saldoPendientePedido - $remanenteDivisaUsd) <= 0.02) {
                            if ($montoDisponiblePago >= ($saldoPendientePedido + $ivaUsdAsignado - 0.02)) {
                                $remanenteDivisaUsd = $saldoPendientePedido;
                            }
                        }

                        [$montoPagadoUsd, $ajustesAsignadoDiv] = $this->distribuirBaseYAjustes(
                            $saldoReal,
                            $saldoRealAjustes,
                            $remanenteDivisaUsd
                        );

                        $montoParaAsignar = $montoPagadoUsd + $ajustesAsignadoDiv + $ivaUsdAsignado;
                        $montoParaAsignarBs = $montoParaAsignar * $tasaDiv;
                        $montoAsignadoPago += $montoParaAsignar;

                        // Rastrear retención pendiente en divisa
                        if ($opcion_iva_divisa === 'retencion' && $retencionPedido > 0.001) {
                            $pedidosConRetencionPendiente[$pedido->id] = $retencionPedido;
                        }

                        \Illuminate\Support\Facades\Log::info('Divisa+IVA pedido ' . $pedido->id
                            . ': base=' . $montoPagadoUsd
                            . ' ajustes=' . $ajustesAsignadoDiv
                            . ' ivaUsd=' . $ivaUsdAsignado
                            . ' ivaBs=' . $ivaAplicadoBs);

                    } else {
                        // ── DIVISA puro (sin IVA): Prioridad Base y luego Ajustes ───────────────
                        $montoParaAsignar  = min($saldoPendientePedido, $montoDisponiblePago);
                        if ($saldoPendientePedido > 0 && ($saldoPendientePedido - $montoParaAsignar) > 0 && ($saldoPendientePedido - $montoParaAsignar) <= 0.02) {
                            if ($montoDisponiblePago >= ($saldoPendientePedido - 0.02)) {
                                $montoParaAsignar = $saldoPendientePedido;
                            }
                        }

                        [$montoPagadoUsd, $ajustesAsignadoDiv] = $this->distribuirBaseYAjustes(
                            $saldoReal,
                            $saldoRealAjustes,
                            $montoParaAsignar
                        );

                        if ((float) $pago->monto > 0) {
                            $montoParaAsignarBs = ((float) $pago->monto_bs) * ($montoParaAsignar / (float) $pago->monto);
                        }
                        $montoAsignadoPago += $montoParaAsignar;
                    }

                    $montoPagadoUsd     = max((float) $montoPagadoUsd, 0);
                    $ajustesAsignadoDiv = max((float) ($ajustesAsignadoDiv ?? 0), 0);

                    if ($montoPagadoUsd <= 0.01 && $ivaAplicadoBs <= 0.01 && $ajustesAsignadoDiv <= 0.01) {
                        continue;
                    }

                    \Illuminate\Support\Facades\Log::info('Monto para asignar al pedido ' . $pedido->id . ': ' . $montoParaAsignar);

                    // Calcular la proporción para distribuir IVA, retención y descuento.
                    $saldoTotalPedido = $saldoReal;
                    $proporcion = $saldoTotalPedido > 0 ? ($montoParaAsignar / $saldoTotalPedido) : 0;

                    // Crear el registro en pagos_pedidos
                    $pagoPedido = new PagoPedido();
                    $pagoPedido->pago_id = $pago->id;
                    $pagoPedido->pedido_id = $pedido->id;
                    $pagoPedido->monto = round($montoPagadoUsd, 2);
                    // iva se usa como monto en Bs aplicado a saldo_iva_bs para poder revertir exactamente al rechazar.
                    $pagoPedido->iva = round($ivaAplicadoBs, 2);
                    
                    // La retención se calcula desde el pedido, respetando si se incluyó IVA
                    if ($opcion_iva === 'retencion' && $esPagoBolivaresConIva && $incluyeIva) {
                        $pagoPedido->retencion = round(
                            $this->retencionPendiente($pedidoActual, $porcentajeRetencionPedido($pedidoActual)),
                            2
                        );
                        \Illuminate\Support\Facades\Log::info('Retención completa registrada: ' . $pagoPedido->retencion);
                    } elseif ($opcion_iva_divisa === 'retencion' && !$esPagoBolivaresConIva && $incluyeIva) {
                        $pagoPedido->retencion = round($retencionPedido, 2);
                    } else {
                        $pagoPedido->retencion = 0;
                    }
                    
                    // Guardar solo el descuento realmente aplicado al saldo para poder revertirlo al rechazar.
                    $pagoPedido->descuento = round($descuentoAplicadoAhora, 2);
                    if ($ajustesAsignadoDiv > 0.001) {
                        $pagoPedido->ajustes_monto = round($ajustesAsignadoDiv, 2);
                    }
                    $pagoPedido->created_at = now();
                    $pagoPedido->updated_at = now();
                    $pagoPedido->save();

                    // Descontar saldos al registrar el pago EN REVISION.
                    $saldoIvaRestante = max($saldoIvaPendientePedido - $ivaAplicadoBs, 0);
                    $saldoBaseRestante = max($saldoReal - $montoPagadoUsd, 0);
                    
                    \Illuminate\Support\Facades\Log::info('Validación actualización pedido ' . $pedido->id . ':');
                    \Illuminate\Support\Facades\Log::info('IVA aplicado: ' . $ivaAplicadoBs . ', IVA restante: ' . $saldoIvaRestante);
                    \Illuminate\Support\Facades\Log::info('Base aplicada: ' . $montoPagadoUsd . ', Base restante: ' . $saldoBaseRestante);

                    // Bolívares: reducir saldos inmediatamente (EN REVISION).
                    // Divisa:    saldo_base y saldo_iva_bs se reducen solo al APROBAR.
                    if ($esPagoBolivaresConIva) {
                        $updatePedido = [
                            'saldo_base' => DB::raw('GREATEST(saldo_base - ' . (float) ($pagoPedido->monto + $pagoPedido->descuento) . ', 0)')
                        ];
                        if ((float) $pagoPedido->iva > 0) {
                            $updatePedido['saldo_iva_bs'] = DB::raw('GREATEST(saldo_iva_bs - ' . (float) $pagoPedido->iva . ', 0)');
                        }
                        // Reducir saldo_ajustes por el monto del ajuste (USD) incluido en el pago.
                        $ajustesNetoDetalle = (float) ($pagoPedido->ajustes_monto ?? 0);
                        if ($ajustesNetoDetalle > 0.01) {
                            $updatePedido['saldo_ajustes'] = DB::raw('GREATEST(saldo_ajustes - ' . round($ajustesNetoDetalle, 2) . ', 0)');
                        }
                        Pedido::where('id', $pedido->id)->update($updatePedido);

                        // Normalizar residuos insignificantes (<= $0.015 USD o <= 0.015 Bs) que puedan quedar por redondeos de conversión
                        $pedCheck = Pedido::find($pedido->id);
                        if ($pedCheck) {
                            $uResiduos = [];
                            if ((float) $pedCheck->saldo_base > 0 && (float) $pedCheck->saldo_base <= 0.015) {
                                $uResiduos['saldo_base'] = 0;
                            }
                            if ((float) $pedCheck->saldo_iva_bs > 0 && (float) $pedCheck->saldo_iva_bs <= 0.015) {
                                $uResiduos['saldo_iva_bs'] = 0;
                            }
                            if (abs((float) $pedCheck->saldo_ajustes) > 0 && abs((float) $pedCheck->saldo_ajustes) <= 0.015) {
                                $uResiduos['saldo_ajustes'] = 0;
                            }
                            if (!empty($uResiduos)) {
                                Pedido::where('id', $pedido->id)->update($uResiduos);
                            }
                        }

                        // Marcar los ajustes como pagados en cuanto su saldo llega a 0, sin esperar
                        // a que el pedido quede PAGADO (la base puede quedar pendiente a propósito).
                        // De lo contrario pedido_ajustes.pagado sigue en false y el ajuste reaparece
                        // como pendiente en el siguiente pago de este mismo pedido.
                        if ($ajustesNetoDetalle > 0.01) {
                            $pedidoAjustesCheck = Pedido::find($pedido->id);
                            if ($pedidoAjustesCheck && (float) ($pedidoAjustesCheck->saldo_ajustes ?? 0) <= 0.01) {
                                PedidoAjuste::marcarPagados((int) $pedido->id);
                            }
                        }
                    }
                    // Divisa + IVA: saldo_iva_bs NO se reduce aquí; el IVA (Bs) queda
                    // registrado en pagoPedido->iva y se aplica cuando el admin aprueba.

                    // Rastrear retención pendiente (Bolívares)
                    if ($opcion_iva === 'retencion' && $esPagoBolivaresConIva && $incluyeIva) {
                        $retencionPedido = $this->retencionPendiente(
                            $pedidoActual,
                            $porcentajeRetencionPedido($pedidoActual)
                        );
                        if ($retencionPedido > 0.01) {
                            $pedidosConRetencionPendiente[$pedido->id] = $retencionPedido;
                        }
                    }
                    // Rastrear retención pendiente (Divisa) — ya fue asignada en el bloque divisa+IVA

                    \Illuminate\Support\Facades\Log::info('Saldos actualizados - IVA: ' . $pagoPedido->iva . ', Base: ' . $pagoPedido->monto);
                }
            }

            // Procesar cada pedido para crear los detalles del grupo y comisiones
            foreach ($pedidos as $pedido) {
                // Buscar el detalle correspondiente a este pedido
                $detalle = collect($detallePedidos)->firstWhere('pedido_id', $pedido->id);

                if (!$detalle) {
                    continue; // Saltar si no hay detalle para este pedido
                }

                \Illuminate\Support\Facades\Log::info('Procesando pedido para detalles: ' . $pedido->id);
                \Illuminate\Support\Facades\Log::info($detalle);

                $pagoGrupoDetalle = new \App\Models\PagoGrupoDetalles();
                $pagoGrupoDetalle->pago_grupo_id = $pago_grupo->id;
                $pagoGrupoDetalle->monto = $detalle['saldo'] ?? 0;
                $pagoGrupoDetalle->base = $detalle['saldo'] ?? 0;
                $pagoGrupoDetalle->descuento = $detalle['descuento'] ?? 0;
                $detallesPedido = \App\Models\PedidoDetalle::where('pedido_id', $pedido->id)->first();
                $porc_iva_det = $detallesPedido && isset($detallesPedido) && $detallesPedido->iva > 0 ? $detallesPedido->iva / 100 : 0;
                $pagoGrupoDetalle->iva = ($detalle['saldo'] - $detalle['descuento']) * $porc_iva_det ?? 0;
                $pagoGrupoDetalle->retencion = $detalle['retencion'] ?? 0;
                $pagoGrupoDetalle->total = ($detalle['saldo'] ?? 0) + (($detalle['iva'] ?? 0) - ($detalle['retencion'] ?? 0));
                $pagoGrupoDetalle->pedido_id = $pedido->id;
                $pagoGrupoDetalle->save();

                // Validar si se debe actualizar el estatus
                $actualizarEstatus = true;

                // LOGICA DE PAGO PARCIAL (MODIFICADA)
                
                if ($pedidos->count() === 1) {
                    $montoTotalSimple = $pedido->pedido_detalle->sum(function ($detalle) {
                        return $detalle->precio * $detalle->cantidad;
                    });

                    $totalPagado = \App\Models\PagoPedido::where('pedido_id', $pedido->id)->sum('monto');

                    // Si queda deuda pendiente y es un único pedido, mantenemos el estatus actual (APROBADO)
                    if (($montoTotalSimple - $totalPagado) > 0.01) {
                        $actualizarEstatus = false;
                    }
                }
                
                if ($actualizarEstatus) {
                    Pedido::where('id', $pedido->id)->update(['estatus' => 'EN REVISION']);
                }
            }

            DB::commit();

            // Limpiar la sesión de pago
            session()->forget('pago_cliente');

            $redirectResponse = redirect()->route('vendedores.pagos.mostrarComprobante', $pago_grupo->id)
                ->with('success', 'Pago registrado exitosamente. Pendiente de aprobación.');

            // Notificación cuando quedan pedidos con retención pendiente
            if (!empty($pedidosConRetencionPendiente)) {
                $totalRetencionPendiente = array_sum($pedidosConRetencionPendiente);
                $pedidosIds = implode(', #', array_keys($pedidosConRetencionPendiente));
                $redirectResponse = $redirectResponse->with('aviso_retencion', [
                    'pedidos'                  => array_keys($pedidosConRetencionPendiente),
                    'pedidos_ids_str'          => $pedidosIds,
                    'total_retencion_pendiente' => $totalRetencionPendiente,
                ]);

                // Guardar nota en los pagos del grupo para que el administrador la vea
                Pago::where('pago_grupo_id', $pago_grupo->id)->update([
                    'descripcion' => DB::raw("CONCAT(COALESCE(descripcion, ''), ' [RETENCIÓN PENDIENTE: Bs." . number_format($totalRetencionPendiente, 2) . " — pedidos #" . $pedidosIds . "]')")
                ]);
            }

            return $redirectResponse;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error al procesar pago múltiple: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Ocurrió un error al procesar el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function retencionPendiente(Pedido $pedido, ?float $porcentajePago = null): float
    {
        $ivaBs = (float) ($pedido->iva_bs ?? 0);
        $porcentaje = $porcentajePago ?? (float) ($pedido->porc_retencion ?? 0);
        $retencionEsperada = round($ivaBs * ($porcentaje / 100), 2);

        if ($retencionEsperada <= 0.001) {
            return 0;
        }

        $retencionesRegistradas = (float) PagoPedido::where('pedido_id', $pedido->id)
            ->whereHas('pago', function ($query) {
                $query->where('estatus', '!=', 'RECHAZADO');
            })
            ->sum('retencion');

        return min(
            max($retencionEsperada - $retencionesRegistradas, 0),
            max((float) ($pedido->saldo_iva_bs ?? 0), 0)
        );
    }

    /**
     * IVA neto (post-retención) que aún falta cobrar en efectivo/transferencia, cuando se eligió
     * aplicar retención. A diferencia de restar retencionPendiente() al saldo_iva_bs ACTUAL —que
     * ya puede haber sido reducido por un pago anterior de este mismo pedido, dejando solo el
     * remanente retenido pendiente de comprobante—, esto compara lo YA cobrado en pagos_pedidos.iva
     * contra el neto total esperado (iva_bs - retención). Así, una vez cobrado el neto completo,
     * el remanente en saldo_iva_bs (que es exactamente la retención) deja de ofrecerse como
     * "pendiente de pago": solo se libera al aprobar el comprobante de retención.
     */
    private function netoIvaPendiente(Pedido $pedido, ?float $porcentajePago = null): float
    {
        $ivaBs = (float) ($pedido->iva_bs ?? 0);
        $porcentaje = $porcentajePago ?? (float) ($pedido->porc_retencion ?? 0);
        $retencionEsperada = $porcentaje > 0 ? round($ivaBs * ($porcentaje / 100), 2) : 0;
        $netoEsperado = max($ivaBs - $retencionEsperada, 0);

        $netoYaCobrado = (float) PagoPedido::where('pedido_id', $pedido->id)
            ->whereHas('pago', function ($query) {
                $query->where('estatus', '!=', 'RECHAZADO');
            })
            ->sum('iva');

        return min(
            max($netoEsperado - $netoYaCobrado, 0),
            max((float) ($pedido->saldo_iva_bs ?? 0), 0)
        );
    }

    private function reservarIvaPrioritario(array $saldosIva, float $montoDisponibleBs): array
    {
        $reservadoPorPedido = [];
        $presupuestoDisponible = max($montoDisponibleBs, 0);

        foreach ($saldosIva as $pedidoId => $saldoIva) {
            $reservado = min(max((float) $saldoIva, 0), $presupuestoDisponible);
            $reservadoPorPedido[$pedidoId] = $reservado;
            $presupuestoDisponible -= $reservado;
        }

        $reservadoPosterior = [];
        $acumulado = 0;
        foreach (array_reverse($reservadoPorPedido, true) as $pedidoId => $reservado) {
            $reservadoPosterior[$pedidoId] = $acumulado;
            $acumulado += $reservado;
        }

        return [$reservadoPorPedido, $reservadoPosterior];
    }

    private function distribuirBaseYAjustes(
        float $saldoBase,
        float $saldoAjustes,
        float $montoDisponibleUsd
    ): array {
        $montoBase = min(max($saldoBase, 0), max($montoDisponibleUsd, 0));
        $montoAjustes = min(
            max($saldoAjustes, 0),
            max($montoDisponibleUsd - $montoBase, 0)
        );

        return [$montoBase, $montoAjustes];
    }

    /**
     * Permite al vendedor subir el comprobante de retención de IVA de un pedido.
     * El archivo se almacena en pedidos.comprobante_retencion y queda visible
     * para el administrador en el módulo de retenciones, quien lo descargará y
     * validará para liquidar el saldo_iva_bs pendiente.
     */
    public function subirComprobanteRetencion(Request $request, $pedidoId)
    {
        $request->validate([
            'comprobante_retencion' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ], [
            'comprobante_retencion.required' => 'Debe seleccionar un archivo.',
            'comprobante_retencion.mimes'    => 'Solo se permiten archivos PDF, JPG o PNG.',
            'comprobante_retencion.max'      => 'El archivo no debe superar 10 MB.',
        ]);

        $user   = auth()->user();
        $pedido = Pedido::on('company')
            ->where('id', $pedidoId)
            ->where('user_id', $user->id)
            ->where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->firstOrFail();

        // Usar la misma estrategia de comprobantes de pago: storage/app/public/comprobantes
        $ruta = $this->uploadComprobante($request->file('comprobante_retencion'));

        DB::connection('company')
            ->table('pedidos')
            ->where('id', $pedido->id)
            ->update(['comprobante_retencion' => $ruta]);

        return response()->json([
            'success' => true,
            'message' => 'Comprobante de retención cargado correctamente. El administrador lo revisará para validar el saldo pendiente.',
            'ruta'    => $ruta,
        ]);
    }
}
