<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComisionVendedor;
use App\Models\Vendedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ComisionesExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\ComisionPago;
use App\Models\ComisionMovimiento;
use App\Models\PagoDestino;

class ComisionVendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Base query para agrupar por pedido_id (cada pedido una sola vez)
        $query = DB::connection('company')->table('pago_grupo_detalles as pgd')
            ->join('pedidos as p', 'pgd.pedido_id', '=', 'p.id')
            ->leftJoin('pedidos_facturas as pf', 'p.id', '=', 'pf.pedido_id')
            ->leftJoin(DB::raw('(SELECT 
                pgd_sub.pedido_id,
                SUM(cv.monto_comision) as total_comision,
                MIN(cv.estatus_comision) as estatus_comision,
                cv.nombre_vendedor,
                cv.correo_vendedor,
                pgd_sub.created_at as fecha_pedido,
                MAX(cp.recibido) as recibido
                FROM comision_vendedores cv 
                JOIN pago_grupo_detalles pgd_sub ON cv.pago_id = pgd_sub.id
                LEFT JOIN comision_pagos cp ON cv.id = cp.comision_vendedores_id
                GROUP BY pgd_sub.pedido_id, cv.nombre_vendedor, cv.correo_vendedor, pgd_sub.created_at
            ) as comision_agrupada'), function ($join) {
                $join->on('pgd.pedido_id', '=', 'comision_agrupada.pedido_id');
            })
            ->leftJoin(DB::raw('(SELECT pedido_id, COUNT(*) as total_productos FROM pedido_detalle GROUP BY pedido_id) as pd_count'), function ($join) {
                $join->on('pgd.pedido_id', '=', 'pd_count.pedido_id');
            })
            ->leftJoin(DB::raw('(SELECT DISTINCT pp.pedido_id, 
                CASE WHEN pag.tpago_id IN (14, 16) THEN 1 ELSE 0 END as tiene_divisa,
                CASE WHEN pag.tpago_id NOT IN (14, 16) THEN 1 ELSE 0 END as tiene_bolivares
                FROM pagos_pedidos pp 
                JOIN pagos pag ON pp.pago_id = pag.id) as pagos_info'), function ($join) {
                $join->on('pgd.pedido_id', '=', 'pagos_info.pedido_id');
            })
            ->select(
                'pgd.pedido_id',
                'p.descripcion as descripcion_pedido',
                'p.descuento as descuento_pedido',
                'comision_agrupada.fecha_pedido',
                'comision_agrupada.nombre_vendedor',
                'comision_agrupada.correo_vendedor',
                'comision_agrupada.total_comision',
                'comision_agrupada.estatus_comision',
                'comision_agrupada.recibido',
                'pd_count.total_productos as cantidad_productos',
                DB::raw('GROUP_CONCAT(DISTINCT pf.factura) as facturas'),
                DB::raw('CASE WHEN MAX(pagos_info.tiene_divisa) = 1 THEN 1 ELSE 0 END as tiene_divisa'),
                DB::raw('CASE WHEN MAX(pagos_info.tiene_bolivares) = 1 THEN 1 ELSE 0 END as tiene_bolivares'),
                DB::raw('CASE 
                    WHEN MAX(pagos_info.tiene_divisa) = 1 THEN "DIVISA EFECTIVO"
                    WHEN MAX(pagos_info.tiene_bolivares) = 1 THEN "BOLIVARES"
                    ELSE "SIN PAGO REGISTRADO"
                END as moneda_pago')
            )
            ->groupBy('pgd.pedido_id', 'p.descripcion', 'p.descuento', 'comision_agrupada.fecha_pedido', 'comision_agrupada.nombre_vendedor', 'comision_agrupada.correo_vendedor', 'comision_agrupada.total_comision', 'comision_agrupada.estatus_comision', 'comision_agrupada.recibido', 'pd_count.total_productos');

        // Filtros
        if ($request->has('vendedor') && $request->vendedor) {
            $query->where('comision_agrupada.correo_vendedor', $request->vendedor);
        }

        if ($request->has('cliente') && $request->cliente) {
            $query->where('p.descripcion', 'like', '%' . $request->cliente . '%');
        }

        // Filtro de estado - por defecto "pendiente" si no se especifica
        $estadoFiltro = $request->get('estado', 'pendiente');
        if ($estadoFiltro && in_array($estadoFiltro, ['pendiente', 'pagada', 'rechazada'])) {
            $query->having('estatus_comision', $estadoFiltro);
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('comision_agrupada.fecha_pedido', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('comision_agrupada.fecha_pedido', '<=', $request->fecha_fin);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'fecha');
        $sortDirection = $request->get('direction', 'desc');

        // Mapeo de campos para ordenamiento
        $sortableFields = [
            'fecha' => 'comision_agrupada.fecha_pedido',
            'pedido' => 'pgd.pedido_id',
            'vendedor' => 'comision_agrupada.nombre_vendedor',
            'total' => 'total_comision',
            'estado' => 'estatus_comision'
        ];

        // Validar campo de ordenamiento
        if (array_key_exists($sortField, $sortableFields)) {
            $sortField = $sortableFields[$sortField];
        } elseif (!in_array($sortField, $sortableFields)) {
            $sortField = 'comision_agrupada.fecha_pedido';
        }

        // Validar dirección
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

        // Aplicar ordenamiento
        if ($sortField === 'total_comision' || $sortField === 'estatus_comision') {
            // Para campos agregados, usar HAVING
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Obtener lista de vendedores para el filtro
        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('v.email', 'v.codigo', 'u.name as nombre_completo')
            ->orderBy('v.codigo')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = $item->codigo . ' - ' . $item->nombre_completo . ' (' . $item->email . ')';
                return [$item->email => $label];
            })
            ->toArray();

        // Obtener totales generales
        $totalesQuery = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id');

        // Aplicar los mismos filtros a los totales
        if ($request->has('vendedor') && $request->vendedor) {
            $totalesQuery->where('cv.correo_vendedor', $request->vendedor);
        }
        if ($estadoFiltro && in_array($estadoFiltro, ['pendiente', 'pagada', 'rechazada'])) {
            $totalesQuery->where('cv.estatus_comision', $estadoFiltro);
        }
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $totalesQuery->whereDate('pgd.created_at', '>=', $request->fecha_inicio);
        }
        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $totalesQuery->whereDate('pgd.created_at', '<=', $request->fecha_fin);
        }

        $totales = $totalesQuery->select(
            DB::raw('SUM(cv.monto_comision) as total_comision'),
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "pendiente" THEN cv.monto_comision ELSE 0 END) as total_pendiente'),
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "pagada" THEN cv.monto_comision ELSE 0 END) as total_pagada'),
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "rechazada" THEN cv.monto_comision ELSE 0 END) as total_rechazada')
        )->first();

        $totalComision = $totales->total_comision ?? 0;
        $totalPendiente = $totales->total_pendiente ?? 0;
        $totalPagada = $totales->total_pagada ?? 0;
        $totalRechazada = $totales->total_rechazada ?? 0;

        // Top 10 Productos con mayor comisión
        $topProductosQuery = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->select(
                'cv.codigo_producto',
                'cv.nombre_producto',
                DB::raw('SUM(cv.monto_comision) as total_comision'),
                DB::raw('SUM(cv.cantidad) as total_cantidad'),
                DB::raw('AVG(cv.porcentaje_comision) as promedio_porcentaje')
            )
            ->groupBy('cv.codigo_producto', 'cv.nombre_producto');

        // Aplicar los mismos filtros
        if ($request->has('vendedor') && $request->vendedor) {
            $topProductosQuery->where('cv.correo_vendedor', $request->vendedor);
        }
        if ($estadoFiltro && in_array($estadoFiltro, ['pendiente', 'pagada', 'rechazada'])) {
            $topProductosQuery->where('cv.estatus_comision', $estadoFiltro);
        }
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $topProductosQuery->whereDate('pgd.created_at', '>=', $request->fecha_inicio);
        }
        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $topProductosQuery->whereDate('pgd.created_at', '<=', $request->fecha_fin);
        }

        $topProductos = $topProductosQuery->orderBy('total_comision', 'desc')->limit(10)->get();

        // Top 10 Vendedores con mayor comisión
        $topVendedoresQuery = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->select(
                'cv.nombre_vendedor',
                'cv.correo_vendedor',
                DB::raw('SUM(cv.monto_comision) as total_comision'),
                DB::raw('COUNT(DISTINCT cv.pago_id) as total_pedidos'),
                DB::raw('COUNT(DISTINCT cv.codigo_producto) as total_productos')
            )
            ->groupBy('cv.nombre_vendedor', 'cv.correo_vendedor');

        // Aplicar los mismos filtros (excepto vendedor)
        if ($estadoFiltro && in_array($estadoFiltro, ['pendiente', 'pagada', 'rechazada'])) {
            $topVendedoresQuery->where('cv.estatus_comision', $estadoFiltro);
        }
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $topVendedoresQuery->whereDate('pgd.created_at', '>=', $request->fecha_inicio);
        }
        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $topVendedoresQuery->whereDate('pgd.created_at', '<=', $request->fecha_fin);
        }

        $topVendedores = $topVendedoresQuery->orderBy('total_comision', 'desc')->limit(10)->get();

        // Obtener destinos de pago desde sdcloud.banks
        $pagoDestinos = DB::connection('mysql')
            ->table('banks')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        // Obtener tasa del día desde companytasas
        $tasaRecord = DB::connection('company')
            ->table('tasas')
            ->orderBy('created_at', 'desc')
            ->first();
        $tasaDia = $tasaRecord ? $tasaRecord->valor : 0;

        // Check if any filters are applied (excluding sort and direction)
        $hasFilters = $request->hasAny(['vendedor', 'cliente', 'estado', 'fecha_inicio', 'fecha_fin']);

        // Paginar los resultados agrupados
        if ($hasFilters) {
            // If filters are applied, show all results without pagination
            $comisiones = $query->get();
            // Convert to LengthAwarePaginator for compatibility with view
            $comisiones = new \Illuminate\Pagination\LengthAwarePaginator(
                $comisiones,
                $comisiones->count(),
                max(1, $comisiones->count()), // Show all per page, min 1 to avoid division by zero
                1,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
        } else {
            // Normal pagination when no filters
            $comisiones = $query->paginate(20)->withQueryString();
        }

        return view('comisiones.index', compact(
            'comisiones',
            'vendedores',
            'pagoDestinos',
            'tasaDia',
            'totalComision',
            'totalPendiente',
            'totalPagada',
            'totalRechazada',
            'topProductos',
            'topVendedores'
        ));
    }

    /**
     * Actualiza el monto de comisión (total) para un pago_id.
     */
    public function updateMontoComision(Request $request, $pagoId)
    {
        $request->validate([
            'nuevo_monto' => 'required|numeric|min:0',
        ]);

        // Obtener todas las comisiones de ese pago_id
        $comisiones = \App\Models\ComisionVendedor::where('pago_id', $pagoId)->get();
        if ($comisiones->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No se encontraron comisiones para este pago.']);
        }

        // Repartir el nuevo monto proporcionalmente según el monto original de cada registro
        $totalActual = $comisiones->sum('monto_comision');
        if ($totalActual == 0) {
            // Si el total actual es 0, repartir igual
            $nuevoMontoPorComision = $request->nuevo_monto / $comisiones->count();
            foreach ($comisiones as $comision) {
                $comision->monto_comision = $nuevoMontoPorComision;
                $comision->save();
            }
        } else {
            foreach ($comisiones as $comision) {
                $proporcion = $comision->monto_comision / $totalActual;
                $comision->monto_comision = $request->nuevo_monto * $proporcion;
                $comision->save();
            }
        }

        return response()->json(['success' => true, 'message' => 'Monto de comisión actualizado correctamente.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ComisionVendedor $comisionVendedor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComisionVendedor $comisionVendedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ComisionVendedor $comisionVendedor)
    {
        $request->validate([
            'estatus_comision' => 'required|in:pendiente,pagada,rechazada'
        ]);

        $comisionVendedor->update([
            'estatus_comision' => $request->estatus_comision,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de la comisión actualizado correctamente',
            'data' => $comisionVendedor->fresh()
        ]);
    }

    /**
     * Approve all commissions for a given pago_id and save payment details.
     */
    public function aprobar(Request $request, $pagoId)
    {
        $request->validate([
            'pagos_destino_id' => 'required|exists:mysql.banks,id',
            'fecha_pago' => 'required|date',
            'monto_bs' => 'nullable|numeric|min:0',
            'monto_divisa' => 'nullable|numeric|min:0',
            'tasa' => 'nullable|numeric|min:0',
            'forma_pago' => 'required|in:Divisa,Bolivares,Otro',
            'numero_referencia' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'correo_vendedor' => 'nullable|email',
            'saldo_aplicado' => 'nullable|numeric|min:0',
        ]);

        // Soportar múltiples IDs separados por coma
        $pedidoIds = explode(',', $pagoId);

        $comisiones = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->whereIn('pgd.pedido_id', $pedidoIds)
            ->where('cv.estatus_comision', '!=', 'pagada')
            ->select('cv.*')
            ->get();

        if ($comisiones->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No hay comisiones pendientes para este pedido'
            ], 400);
        }

        $totalComision = $comisiones->sum('monto_comision');

        // Generar un ID de grupo para este pago (masivo o individual)
        $grupoPagoId = 'BPG-' . strtoupper(\Illuminate\Support\Str::random(8));

        DB::connection('company')->beginTransaction();
        try {
            foreach ($comisiones as $comision) {
                // Cálculo proporcional de los montos si hay varias comisiones en un mismo pago_id
                $factor = $totalComision > 0 ? ($comision->monto_comision / $totalComision) : 0;

                ComisionPago::create([
                    'grupo_pago_id' => $grupoPagoId,
                    'comision_vendedores_id' => $comision->id,
                    'pagos_destino_id' => $request->pagos_destino_id,
                    'observaciones' => $request->observaciones,
                    'fecha_pago' => $request->fecha_pago,
                    'monto_bs' => $request->monto_bs * $factor,
                    'monto_divisa' => $request->monto_divisa * $factor,
                    'tasa' => $request->tasa,
                    'forma_pago' => $request->forma_pago,
                    'numero_referencia' => $request->numero_referencia,
                ]);

                DB::connection('company')->table('comision_vendedores')
                    ->where('id', $comision->id)
                    ->update(['estatus_comision' => 'pagada', 'updated_at' => now()]);
            }

            // ── Estado de cuenta ─────────────────────────────────────────────
            $correoVendedor = $comisiones->first()->correo_vendedor;
            $nombreVendedor = $comisiones->first()->nombre_vendedor;

            // Monto pagado en USD (siempre referenciamos en divisa para el ledger)
            $montoPagadoUSD = (float) $request->monto_divisa;
            if ($request->forma_pago === 'Bolivares' && (float) $request->tasa > 0) {
                $montoPagadoUSD = round((float) $request->monto_bs / (float) $request->tasa, 2);
            }

            $saldoAplicado = min(
                (float) ($request->saldo_aplicado ?? 0),
                $totalComision
            );

            // Datos legibles para los conceptos
            $pagoGrupoDetallesIds = $comisiones->pluck('pago_id')->unique();
            $pedidosIds = DB::connection('company')
                ->table('pago_grupo_detalles')
                ->whereIn('id', $pagoGrupoDetallesIds)
                ->pluck('pedido_id')
                ->map(fn($id) => '#' . $id)
                ->implode(', ');

            $productos = $comisiones->pluck('nombre_producto')
                ->filter()
                ->unique()
                ->implode(', ');

            $fechaPago = \Carbon\Carbon::parse($request->fecha_pago)->format('d/m/Y');
            $refText = $request->numero_referencia ?: 'sin referencia';

            // Movimiento 1: Comisión devengada (crédito al vendedor)
            ComisionMovimiento::registrarMovimiento([
                'correo_vendedor' => $correoVendedor,
                'nombre_vendedor' => $nombreVendedor,
                'tipo' => 'comision_devengada',
                'monto' => $totalComision,
                'es_credito' => true,
                'concepto' => "Comisión por venta de: {$productos} — Pedido(s): {$pedidosIds}",
                'grupo_pago_id' => $grupoPagoId,
                'monto_comision_original' => $totalComision,
                'registrado_por' => auth()->id(),
            ]);

            // Movimiento 2: Pago realizado (débito al vendedor)
            // El saldo previo negativo (crédito del admin) ya se absorbe automáticamente
            // en el cálculo del saldo corriente al registrar M1. Si además se aplicó un
            // crédito explícito, se deja constancia en el concepto y en saldo_aplicado,
            // pero NO se crea un movimiento adicional para evitar doble contabilización.
            $conceptoPago = "Pago recibido el {$fechaPago} — {$request->forma_pago} · Ref: {$refText}";
            if ($saldoAplicado > 0.001) {
                $conceptoPago .= " (+ \${$saldoAplicado} crédito aplicado)";
            }

            ComisionMovimiento::registrarMovimiento([
                'correo_vendedor' => $correoVendedor,
                'nombre_vendedor' => $nombreVendedor,
                'tipo' => 'pago_comision',
                'monto' => $montoPagadoUSD,
                'es_credito' => false,
                'concepto' => $conceptoPago,
                'grupo_pago_id' => $grupoPagoId,
                'monto_comision_original' => $totalComision,
                'monto_pagado_real' => $montoPagadoUSD,
                'saldo_aplicado' => $saldoAplicado > 0.001 ? $saldoAplicado : null,
                'registrado_por' => auth()->id(),
            ]);
            // ─────────────────────────────────────────────────────────────────

            DB::connection('company')->commit();

            $saldoFinal = ComisionMovimiento::saldoActual($correoVendedor);

            return response()->json([
                'success' => true,
                'message' => 'Pago de comisiones registrado y aprobado correctamente',
                'updated_count' => $comisiones->count(),
                'saldo_final' => $saldoFinal,
                'grupo_pago_id' => $grupoPagoId,
            ]);
        } catch (\Exception $e) {
            DB::connection('company')->rollBack();
            Log::error('Error al aprobar comisiones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject all commissions for a given pedido_id.
     */
    public function rechazar($pedidoId)
    {
        $updated = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->where('pgd.pedido_id', $pedidoId)
            ->update([
                'cv.estatus_comision' => 'rechazada',
                'cv.updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Comisiones rechazadas correctamente',
            'updated_count' => $updated
        ]);
    }

    /**
     * Get commission details for a specific pedido_id.
     */
    public function getDetalles($pedidoId)
    {
        $detalles = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->where('pgd.pedido_id', $pedidoId)
            ->select('cv.codigo_producto', 'cv.nombre_producto', 'cv.cantidad', 'cv.monto_comision', 'cv.porcentaje_comision')
            ->get();

        return response()->json([
            'success' => true,
            'detalles' => $detalles
        ]);
    }

    /**
     * Get destinations for payment.
     */
    public function getPagoDestinos()
    {
        $destinos = PagoDestino::orderBy('nombre')->get(['id', 'nombre']);

        return response()->json([
            'success' => true,
            'destinos' => $destinos
        ]);
    }

    /**
     * Get banks for payment destination (AJAX for modal).
     */
    public function getPagoBancos()
    {
        $bancos = DB::connection('mysql')
            ->table('banks')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'bancos' => $bancos
        ]);
    }

    /**
     * Get payment details for a specific pago_id.
     */
    public function getDetallePago($pagoId)
    {
        $pago = DB::connection('company')
            ->table('pago_grupo_detalles as pgd')
            ->where('pgd.id', $pagoId)
            ->select(
                'pgd.id',
                'pgd.pedido_id',
                'pgd.monto',
                'pgd.base',
                'pgd.descuento',
                'pgd.total',
                'pgd.iva',
                'pgd.retencion',
                'pgd.created_at'
            )
            ->first();

        if (!$pago) {
            return response()->json([
                'success' => false,
                'message' => 'Pago no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pago' => $pago
        ]);
    }

    /**
     * Get the payment registration details for commissions of a specific grupo_pago_id.
     */
    public function getComisionPagoDetalle($grupoPagoId)
    {
        // Si el ID no empieza por BPG-, podría ser un pago_id antiguo
        if (!str_starts_with($grupoPagoId, 'BPG-')) {
            $detallePago = ComisionPago::whereHas('comisionVendedor', function ($q) use ($grupoPagoId) {
                $q->where('pago_id', $grupoPagoId);
            })
                ->with('pagoDestino')
                ->first();
        } else {
            $detallePago = ComisionPago::where('grupo_pago_id', $grupoPagoId)
                ->with('pagoDestino')
                ->first();
        }

        if (!$detallePago) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron detalles de pago para esta transacción'
            ], 404);
        }

        // Si es un grupo, recalcular montos totales del grupo
        if (str_starts_with($grupoPagoId, 'BPG-')) {
            $totalesGrupo = ComisionPago::where('grupo_pago_id', $grupoPagoId)
                ->select(
                    DB::raw('SUM(monto_bs) as total_bs'),
                    DB::raw('SUM(monto_divisa) as total_divisa')
                )->first();

            $detallePago->monto_bs = $totalesGrupo->total_bs;
            $detallePago->monto_divisa = $totalesGrupo->total_divisa;
        }

        return response()->json([
            'success' => true,
            'pago' => $detallePago
        ]);
    }

    /**
     * Confirm receipt of commission payment for all items in a grupo_pago_id or pago_id.
     */
    public function confirmarRecibido($id)
    {
        try {
            if (str_starts_with($id, 'BPG-')) {
                ComisionPago::where('grupo_pago_id', $id)
                    ->update(['recibido' => true]);
            } else {
                $comisionesIds = ComisionVendedor::where('pago_id', $id)->pluck('id');
                ComisionPago::whereIn('comision_vendedores_id', $comisionesIds)
                    ->update(['recibido' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Confirmación de recepción registrada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la recepción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm receipt of multiple commission payments in batch.
     */
    public function confirmarRecibidoLote(Request $request)
    {
        $request->validate([
            'identificadores' => 'required|array|min:1',
            'identificadores.*' => 'required|string'
        ]);

        $vendedorEmail = auth()->user()->email;
        $identificadores = $request->identificadores;
        $updatedCount = 0;

        DB::connection('company')->beginTransaction();
        try {
            foreach ($identificadores as $id) {
                // Verificar que el identificador pertenece al vendedor autenticado
                if (str_starts_with($id, 'BPG-')) {
                    // Es un grupo de pago
                    $comisionesCount = DB::connection('company')
                        ->table('comision_vendedores as cv')
                        ->join('comision_pagos as cp', 'cv.id', '=', 'cp.comision_vendedores_id')
                        ->where('cp.grupo_pago_id', $id)
                        ->where('cv.correo_vendedor', $vendedorEmail)
                        ->count();

                    if ($comisionesCount > 0) {
                        ComisionPago::where('grupo_pago_id', $id)
                            ->update(['recibido' => true]);
                        $updatedCount++;
                    }
                } else {
                    // Es un pago_id individual
                    $comisionesIds = ComisionVendedor::where('pago_id', $id)
                        ->where('correo_vendedor', $vendedorEmail)
                        ->pluck('id');

                    if ($comisionesIds->isNotEmpty()) {
                        ComisionPago::whereIn('comision_vendedores_id', $comisionesIds)
                            ->update(['recibido' => true]);
                        $updatedCount++;
                    }
                }
            }

            DB::connection('company')->commit();

            return response()->json([
                'success' => true,
                'message' => "Se confirmaron {$updatedCount} pago(s) correctamente"
            ]);
        } catch (\Exception $e) {
            DB::connection('company')->rollBack();
            Log::error('Error al confirmar pagos en lote: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la confirmación en lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(ComisionVendedor $comisionVendedor)
    {
        //
    }

    /**
     * Export commissions to Excel or PDF
     */
    public function export(Request $request)
    {
        $query = ComisionVendedor::query()
            ->select('comision_vendedores.*')
            ->join('pagos', 'pagos.id', '=', 'comision_vendedores.pago_id')
            ->orderBy('comision_vendedores.created_at', 'desc');

        // Apply filters
        if ($request->has('vendedor') && $request->vendedor) {
            $query->where('comision_vendedores.correo_vendedor', $request->vendedor);
        }

        if ($request->has('producto') && $request->producto) {
            $query->where(function ($q) use ($request) {
                $q->where('comision_vendedores.codigo_producto', 'like', '%' . $request->producto . '%')
                    ->orWhere('comision_vendedores.nombre_producto', 'like', '%' . $request->producto . '%');
            });
        }

        if ($request->has('estado') && in_array($request->estado, ['pendiente', 'pagada'])) {
            $query->where('comision_vendedores.estatus_comision', $request->estado);
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('comision_vendedores.created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('comision_vendedores.created_at', '<=', $request->fecha_fin);
        }

        $comisiones = $query->get();

        // Get vendedores for the filter in the view
        $vendedores = Vendedor::select('email', 'nombre')
            ->where('estatus', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'email');

        if ($request->formato === 'pdf') {
            $pdf = PDF::loadView('comisiones.export_pdf', [
                'comisiones' => $comisiones,
                'filtros' => $request->all(),
                'vendedores' => $vendedores
            ]);

            return $pdf->download('reporte_comisiones_' . date('Y-m-d') . '.pdf');
        }

        // Default to Excel
        return Excel::download(new ComisionesExport($comisiones), 'reporte_comisiones_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Display a listing of received commissions for the logged-in seller.
     */
    public function comisionesRecibidas(Request $request)
    {
        $vendedorEmail = auth()->user()->email;

        // Base query para agrupar por el PAGO (grupo_pago_id o pago_id si es antiguo)
        $query = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->join('pedidos as p', 'pgd.pedido_id', '=', 'p.id')
            ->leftJoin('comision_pagos as cp', 'cv.id', '=', 'cp.comision_vendedores_id')
            ->leftJoin('pago_destinos as pd', 'cp.pagos_destino_id', '=', 'pd.id')
            ->where('cv.correo_vendedor', $vendedorEmail)
            ->select(
                DB::raw('COALESCE(cp.grupo_pago_id, CAST(cv.pago_id AS CHAR)) as identificador_pago'),
                DB::raw('MIN(cp.fecha_pago) as fecha_desembolso'),
                DB::raw('SUM(cv.monto_comision) as total_comision'),
                DB::raw('MIN(cv.estatus_comision) as estatus_comision'),
                DB::raw('MAX(cp.recibido) as recibido'),
                DB::raw('COUNT(DISTINCT cv.pago_id) as total_pedidos'),
                DB::raw('GROUP_CONCAT(DISTINCT p.descripcion SEPARATOR ", ") as clientes'),
                DB::raw('MAX(cp.numero_referencia) as referencia'),
                DB::raw('MAX(pd.nombre) as banco_destino'),
                DB::raw('MAX(cp.forma_pago) as forma_pago'),
                DB::raw('MAX(cp.observaciones) as observaciones'),
                DB::raw('SUM(cp.monto_divisa) as total_divisa'),
                DB::raw('SUM(cp.monto_bs) as total_bs'),
                DB::raw('MAX(cp.tasa) as tasa')
            )
            ->groupBy('identificador_pago');

        if ($request->has('cliente') && $request->cliente) {
            $query->having('clientes', 'like', '%' . $request->cliente . '%');
        }

        // Filtro de estado
        $estadoFiltro = $request->get('estado', 'pagada');
        if ($estadoFiltro && in_array($estadoFiltro, ['pendiente', 'pagada', 'rechazada'])) {
            $query->having('estatus_comision', $estadoFiltro);
        }

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('comision_agrupada.fecha_pedido', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('comision_agrupada.fecha_pedido', '<=', $request->fecha_fin);
        }

        $query->orderBy('fecha_desembolso', 'desc');

        // Totales generales para el vendedor
        $totalesBaseQuery = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->where('cv.correo_vendedor', $vendedorEmail);

        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $totalesBaseQuery->whereDate('pgd.created_at', '>=', $request->fecha_inicio);
        }
        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $totalesBaseQuery->whereDate('pgd.created_at', '<=', $request->fecha_fin);
        }

        $totales = $totalesBaseQuery->select(
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "pendiente" THEN cv.monto_comision ELSE 0 END) as total_pendiente'),
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "pagada" THEN cv.monto_comision ELSE 0 END) as total_pagada'),
            DB::raw('SUM(CASE WHEN cv.estatus_comision = "pagada" AND cv.id IN (SELECT comision_vendedores_id FROM comision_pagos WHERE recibido = 1) THEN cv.monto_comision ELSE 0 END) as total_confirmado')
        )->first();

        $comisiones = $query->paginate(15)->appends($request->all());

        $saldoCuenta = ComisionMovimiento::saldoActual($vendedorEmail);

        return view('comisiones.recibidas', [
            'comisiones' => $comisiones,
            'totalPendiente' => $totales->total_pendiente ?? 0,
            'totalPagada' => $totales->total_pagada ?? 0,
            'totalConfirmado' => $totales->total_confirmado ?? 0,
            'estadoFiltro' => $estadoFiltro,
            'saldoCuenta' => $saldoCuenta,
        ]);
    }

    /**
     * Get commissions included in a bulk payment group or individual pago_id.
     */
    public function getComisionesGrupo($identificador)
    {
        $vendedorEmail = auth()->user()->email;
        $query = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->join('pedidos as p', 'pgd.pedido_id', '=', 'p.id')
            ->leftJoin('comision_pagos as cp', 'cv.id', '=', 'cp.comision_vendedores_id')
            ->where('cv.correo_vendedor', $vendedorEmail);

        if (str_starts_with($identificador, 'BPG-')) {
            $query->where('cp.grupo_pago_id', $identificador);
        } else {
            $query->where('cv.pago_id', $identificador);
        }

        $items = $query->select(
            'cv.pago_id',
            'pgd.pedido_id',
            'p.descripcion as cliente',
            DB::raw('SUM(cv.monto_comision) as total_comision'),
            'pgd.created_at as fecha_pedido'
        )
            ->groupBy('cv.pago_id', 'pgd.pedido_id', 'p.descripcion', 'pgd.created_at')
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }

    /**
     * Get all commission IDs with current filters applied (for bulk selection)
     */
    public function getTodosIds(Request $request)
    {
        $vendedorEmail = auth()->user()->email;

        // Build the same query as in index() method
        $query = DB::connection('company')->table('comision_vendedores as cv')
            ->join('pago_grupo_detalles as pgd', 'cv.pago_id', '=', 'pgd.id')
            ->join('pedidos as p', 'pgd.pedido_id', '=', 'p.id')
            ->leftJoin('comision_pagos as cp', 'cv.id', '=', 'cp.comision_vendedores_id')
            ->where('cv.correo_vendedor', $vendedorEmail)
            ->whereNull('cp.comision_vendedores_id'); // Only unpaid commissions

        // Apply the same filters as in the index method
        if ($request->filled('vendedor')) {
            $query->where('cv.codigo_vendedor', $request->vendedor);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('pgd.created_at', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('pgd.created_at', '<=', $request->fecha_fin);
        }

        if ($request->filled('estados')) {
            $estados = explode(',', $request->estados);
            $query->whereIn('cv.estado', $estados);
        }

        if ($request->filled('rango_monto')) {
            $rango = explode('-', $request->rango_monto);
            if (count($rango) === 2) {
                $query->whereBetween('cv.monto_comision', [$rango[0], $rango[1]]);
            }
        }

        // Get all IDs with their data for selection
        $comisiones = $query->select(
            'cv.id',
            'cv.monto_comision',
            'cv.codigo_vendedor as vendedor'
        )->get();

        return response()->json([
            'success' => true,
            'comisiones' => $comisiones,
            'total' => $comisiones->count()
        ]);
    }

    // ─── Estado de Cuenta ────────────────────────────────────────────────────

    /**
     * API: saldo actual de un vendedor (para el modal de pago).
     */
    public function saldoVendedor($correo)
    {
        $correo = urldecode($correo);
        $saldo = ComisionMovimiento::saldoActual($correo);
        $vendedor = ComisionMovimiento::where('correo_vendedor', $correo)
            ->latest('id')
            ->value('nombre_vendedor');

        return response()->json([
            'success' => true,
            'correo' => $correo,
            'vendedor' => $vendedor,
            'saldo' => $saldo,
            // positivo = admin debe al vendedor | negativo = admin tiene crédito
        ]);
    }

    /**
     * Admin: listado de todos los vendedores con su saldo actual.
     */
    public function estadoCuenta(Request $request)
    {
        // Último movimiento por vendedor (para leer saldo_resultante)
        $subquery = ComisionMovimiento::selectRaw('correo_vendedor, MAX(id) as last_id')
            ->groupBy('correo_vendedor');

        $vendedores = ComisionMovimiento::joinSub($subquery, 'last', function ($join) {
            $join->on('comision_movimientos.correo_vendedor', '=', 'last.correo_vendedor')
                ->on('comision_movimientos.id', '=', 'last.last_id');
        })
            ->select(
                'comision_movimientos.correo_vendedor',
                'comision_movimientos.nombre_vendedor',
                'comision_movimientos.saldo_resultante as saldo'
            )
            ->orderBy('comision_movimientos.nombre_vendedor')
            ->get();

        // Totales devengados y pagados por vendedor
        $totales = ComisionMovimiento::selectRaw("
                correo_vendedor,
                SUM(CASE WHEN tipo = 'comision_devengada' THEN monto ELSE 0 END) as total_devengado,
                SUM(CASE WHEN tipo = 'pago_comision' THEN monto ELSE 0 END)      as total_pagado
            ")
            ->groupBy('correo_vendedor')
            ->get()
            ->keyBy('correo_vendedor');

        $vendedores = $vendedores->map(function ($v) use ($totales) {
            $t = $totales->get($v->correo_vendedor);
            $v->total_devengado = $t ? (float) $t->total_devengado : 0;
            $v->total_pagado = $t ? (float) $t->total_pagado : 0;
            return $v;
        });

        return view('comisiones.estado_cuenta', compact('vendedores'));
    }

    /**
     * Admin: ledger completo de un vendedor.
     */
    public function estadoCuentaVendedor(Request $request, $correo)
    {
        $correo = urldecode($correo);
        $movimientos = ComisionMovimiento::where('correo_vendedor', $correo)->orderBy('id')->get();
        $saldoActual = ComisionMovimiento::saldoActual($correo);
        $nombreVendedor = $movimientos->first()->nombre_vendedor ?? $correo;

        return view('comisiones.estado_cuenta_vendedor', compact('movimientos', 'saldoActual', 'nombreVendedor', 'correo'));
    }

    /**
     * Vendedor: su propio estado de cuenta.
     */
    public function miEstadoCuenta()
    {
        $correo = auth()->user()->email;
        $movimientos = ComisionMovimiento::where('correo_vendedor', $correo)->orderBy('id')->get();
        $saldoActual = ComisionMovimiento::saldoActual($correo);

        return view('comisiones.mi_estado_cuenta', compact('movimientos', 'saldoActual'));
    }
}


