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

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos,
            'cliente' => $this->getClienteByRif($rif),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCaja($request);
        $caja = null;

        DB::connection('company')->transaction(function () use ($data, &$caja) {
            $caja = LogisticaCaja::create([
                'codigo' => 'PENDIENTE',
                'public_token' => Str::uuid()->toString(),
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

        return view('admin.logistica.show', compact('caja'));
    }

    public function edit(int $id)
    {
        $caja = LogisticaCaja::with('items')->findOrFail($id);
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
        $data = $this->validateCaja($request, $caja->id);

        DB::connection('company')->transaction(function () use ($caja, $data) {
            $estatusAnterior = $caja->estatus;
            $nuevoEstatus = $data['estatus'] ?? $estatusAnterior;

            $caja->fill([
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

    public function destroy(int $id)
    {
        $caja = LogisticaCaja::findOrFail($id);

        DB::connection('company')->transaction(function () use ($caja) {
            $caja->delete();
        });

        return redirect()->route('admin.logistica.index')->with('success', 'Caja eliminada correctamente.');
    }

    public function label(int $id)
    {
        $caja = LogisticaCaja::with('items')->findOrFail($id);
        $publicUrl = route('logistica.public.show', ['token' => $caja->public_token]);

        return view('admin.logistica.label', compact('caja', 'publicUrl'));
    }

    public function publicShow(string $token)
    {
        $this->ensureCompanyConnectionDatabase();

        $caja = LogisticaCaja::with('items')->where('public_token', $token)->firstOrFail();

        $pedidoIds = $caja->items->pluck('pedido_id')->filter()->unique()->values();
        $facturas = $caja->items->pluck('factura_numero')->filter()->unique()->values();

        return view('logistica.public_show', compact('caja', 'pedidoIds', 'facturas'));
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

    private function validateCaja(Request $request, ?int $cajaId = null): array
    {
        $validated = $request->validate([
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
            'items.*.vendedor_codigo' => 'nullable|string|max:80',
            'items.*.vendedor_nombre' => 'nullable|string|max:150',
        ]);

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
        $rows = DB::connection('company')
            ->table('pedidos as p')
            ->join('pedido_detalle as pd', 'pd.pedido_id', '=', 'p.id')
            ->leftJoin('pedidos_facturas as pf', 'pf.pedido_id', '=', 'p.id')
            ->leftJoin('sdcloud.users as u', 'u.id', '=', 'p.user_id')
            ->selectRaw('p.id as pedido_id')
            ->selectRaw('p.fecha as pedido_fecha')
            ->selectRaw('p.seller_code as vendedor_codigo')
            ->selectRaw('CONCAT(COALESCE(u.name, ""), " ", COALESCE(u.last_name, "")) as vendedor_nombre')
            ->selectRaw('COALESCE(pf.factura, "") as factura_numero')
            ->selectRaw('pd.id as pedido_detalle_id, pd.codigo_inven, pd.inven_descr, pd.inven_unidad, pd.cantidad as cantidad_total')
            ->where('p.rif', $rif)
            ->where('p.estatus', 'APROBADO')
            ->orderBy('p.id')
            ->orderBy('pd.id')
            ->get();

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
}
