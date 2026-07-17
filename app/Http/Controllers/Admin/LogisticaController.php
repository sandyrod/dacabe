<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogisticaCaja;
use App\Models\LogisticaCajaItem;
use App\Models\OrderClient;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LogisticaController extends Controller
{
    private const ESTATUS = ['ARMADA', 'EN_TRANSITO', 'ENTREGADA', 'CANCELADA'];

    public function dashboard()
    {
        $kpis = [
            'total' => LogisticaCaja::count(),
            'armadas' => LogisticaCaja::where('estatus', 'ARMADA')->count(),
            'transito' => LogisticaCaja::where('estatus', 'EN_TRANSITO')->count(),
            'entregadas' => LogisticaCaja::where('estatus', 'ENTREGADA')->count(),
            'canceladas' => LogisticaCaja::where('estatus', 'CANCELADA')->count(),
        ];

        $pendientes = $kpis['armadas'] + $kpis['transito'];
        $porcentajeEntrega = $kpis['total'] > 0
            ? round(($kpis['entregadas'] / $kpis['total']) * 100, 2)
            : 0;

        $ultimas = LogisticaCaja::withCount('items')->latest()->limit(10)->get();

        $entregasSemana = LogisticaCaja::query()
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('dia')
            ->get();

        return view('admin.logistica.dashboard', compact(
            'kpis',
            'pendientes',
            'porcentajeEntrega',
            'ultimas',
            'entregasSemana'
        ));
    }

    public function index(Request $request)
    {
        $query = LogisticaCaja::withCount('items')->latest();

        if ($request->filled('estatus') && in_array($request->estatus, self::ESTATUS, true)) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->where(function ($q) use ($term) {
                $q->where('codigo', 'like', '%' . $term . '%')
                    ->orWhere('cliente_nombre', 'like', '%' . $term . '%')
                    ->orWhere('cliente_rif', 'like', '%' . $term . '%')
                    ->orWhere('chofer_nombre', 'like', '%' . $term . '%')
                    ->orWhere('vendedor_nombre', 'like', '%' . $term . '%');
            });
        }

        $cajas = $query->paginate(20)->appends($request->query());

        return view('admin.logistica.index', compact('cajas'));
    }

    public function create()
    {
        $clientes = $this->getClientesConPedidosAprobados();
        $choferes = User::select('id', 'name', 'last_name')->orderBy('name')->get();

        return view('admin.logistica.create', [
            'caja' => new LogisticaCaja(),
            'clientes' => $clientes,
            'choferes' => $choferes,
            'estatusList' => self::ESTATUS,
            'selectedItems' => [],
            'pedidosDisponibles' => [],
        ]);
    }

    public function getPedidosCliente(Request $request, string $rif)
    {
        $cajaId = $request->query('caja_id');
        $pedidos = $this->pedidosDisponiblesPorCliente($rif, $cajaId ? (int) $cajaId : null);
        $cliente = $this->getClienteByRif($rif);
        $ultimaCaja = $this->getUltimaCajaCliente($rif, $cajaId ? (int) $cajaId : null);

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'cliente' => $cliente,
            'ultima_caja' => $ultimaCaja,
            'sugerido_bulto_codigo' => $this->buildSuggestedLoteCodigo($rif, optional($cliente)->codcli),
        ]);
    }

    public function autoCrearBultosSugeridos(Request $request)
    {
        $data = $request->validate([
            'bulto_codigo' => 'nullable|string|max:40',
            'cliente_rif' => 'required|string|max:40',
            'cliente_codcli' => 'nullable|string|max:40',
            'cliente_nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:80',
            'direccion_fiscal' => 'nullable|string|max:255',
            'direccion_entrega' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:120',
            'vendedor_user_id' => 'nullable|integer',
            'vendedor_nombre' => 'nullable|string|max:150',
            'chofer_user_id' => 'nullable|integer',
            'chofer_nombre' => 'nullable|string|max:150',
            'estatus' => 'required|in:' . implode(',', self::ESTATUS),
            'observaciones' => 'nullable|string|max:1000',
            'sugerencias' => 'required|array|min:1',
            'sugerencias.*.pedido_id' => 'required|integer',
            'sugerencias.*.pedido_detalle_id' => 'required|integer',
            'sugerencias.*.producto_codigo' => 'required|string|max:80',
            'sugerencias.*.producto_descripcion' => 'required|string|max:255',
            'sugerencias.*.unidad' => 'nullable|string|max:40',
            'sugerencias.*.factura_numero' => 'nullable|string|max:80',
            'sugerencias.*.vendedor_codigo' => 'nullable|string|max:80',
            'sugerencias.*.vendedor_nombre' => 'nullable|string|max:150',
            'sugerencias.*.cantidad_por_bulto' => 'required|numeric|min:0.01',
            'sugerencias.*.cantidad_disponible' => 'required|numeric|min:0.01',
            'sugerencias.*.bultos' => 'required|integer|min:1|max:200',
            'sugerencias.*.peso_gramos' => 'nullable|numeric|min:0',
            'sugerencias.*.unidades_por_bulto' => 'required|numeric|min:1',
        ]);

        $pedidosDisponibles = $this->pedidosDisponiblesPorCliente($data['cliente_rif'], null);
        $itemsDisponibles = [];
        foreach ($pedidosDisponibles as $pedido) {
            foreach ($pedido['items'] as $item) {
                $itemsDisponibles[(int) $item['pedido_detalle_id']] = [
                    'pedido_id' => (int) $pedido['pedido_id'],
                    'factura_numero' => $pedido['factura_numero'] ?? null,
                    'vendedor_codigo' => $pedido['vendedor_codigo'] ?? null,
                    'vendedor_nombre' => $pedido['vendedor_nombre'] ?? null,
                    'producto_codigo' => $item['producto_codigo'],
                    'producto_descripcion' => $item['producto_descripcion'],
                    'unidad' => $item['unidad'] ?? null,
                    'cantidad_disponible' => (float) ($item['cantidad_disponible'] ?? 0),
                    'unidades_por_bulto' => (float) ($item['unidades_por_bulto'] ?? 0),
                    'peso_gramos' => (float) ($item['peso_gramos'] ?? 0),
                ];
            }
        }

        $cajasPlan = [];
        foreach ($data['sugerencias'] as $sugerencia) {
            $detalleId = (int) $sugerencia['pedido_detalle_id'];
            if (!isset($itemsDisponibles[$detalleId])) {
                throw ValidationException::withMessages([
                    'sugerencias' => 'Una sugerencia ya no está disponible para armar cajas.',
                ]);
            }

            $disponible = $itemsDisponibles[$detalleId];
            $upbConfigurado = (float) $disponible['unidades_por_bulto'];
            $cantidadPorBulto = (float) $sugerencia['cantidad_por_bulto'];
            $cantidadDisponible = (float) $disponible['cantidad_disponible'];
            $bultos = (int) $sugerencia['bultos'];

            if ($upbConfigurado <= 0 || abs($upbConfigurado - $cantidadPorBulto) > 0.0001) {
                throw ValidationException::withMessages([
                    'sugerencias' => 'La configuración de unidades por bulto cambió para un producto. Recarga la pantalla.',
                ]);
            }

            $maxBultos = (int) floor($cantidadDisponible / $cantidadPorBulto);
            if ($maxBultos < 1 || $bultos > $maxBultos) {
                throw ValidationException::withMessages([
                    'sugerencias' => 'La cantidad disponible cambió y no permite crear los bultos sugeridos.',
                ]);
            }

            for ($i = 0; $i < $bultos; $i++) {
                $cajasPlan[] = [
                    'pedido_id' => $disponible['pedido_id'],
                    'pedido_detalle_id' => $detalleId,
                    'factura_numero' => $disponible['factura_numero'],
                    'producto_codigo' => $disponible['producto_codigo'],
                    'producto_descripcion' => $disponible['producto_descripcion'],
                    'unidad' => $disponible['unidad'],
                    'cantidad' => $cantidadPorBulto,
                    'peso_gramos' => $disponible['peso_gramos'],
                    'vendedor_codigo' => $disponible['vendedor_codigo'],
                    'vendedor_nombre' => $disponible['vendedor_nombre'],
                ];
            }
        }

        if (empty($cajasPlan)) {
            throw ValidationException::withMessages([
                'sugerencias' => 'No hay bultos válidos para crear.',
            ]);
        }

        $bultoCodigo = trim((string) ($data['bulto_codigo'] ?? ''));
        if ($bultoCodigo === '') {
            $bultoCodigo = $this->buildSuggestedLoteCodigo($data['cliente_rif'], $data['cliente_codcli'] ?? null);
        }

        if ($this->loteEstaCerrado($data['cliente_rif'], $bultoCodigo)) {
            throw ValidationException::withMessages([
                'bulto_codigo' => 'El lote indicado está cerrado. Debes usar un nuevo código de lote.',
            ]);
        }

        $created = [];
        DB::connection('company')->transaction(function () use ($data, $bultoCodigo, $cajasPlan, &$created) {
            $existentes = $this->getCajasDelLote($data['cliente_rif'], $bultoCodigo, false);
            $existingCount = $existentes->count();
            $newCount = count($cajasPlan);
            $finalTotal = $existingCount + $newCount;

            if ($existingCount > 0) {
                foreach ($existentes as $cajaExistente) {
                    $cajaExistente->bulto_total = $finalTotal;
                    $cajaExistente->updated_by = Auth::id();
                    $cajaExistente->save();
                }
            }

            $position = $existingCount + 1;
            foreach ($cajasPlan as $plan) {
                $item = [
                    'pedido_id' => $plan['pedido_id'],
                    'pedido_detalle_id' => $plan['pedido_detalle_id'],
                    'factura_numero' => $plan['factura_numero'],
                    'producto_codigo' => $plan['producto_codigo'],
                    'producto_descripcion' => $plan['producto_descripcion'],
                    'unidad' => $plan['unidad'],
                    'cantidad' => $plan['cantidad'],
                    'peso_gramos' => $plan['peso_gramos'],
                    'vendedor_codigo' => $plan['vendedor_codigo'],
                    'vendedor_nombre' => $plan['vendedor_nombre'],
                ];

                $caja = LogisticaCaja::create([
                    'codigo' => 'PENDIENTE',
                    'public_token' => Str::uuid()->toString(),
                    'bulto_codigo' => $bultoCodigo,
                    'bulto_posicion' => $position,
                    'bulto_total' => $finalTotal,
                    'peso_kg' => $this->calculatePesoKgByItems([$item]),
                    'cliente_rif' => $data['cliente_rif'],
                    'cliente_codcli' => $data['cliente_codcli'] ?? null,
                    'cliente_nombre' => $data['cliente_nombre'],
                    'telefono' => $data['telefono'] ?? null,
                    'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
                    'direccion_entrega' => $data['direccion_entrega'] ?? null,
                    'ciudad' => $data['ciudad'] ?? null,
                    'estado' => $data['estado'] ?? null,
                    'vendedor_user_id' => $data['vendedor_user_id'] ?? null,
                    'vendedor_nombre' => $data['vendedor_nombre'] ?? $plan['vendedor_nombre'] ?? null,
                    'chofer_user_id' => $data['chofer_user_id'] ?? null,
                    'chofer_nombre' => $data['chofer_nombre'] ?? null,
                    'estatus' => $data['estatus'] ?? 'ARMADA',
                    'fecha_armado' => now(),
                    'fecha_entrega' => ($data['estatus'] ?? 'ARMADA') === 'ENTREGADA' ? now() : null,
                    'observaciones' => $data['observaciones'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $caja->codigo = $this->buildCodigo((int) $caja->id);
                $caja->save();

                $this->persistItems($caja, [$item]);

                $created[] = $caja->id;
                $position++;
            }
        });

        return response()->json([
            'success' => true,
            'created' => count($created),
            'bulto_codigo' => $bultoCodigo,
            'message' => 'Se crearon ' . count($created) . ' caja(s) sugeridas para el lote ' . $bultoCodigo . '.',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCaja($request);

        if (!empty($data['bulto_codigo']) && $this->loteEstaCerrado($data['cliente_rif'], $data['bulto_codigo'])) {
            return back()->withInput()->with('error', 'El lote indicado está cerrado. No se pueden agregar más cajas.');
        }

        $caja = null;

        DB::connection('company')->transaction(function () use ($data, &$caja) {
            $caja = LogisticaCaja::create([
                'codigo' => 'PENDIENTE',
                'public_token' => Str::uuid()->toString(),
                'bulto_codigo' => $data['bulto_codigo'] ?? null,
                'bulto_posicion' => $data['bulto_posicion'] ?? null,
                'bulto_total' => $data['bulto_total'] ?? null,
                'peso_kg' => $this->resolvePesoKg($data),
                'cliente_rif' => $data['cliente_rif'],
                'cliente_codcli' => $data['cliente_codcli'] ?? null,
                'cliente_nombre' => $data['cliente_nombre'],
                'telefono' => $data['telefono'] ?? null,
                'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'ciudad' => $data['ciudad'] ?? null,
                'estado' => $data['estado'] ?? null,
                'vendedor_user_id' => $data['vendedor_user_id'] ?? null,
                'vendedor_nombre' => $data['vendedor_nombre'] ?? null,
                'chofer_user_id' => $data['chofer_user_id'] ?? null,
                'chofer_nombre' => $data['chofer_nombre'] ?? null,
                'estatus' => $data['estatus'] ?? 'ARMADA',
                'fecha_armado' => now(),
                'fecha_entrega' => ($data['estatus'] ?? 'ARMADA') === 'ENTREGADA' ? now() : null,
                'observaciones' => $data['observaciones'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $caja->codigo = $this->buildCodigo((int) $caja->id);
            $caja->save();

            $this->persistItems($caja, $data['items']);
        });

        return redirect()->route('admin.logistica.index')->with('success', 'Caja creada correctamente.');
    }

    public function show(int $id)
    {
        $caja = LogisticaCaja::with('items')->findOrFail($id);
        $loteCajas = $this->getCajasDelLote($caja->cliente_rif, $caja->bulto_codigo, true);

        return view('admin.logistica.show', compact('caja', 'loteCajas'));
    }

    public function edit(int $id)
    {
        $caja = LogisticaCaja::with('items')->findOrFail($id);
        if ($this->cajaEstaBloqueadaPorLoteCerrado($caja)) {
            return redirect()->route('admin.logistica.show', $caja->id)
                ->with('error', 'La caja pertenece a un lote cerrado y no puede editarse.');
        }

        $clientes = $this->getClientesConPedidosAprobados();
        $choferes = User::select('id', 'name', 'last_name')->orderBy('name')->get();
        $pedidosDisponibles = $this->pedidosDisponiblesPorCliente($caja->cliente_rif, $caja->id);

        $selectedItems = collect($caja->items)->mapWithKeys(function ($item) {
            return [$item->pedido_detalle_id => [
                'cantidad' => (float) $item->cantidad,
                'pedido_id' => (int) $item->pedido_id,
            ]];
        })->toArray();

        return view('admin.logistica.edit', compact(
            'caja',
            'clientes',
            'choferes',
            'pedidosDisponibles',
            'selectedItems'
        ) + ['estatusList' => self::ESTATUS]);
    }

    public function update(Request $request, int $id)
    {
        $caja = LogisticaCaja::findOrFail($id);
        if ($this->cajaEstaBloqueadaPorLoteCerrado($caja)) {
            return back()->withInput()->with('error', 'La caja pertenece a un lote cerrado y no puede modificarse.');
        }

        $data = $this->validateCaja($request, $caja->id);

        if (!empty($data['bulto_codigo']) && $this->loteEstaCerrado($data['cliente_rif'], $data['bulto_codigo'], $caja->id)) {
            return back()->withInput()->with('error', 'No puedes mover esta caja a un lote cerrado.');
        }

        DB::connection('company')->transaction(function () use ($caja, $data) {
            $estatusAnterior = $caja->estatus;
            $nuevoEstatus = $data['estatus'] ?? $estatusAnterior;

            $caja->fill([
                'bulto_codigo' => $data['bulto_codigo'] ?? null,
                'bulto_posicion' => $data['bulto_posicion'] ?? null,
                'bulto_total' => $data['bulto_total'] ?? null,
                'peso_kg' => $this->resolvePesoKg($data),
                'cliente_rif' => $data['cliente_rif'],
                'cliente_codcli' => $data['cliente_codcli'] ?? null,
                'cliente_nombre' => $data['cliente_nombre'],
                'telefono' => $data['telefono'] ?? null,
                'direccion_fiscal' => $data['direccion_fiscal'] ?? null,
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'ciudad' => $data['ciudad'] ?? null,
                'estado' => $data['estado'] ?? null,
                'vendedor_user_id' => $data['vendedor_user_id'] ?? null,
                'vendedor_nombre' => $data['vendedor_nombre'] ?? null,
                'chofer_user_id' => $data['chofer_user_id'] ?? null,
                'chofer_nombre' => $data['chofer_nombre'] ?? null,
                'estatus' => $nuevoEstatus,
                'observaciones' => $data['observaciones'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            if ($estatusAnterior !== 'ENTREGADA' && $nuevoEstatus === 'ENTREGADA') {
                $caja->fecha_entrega = now();
            }

            if ($nuevoEstatus !== 'ENTREGADA') {
                $caja->fecha_entrega = null;
            }

            $caja->save();

            $caja->items()->delete();
            $this->persistItems($caja, $data['items']);
        });

        return redirect()->route('admin.logistica.index')->with('success', 'Caja actualizada correctamente.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'estatus' => 'required|in:' . implode(',', self::ESTATUS),
        ]);

        $caja = LogisticaCaja::findOrFail($id);

        $caja->estatus = $request->estatus;
        $caja->updated_by = Auth::id();

        if ($request->estatus === 'ENTREGADA') {
            $caja->fecha_entrega = now();
        } elseif ($caja->fecha_entrega !== null) {
            $caja->fecha_entrega = null;
        }

        $caja->save();

        return redirect()->route('admin.logistica.index')->with('success', 'Estatus actualizado.');
    }

    public function updateLoteStatus(Request $request)
    {
        $data = $request->validate([
            'cliente_rif' => 'required|string|max:40',
            'bulto_codigo' => 'required|string|max:40',
            'estatus' => 'required|in:' . implode(',', self::ESTATUS),
        ]);

        $cajas = LogisticaCaja::query()
            ->where('cliente_rif', $data['cliente_rif'])
            ->whereRaw('LOWER(TRIM(COALESCE(bulto_codigo, ""))) = ?', [strtolower(trim($data['bulto_codigo']))])
            ->orderBy('id')
            ->get();

        if ($cajas->isEmpty()) {
            return back()->with('error', 'No se encontraron cajas para ese lote.');
        }

        DB::connection('company')->transaction(function () use ($cajas, $data) {
            foreach ($cajas as $caja) {
                $caja->estatus = $data['estatus'];
                $caja->updated_by = Auth::id();
                $caja->fecha_entrega = $data['estatus'] === 'ENTREGADA' ? now() : null;
                $caja->save();
            }
        });

        return back()->with('success', 'Estatus del lote actualizado en ' . $cajas->count() . ' caja(s).');
    }

    public function destroy(int $id)
    {
        $caja = LogisticaCaja::findOrFail($id);
        if ($this->cajaEstaBloqueadaPorLoteCerrado($caja)) {
            return back()->with('error', 'La caja pertenece a un lote cerrado y no puede eliminarse.');
        }

        DB::connection('company')->transaction(function () use ($caja) {
            $caja->delete();
        });

        return redirect()->route('admin.logistica.index')->with('success', 'Caja eliminada correctamente.');
    }

    public function label(int $id)
    {
        $caja = LogisticaCaja::with('items')->findOrFail($id);
        $publicUrl = route('logistica.public.show', ['token' => $caja->public_token]);
        $pedidoIds = [];
        $facturas = [];

        foreach ($caja->items as $item) {
            if (!empty($item->pedido_id)) {
                $pedidoIds[(string) $item->pedido_id] = (string) $item->pedido_id;
            }

            if (!empty($item->factura_numero)) {
                $facturas[(string) $item->factura_numero] = (string) $item->factura_numero;
            }
        }

        $pedidoIds = array_values($pedidoIds);
        $facturas = array_values($facturas);
        $bultoEtiqueta = $caja->bulto_total && $caja->bulto_posicion
            ? $caja->bulto_posicion . '/' . $caja->bulto_total
            : null;

        return view('admin.logistica.label', compact('caja', 'publicUrl', 'pedidoIds', 'facturas', 'bultoEtiqueta'));
    }

    public function loteEtiquetas(string $clienteRif, string $bultoCodigo)
    {
        $cajas = $this->getCajasDelLote($clienteRif, $bultoCodigo, true);

        if ($cajas->isEmpty()) {
            abort(404, 'No se encontraron cajas para el lote indicado.');
        }

        $cliente = $cajas->first();

        return view('admin.logistica.lote_etiquetas', [
            'cajas' => $cajas,
            'clienteRif' => $clienteRif,
            'bultoCodigo' => $bultoCodigo,
            'loteCerradoAt' => $cliente->lote_cerrado_at,
            'loteCerradoPor' => $cliente->lote_cerrado_por,
        ]);
    }

    public function cerrarLoteCliente(Request $request)
    {
        $data = $request->validate([
            'cliente_rif' => 'required|string|max:40',
            'bulto_codigo' => 'required|string|max:40',
        ]);

        $cajas = $this->getCajasDelLote($data['cliente_rif'], $data['bulto_codigo'], false);

        if ($cajas->isEmpty()) {
            return back()->with('error', 'No hay cajas activas para cerrar en este lote.');
        }

        if ($cajas->every(function ($caja) { return !empty($caja->lote_cerrado_at); })) {
            return redirect()->route('admin.logistica.lote.etiquetas', [
                'clienteRif' => $data['cliente_rif'],
                'bultoCodigo' => $data['bulto_codigo'],
            ])->with('success', 'El lote ya estaba cerrado.');
        }

        DB::connection('company')->transaction(function () use ($cajas) {
            foreach ($cajas as $caja) {
                $caja->lote_cerrado_at = now();
                $caja->lote_cerrado_por = Auth::id();
                $caja->updated_by = Auth::id();
                $caja->save();
            }
        });

        return redirect()->route('admin.logistica.lote.etiquetas', [
            'clienteRif' => $data['cliente_rif'],
            'bultoCodigo' => $data['bulto_codigo'],
        ])->with('success', 'Lote cerrado correctamente. Puedes imprimir todas las etiquetas ahora.');
    }

    public function publicShow(string $token)
    {
        $this->ensureCompanyConnectionDatabase();

        $caja = LogisticaCaja::with('items')->where('public_token', $token)->firstOrFail();

        $pedidoIds = [];
        $facturas = [];

        foreach ($caja->items as $item) {
            if (!empty($item->pedido_id)) {
                $pedidoIds[(string) $item->pedido_id] = (string) $item->pedido_id;
            }

            if (!empty($item->factura_numero)) {
                $facturas[(string) $item->factura_numero] = (string) $item->factura_numero;
            }
        }

        $pedidoIds = array_values($pedidoIds);
        $facturas = array_values($facturas);
        $bultoEtiqueta = $caja->bulto_total && $caja->bulto_posicion
            ? $caja->bulto_posicion . '/' . $caja->bulto_total
            : null;

        return view('logistica.public_show', compact('caja', 'pedidoIds', 'facturas', 'bultoEtiqueta'));
    }

    private function ensureCompanyConnectionDatabase(): void
    {
        $currentDb = DB::connection('company')->getDatabaseName();
        if (!empty($currentDb)) {
            return;
        }

        // Fallback para vistas públicas (QR) sin sesión de usuario.
        Config::set('database.connections.company.database', 'dacabe');
        DB::purge('company');
        DB::reconnect('company');
    }

    private function getCajasDelLote(string $clienteRif, ?string $bultoCodigo, bool $withItems = false)
    {
        $query = LogisticaCaja::query()
            ->where('cliente_rif', $clienteRif)
            ->when($bultoCodigo !== null && trim($bultoCodigo) !== '', function ($q) use ($bultoCodigo) {
                $q->whereRaw('LOWER(TRIM(COALESCE(bulto_codigo, ""))) = ?', [strtolower(trim($bultoCodigo))]);
            })
            ->where('estatus', '!=', 'CANCELADA')
            ->orderByRaw('COALESCE(bulto_posicion, 0) ASC')
            ->orderBy('id');

        if ($withItems) {
            $query->with('items');
        }

        return $query->get();
    }

    private function loteEstaCerrado(string $clienteRif, string $bultoCodigo, ?int $excludeCajaId = null): bool
    {
        return LogisticaCaja::query()
            ->where('cliente_rif', $clienteRif)
            ->whereRaw('LOWER(TRIM(COALESCE(bulto_codigo, ""))) = ?', [strtolower(trim($bultoCodigo))])
            ->when($excludeCajaId, function ($q) use ($excludeCajaId) {
                $q->where('id', '!=', $excludeCajaId);
            })
            ->whereNotNull('lote_cerrado_at')
            ->where('estatus', '!=', 'CANCELADA')
            ->exists();
    }

    private function cajaEstaBloqueadaPorLoteCerrado(LogisticaCaja $caja): bool
    {
        if (!empty($caja->lote_cerrado_at)) {
            return true;
        }

        if (empty($caja->bulto_codigo)) {
            return false;
        }

        return $this->loteEstaCerrado($caja->cliente_rif, $caja->bulto_codigo, $caja->id);
    }

    private function validateCaja(Request $request, ?int $cajaId = null): array
    {
        $validated = $request->validate([
            'bulto_codigo' => 'nullable|string|max:40|required_with:bulto_posicion,bulto_total',
            'bulto_posicion' => 'nullable|integer|min:1|required_with:bulto_codigo,bulto_total',
            'bulto_total' => 'nullable|integer|min:1|required_with:bulto_codigo,bulto_posicion',
            'peso_kg' => 'nullable|numeric|min:0',
            'cliente_rif' => 'required|string|max:40',
            'cliente_codcli' => 'nullable|string|max:40',
            'cliente_nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:80',
            'direccion_fiscal' => 'nullable|string|max:255',
            'direccion_entrega' => 'required|string|max:255',
            'ciudad' => 'nullable|string|max:120',
            'estado' => 'nullable|string|max:120',
            'vendedor_user_id' => 'nullable|integer',
            'vendedor_nombre' => 'nullable|string|max:150',
            'chofer_user_id' => 'nullable|integer',
            'chofer_nombre' => 'nullable|string|max:150',
            'estatus' => 'required|in:' . implode(',', self::ESTATUS),
            'observaciones' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.pedido_id' => 'required|integer',
            'items.*.pedido_detalle_id' => 'nullable|integer',
            'items.*.factura_numero' => 'nullable|string|max:80',
            'items.*.producto_codigo' => 'required|string|max:80',
            'items.*.producto_descripcion' => 'required|string|max:255',
            'items.*.unidad' => 'nullable|string|max:40',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.peso_gramos' => 'nullable|numeric|min:0',
            'items.*.vendedor_codigo' => 'nullable|string|max:80',
            'items.*.vendedor_nombre' => 'nullable|string|max:150',
        ]);

        if (!empty($validated['bulto_posicion']) && !empty($validated['bulto_total']) && (int) $validated['bulto_posicion'] > (int) $validated['bulto_total']) {
            throw ValidationException::withMessages([
                'bulto_posicion' => 'El número de la caja dentro del bulto no puede ser mayor que el total de cajas del bulto.',
            ]);
        }

        $this->validateDisponibilidadItems($validated['items'], $cajaId);

        return $validated;
    }

    private function validateDisponibilidadItems(array $items, ?int $cajaId = null): void
    {
        $detalleIds = collect($items)
            ->pluck('pedido_detalle_id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        if (empty($detalleIds)) {
            return;
        }

        $detalles = DB::connection('company')
            ->table('pedido_detalle')
            ->whereIn('id', $detalleIds)
            ->pluck('cantidad', 'id');

        $yaEmpacado = DB::connection('company')
            ->table('logistica_caja_items as lci')
            ->join('logistica_cajas as lc', 'lc.id', '=', 'lci.caja_id')
            ->whereIn('lci.pedido_detalle_id', $detalleIds)
            ->where('lc.estatus', '!=', 'CANCELADA')
            ->when($cajaId, function ($q) use ($cajaId) {
                $q->where('lc.id', '!=', $cajaId);
            })
            ->groupBy('lci.pedido_detalle_id')
            ->selectRaw('lci.pedido_detalle_id, SUM(lci.cantidad) as qty')
            ->pluck('qty', 'pedido_detalle_id');

        foreach ($items as $index => $item) {
            $detalleId = (int) ($item['pedido_detalle_id'] ?? 0);
            if ($detalleId <= 0) {
                continue;
            }

            $totalDetalle = (float) ($detalles[$detalleId] ?? 0);
            $ya = (float) ($yaEmpacado[$detalleId] ?? 0);
            $disponible = $totalDetalle - $ya;
            $solicitado = (float) $item['cantidad'];

            if ($solicitado > $disponible + 0.0001) {
                $humanIndex = $index + 1;
                throw ValidationException::withMessages([
                    "items.{$index}.cantidad" => "La cantidad del item {$humanIndex} excede la disponible para el pedido detalle {$detalleId}.",
                ]);
            }
        }
    }

    private function persistItems(LogisticaCaja $caja, array $items): void
    {
        foreach ($items as $item) {
            LogisticaCajaItem::create([
                'caja_id' => $caja->id,
                'pedido_id' => (int) $item['pedido_id'],
                'pedido_detalle_id' => !empty($item['pedido_detalle_id']) ? (int) $item['pedido_detalle_id'] : null,
                'factura_numero' => $item['factura_numero'] ?? null,
                'producto_codigo' => $item['producto_codigo'],
                'producto_descripcion' => $item['producto_descripcion'],
                'unidad' => $item['unidad'] ?? null,
                'cantidad' => (float) $item['cantidad'],
                'vendedor_codigo' => $item['vendedor_codigo'] ?? null,
                'vendedor_nombre' => $item['vendedor_nombre'] ?? null,
            ]);
        }
    }

    private function resolvePesoKg(array $data): ?float
    {
        if (array_key_exists('peso_kg', $data) && $data['peso_kg'] !== null && $data['peso_kg'] !== '') {
            return round((float) $data['peso_kg'], 3);
        }

        return $this->calculatePesoKgByItems($data['items'] ?? []);
    }

    private function calculatePesoKgByItems(array $items): ?float
    {
        if (empty($items)) {
            return null;
        }

        $codigos = collect($items)
            ->pluck('producto_codigo')
            ->filter()
            ->map(fn ($code) => trim((string) $code))
            ->unique()
            ->values()
            ->all();

        if (empty($codigos)) {
            return null;
        }

        $weights = collect();
        if ($this->invenInformacionHasColumn('peso_gramos')) {
            $weights = DB::connection('company')
                ->table('inven_informacion')
                ->whereIn('codigo', $codigos)
                ->pluck('peso_gramos', 'codigo');
        }

        $totalGramos = 0.0;
        foreach ($items as $item) {
            $codigo = trim((string) ($item['producto_codigo'] ?? ''));
            if ($codigo === '') {
                continue;
            }

            $cantidad = (float) ($item['cantidad'] ?? 0);
            $pesoGramos = (float) ($weights[$codigo] ?? ($item['peso_gramos'] ?? 0));
            if ($cantidad <= 0 || $pesoGramos <= 0) {
                continue;
            }

            $totalGramos += ($cantidad * $pesoGramos);
        }

        if ($totalGramos <= 0) {
            return null;
        }

        return round($totalGramos / 1000, 3);
    }

    private function buildCodigo(int $id): string
    {
        return 'CJ-' . now()->format('Ymd') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    private function getClientesConPedidosAprobados()
    {
        $clienteSelect = $this->clienteSelectRaw();

        return OrderClient::query()
            ->from('CLIENTE as c')
            ->selectRaw('c.RIF as rif, c.CODCLI as codcli, c.NOMBRE as nombre, ' . $clienteSelect)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('pedidos as p')
                    ->whereRaw('BINARY p.rif = BINARY c.RIF')
                    ->where('p.estatus', 'APROBADO');
            })
            ->orderBy('c.NOMBRE')
            ->limit(2000)
            ->get();
    }

    private function getClienteByRif(string $rif): ?object
    {
        $clienteSelect = $this->clienteSelectRaw();

        return DB::connection('company')
            ->table('CLIENTE as c')
            ->selectRaw('c.RIF as rif, c.CODCLI as codcli, c.NOMBRE as nombre, ' . $clienteSelect)
            ->where('c.RIF', $rif)
            ->first();
    }

    private function pedidosDisponiblesPorCliente(string $rif, ?int $cajaId = null): array
    {
        $invenFields = $this->invenInfoFieldsRaw();
        $hasProductoBultos = $this->companyTableExists('producto_bultos');

        $query = DB::connection('company')
            ->table('pedidos as p')
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->leftJoin('pedidos_facturas as pf', 'pf.pedido_id', '=', 'p.id')
            ->leftJoin('inven_informacion as ii', function ($join) {
                $join->on(DB::raw('BINARY ii.codigo'), '=', DB::raw('BINARY pd.codigo_inven'));
            })
            ->leftJoin('sdcloud.users as u', 'u.id', '=', 'p.user_id')
            ->selectRaw('p.id as pedido_id')
            ->selectRaw('p.fecha as pedido_fecha')
            ->selectRaw('p.seller_code as vendedor_codigo')
            ->selectRaw('CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, "")) as vendedor_nombre')
            ->selectRaw('COALESCE(pf.factura, "") as factura_numero')
            ->selectRaw('pd.id as pedido_detalle_id, pd.codigo_inven, pd.inven_descr, pd.inven_unidad, pd.cantidad as cantidad_total')
            ->selectRaw($invenFields['peso_gramos'])
            ->selectRaw($invenFields['requiere_etiqueta'])
            ->where('p.rif', $rif)
            ->where('p.estatus', 'APROBADO')
            ->orderBy('p.id')
            ->orderBy('pd.id');

        if ($hasProductoBultos) {
            $query->leftJoin('producto_bultos as pb', function ($join) {
                $join->on(DB::raw('BINARY pb.codigo'), '=', DB::raw('BINARY pd.codigo_inven'));
            });
            $query->selectRaw('COALESCE(pb.unidades_por_bulto, 0) as unidades_por_bulto');
        } else {
            $query->selectRaw('0 as unidades_por_bulto');
        }

        $rows = $query->get();

        $detalleIds = $rows->pluck('pedido_detalle_id')->unique()->values()->all();

        $yaEmpacado = DB::connection('company')
            ->table('logistica_caja_items as lci')
            ->join('logistica_cajas as lc', 'lc.id', '=', 'lci.caja_id')
            ->whereIn('lci.pedido_detalle_id', $detalleIds)
            ->where('lc.estatus', '!=', 'CANCELADA')
            ->when($cajaId, function ($q) use ($cajaId) {
                $q->where('lc.id', '!=', $cajaId);
            })
            ->groupBy('lci.pedido_detalle_id')
            ->selectRaw('lci.pedido_detalle_id, SUM(lci.cantidad) as qty')
            ->pluck('qty', 'pedido_detalle_id');

        $pedidos = [];

        foreach ($rows as $row) {
            if ((int) ($row->requiere_etiqueta ?? 1) !== 1) {
                continue;
            }

            $detalleId = (int) $row->pedido_detalle_id;
            $total = (float) $row->cantidad_total;
            $packed = (float) ($yaEmpacado[$detalleId] ?? 0);
            $disponible = max(0, $total - $packed);

            if ($disponible <= 0) {
                continue;
            }

            $pedidoId = (int) $row->pedido_id;
            if (!isset($pedidos[$pedidoId])) {
                $pedidos[$pedidoId] = [
                    'pedido_id' => $pedidoId,
                    'pedido_fecha' => $row->pedido_fecha ? Carbon::parse($row->pedido_fecha)->format('d/m/Y') : null,
                    'factura_numero' => $row->factura_numero,
                    'vendedor_codigo' => $row->vendedor_codigo,
                    'vendedor_nombre' => trim($row->vendedor_nombre),
                    'items' => [],
                ];
            }

            $pedidos[$pedidoId]['items'][] = [
                'pedido_detalle_id' => $detalleId,
                'producto_codigo' => $row->codigo_inven,
                'producto_descripcion' => $row->inven_descr,
                'unidad' => $row->inven_unidad,
                'unidades_por_bulto' => (float) ($row->unidades_por_bulto ?? 0),
                'peso_gramos' => (float) ($row->peso_gramos ?? 0),
                'cantidad_total' => $total,
                'cantidad_disponible' => $disponible,
            ];
        }

        return array_values($pedidos);
    }

    private function clienteSelectRaw(): string
    {
        $available = collect(DB::connection('company')->select('SHOW COLUMNS FROM CLIENTE'))
            ->pluck('Field')
            ->map(fn ($field) => strtoupper((string) $field))
            ->all();

        $pick = function (array $candidates, string $alias) use ($available) {
            foreach ($candidates as $field) {
                if (in_array($field, $available, true)) {
                    return "c.{$field} as {$alias}";
                }
            }

            return "'' as {$alias}";
        };

        return implode(', ', [
            $pick(['TELEFONO', 'TELEFONO1', 'CELULAR'], 'telefono'),
            $pick(['DIRECCION', 'DIRECCION1', 'DIRFISCAL'], 'direccion_fiscal'),
            $pick(['DIRECCION_ENTREGA', 'DIR_ENTREGA', 'DIRECCION'], 'direccion_entrega'),
            $pick(['CIUDAD', 'MUNICIPIO'], 'ciudad'),
            $pick(['ESTADO'], 'estado'),
        ]);
    }

    private function getUltimaCajaCliente(string $rif, ?int $excludeCajaId = null): ?object
    {
        $query = LogisticaCaja::query()
            ->select('id', 'ciudad', 'estado', 'direccion_entrega')
            ->where('cliente_rif', $rif)
            ->where('estatus', '!=', 'CANCELADA');

        if ($excludeCajaId !== null) {
            $query->where('id', '!=', $excludeCajaId);
        }

        return $query->orderByDesc('id')->first();
    }

    private function buildSuggestedLoteCodigo(string $clienteRif, ?string $clienteCodcli = null): string
    {
        $baseRaw = $clienteCodcli ?: $clienteRif;
        $base = preg_replace('/[^a-zA-Z0-9]/', '', (string) $baseRaw);
        $base = strtoupper((string) $base);
        if ($base === '') {
            $base = 'CLIENTE';
        }

        $prefix = 'LOT-' . $base . '-' . now()->format('Ymd');

        $existing = LogisticaCaja::query()
            ->where('cliente_rif', $clienteRif)
            ->whereRaw('LOWER(TRIM(COALESCE(bulto_codigo, ""))) LIKE ?', [strtolower($prefix) . '%'])
            ->pluck('bulto_codigo')
            ->filter()
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->all();

        if (!in_array(strtoupper($prefix), $existing, true)) {
            return $prefix;
        }

        for ($i = 2; $i <= 999; $i++) {
            $candidate = $prefix . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            if (!in_array(strtoupper($candidate), $existing, true)) {
                return $candidate;
            }
        }

        return $prefix . '-' . now()->format('His');
    }

    private function invenInfoFieldsRaw(): array
    {
        $available = collect(DB::connection('company')->select('SHOW COLUMNS FROM inven_informacion'))
            ->pluck('Field')
            ->map(fn ($field) => strtolower((string) $field))
            ->all();

        $hasPeso = in_array('peso_gramos', $available, true);
        $hasEtiqueta = in_array('requiere_etiqueta', $available, true);

        return [
            'peso_gramos' => $hasPeso
                ? 'COALESCE(ii.peso_gramos, 0) as peso_gramos'
                : '0 as peso_gramos',
            'requiere_etiqueta' => $hasEtiqueta
                ? 'COALESCE(ii.requiere_etiqueta, 1) as requiere_etiqueta'
                : '1 as requiere_etiqueta',
        ];
    }

    private function invenInformacionHasColumn(string $column): bool
    {
        $fields = collect(DB::connection('company')->select('SHOW COLUMNS FROM inven_informacion'))
            ->pluck('Field')
            ->map(fn ($field) => strtolower((string) $field))
            ->all();

        return in_array(strtolower($column), $fields, true);
    }

    private function companyTableExists(string $table): bool
    {
        $escaped = str_replace('`', '``', $table);
        $rows = DB::connection('company')->select("SHOW TABLES LIKE '{$escaped}'");

        return !empty($rows);
    }
}
