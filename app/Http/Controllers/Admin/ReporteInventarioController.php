<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductoBulto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteInventarioController extends Controller
{
    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Obtener todos los depósitos ordenados.
     */
    private function getDepositos(): \Illuminate\Support\Collection
    {
        return DB::connection('company')
            ->table('DEPOSITO')
            ->orderBy('CDEPOS')
            ->get();
    }

    /**
     * Obtener todos los grupos ordenados.
     */
    private function getGrupos(): \Illuminate\Support\Collection
    {
        return DB::connection('company')
            ->table('GRUPO')
            ->orderBy('CGRUPO')
            ->get();
    }

    /**
     * Construir query base de productos activos con filtros.
     */
    private function buildProductQuery(Request $request): \Illuminate\Database\Query\Builder
    {
        $q = DB::connection('company')
            ->table('INVEN as i')
            ->leftJoin('GRUPO as g', 'g.CGRUPO', '=', 'i.CGRUPO')
            ->select([
                'i.CODIGO', 'i.DESCR', 'i.BASE1', 'i.BASE2',
                'i.CGRUPO', 'i.SMIN', 'i.SMAX',
                'g.DGRUPO',
            ]);

        if ($request->filled('codigo_inicia')) {
            $q->where('i.CODIGO', 'like', $request->codigo_inicia . '%');
        } else {
            if ($request->filled('codigo_desde')) {
                $q->where('i.CODIGO', '>=', $request->codigo_desde);
            }
            if ($request->filled('codigo_hasta')) {
                $q->where('i.CODIGO', '<=', $request->codigo_hasta);
            }
        }
        if ($request->filled('nombre_desde')) {
            $q->where('i.DESCR', '>=', strtoupper($request->nombre_desde));
        }
        if ($request->filled('nombre_hasta')) {
            $q->where('i.DESCR', '<=', strtoupper($request->nombre_hasta));
        }
        if ($request->filled('cgrupo')) {
            $q->where('i.CGRUPO', $request->cgrupo);
        }

        $sortBy = $request->get('sort_by', 'codigo');
        $q->orderBy($sortBy === 'nombre' ? 'i.DESCR' : 'i.CODIGO');

        return $q;
    }

    /**
     * Enriquecer colección de productos con stock por depósito y datos de bulto.
     *
     * @param  \Illuminate\Support\Collection $productos
     * @param  \Illuminate\Support\Collection $depositos  (subset a mostrar)
     * @param  bool                           $soloConStock
     */
    private function enrichProductos($productos, $depositos, bool $soloConStock): \Illuminate\Support\Collection
    {
        if ($productos->isEmpty()) {
            return $productos;
        }

        $codigos = $productos->pluck('CODIGO')->toArray();

        // Stock por depósito
        $stocks = DB::connection('company')
            ->table('ARTDEPOS')
            ->whereIn('CODIGO', $codigos)
            ->select(['CODIGO', 'CDEPOS', DB::raw('SUM(EUNIDAD) as existencias')])
            ->groupBy('CODIGO', 'CDEPOS')
            ->get();

        $stockMatrix = [];
        foreach ($stocks as $s) {
            $stockMatrix[$s->CODIGO][$s->CDEPOS] = (float) $s->existencias;
        }

        // Inventario Inicial
        $inventariosIniciales = DB::connection('company')
            ->table('inventario_inicial')
            ->whereIn('codigo', $codigos)
            ->select('codigo', DB::raw('SUM(cantidad) as total_inicial'))
            ->groupBy('codigo')
            ->get();

        $invInicialMatrix = [];
        foreach ($inventariosIniciales as $inv) {
            $invInicialMatrix[$inv->codigo] = (float) $inv->total_inicial;
        }

        // Configuración de bultos
        $bultos = ProductoBulto::whereIn('codigo', $codigos)
            ->get()
            ->keyBy('codigo');

        return $productos->map(function ($prod) use ($stockMatrix, $depositos, $bultos, $invInicialMatrix) {
            $prod->stocks      = [];
            $prod->total_stock = 0;

            foreach ($depositos as $dep) {
                $s = $stockMatrix[$prod->CODIGO][$dep->CDEPOS] ?? 0;
                $prod->stocks[$dep->CDEPOS] = $s;
                $prod->total_stock         += $s;
            }

            $bultoCfg                = $bultos->get($prod->CODIGO);
            $prod->upb               = $bultoCfg ? (int) $bultoCfg->unidades_por_bulto : null;
            $prod->bultos_total      = ($prod->upb > 0) ? intdiv((int) $prod->total_stock, $prod->upb) : null;
            $prod->unidades_sobrantes = ($prod->upb > 0) ? ((int) $prod->total_stock % $prod->upb) : null;
            $prod->stock_critico     = $prod->SMIN > 0 && $prod->total_stock <= $prod->SMIN;
            
            // Agregar inventario inicial
            $prod->inventario_inicial = $invInicialMatrix[$prod->CODIGO] ?? 0;
            
            // Calcular porcentaje de venta (Inv. Inicial vs Existencias)
            $invInicial = $prod->inventario_inicial;
            $existencias = $prod->total_stock;
            $prod->porcentaje_venta = 0;
            
            if ($invInicial > 0) {
                // Si el inventario inicial > 0, calcular cuánto se ha vendido
                $vendido = $invInicial - $existencias;
                $prod->porcentaje_venta = ($vendido / $invInicial) * 100;
                // Limitar a 100% máximo y mostrar si es negativo (más existencias que Inicial)
                if ($prod->porcentaje_venta < 0) {
                    $prod->porcentaje_venta = 0;
                } elseif ($prod->porcentaje_venta > 100) {
                    $prod->porcentaje_venta = 100;
                }
            } elseif ($existencias > 0) {
                // Si no hay inventario inicial pero hay existencias, marcar como 100%
                $prod->porcentaje_venta = 100;
            }

            return $prod;
        })->when($soloConStock, fn($c) => $c->filter(fn($p) => $p->total_stock > 0))->values();
    }

    /**
     * Detectar grupos de variantes por talla (patrón "T=XX" al final de DESCR).
     */
    private function detectarVariantesTalla($productos): array
    {
        $sizeOrder  = ['XS' => 0, 'S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5, 'XXXL' => 6];
        // Detecta tallas alfabéticas (T=M, T=XL) y numéricas (T=35, T=40)
        // en cualquier posición de la descripción (ej: "...T=39 FP1" o "...T=M")
        $pattern   = '/\s+T=([A-Z0-9]{1,5})\b/i';
        $variantes = [];

        foreach ($productos as $prod) {
            if (!preg_match($pattern, $prod->DESCR, $m)) {
                continue;
            }
            // Recortar desde T=XX en adelante (incluye sufijos como "FP1")
            $base  = trim(preg_replace('/\s+T=[A-Z0-9]{1,5}\b.*/i', '', $prod->DESCR));
            $talla = strtoupper($m[1]);
            $key   = $prod->CGRUPO . '::' . $base;

            if (!isset($variantes[$key])) {
                $variantes[$key] = [
                    'nombre_base' => $base,
                    'cgrupo'      => $prod->CGRUPO,
                    'dgrupo'      => $prod->DGRUPO,
                    'tallas'      => [],
                ];
            }
            $variantes[$key]['tallas'][$talla] = $prod;
        }

        // Sólo grupos con 2 o más variantes
        $variantes = array_filter($variantes, fn($v) => count($v['tallas']) >= 2);

        // Ordenar tallas: primero las alfabéticas estándar, luego las numéricas (botas, etc.)
        foreach ($variantes as &$v) {
            uksort($v['tallas'], function ($a, $b) use ($sizeOrder) {
                $isNumA = is_numeric($a);
                $isNumB = is_numeric($b);
                if ($isNumA && $isNumB) {
                    return (float)$a <=> (float)$b;
                }
                return ($sizeOrder[$a] ?? 99) <=> ($sizeOrder[$b] ?? 99);
            });
        }

        return $variantes;
    }

    /**
     * Calcular totales por depósito y general.
     */
    private function calcularTotales($productos, $depositos): array
    {
        $totalesDeposito = [];
        foreach ($depositos as $dep) {
            $totalesDeposito[$dep->CDEPOS] = $productos->sum(fn($p) => $p->stocks[$dep->CDEPOS] ?? 0);
        }

        return [
            'por_deposito'   => $totalesDeposito,
            'general'        => $productos->sum('total_stock'),
            'valor_usd'      => $productos->sum(fn($p) => $p->total_stock * (float)$p->BASE1),
            'valor_bs'       => $productos->sum(fn($p) => $p->total_stock * (float)$p->BASE2),
            'con_stock'      => $productos->filter(fn($p) => $p->total_stock > 0)->count(),
            'criticos'       => $productos->filter(fn($p) => $p->stock_critico)->count(),
            'inventario_inicial' => $productos->sum('inventario_inicial'),
        ];
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    /**
     * Reporte completo: todos los depósitos.
     */
    public function index(Request $request)
    {
        $depositos     = $this->getDepositos();
        $grupos        = $this->getGrupos();
        $soloConStock  = $request->boolean('solo_con_stock');

        $productos = collect();
        $totales   = [];
        $variantes = [];

        if ($request->isMethod('get') && $request->anyFilled(['codigo_inicia', 'codigo_desde', 'codigo_hasta', 'nombre_desde', 'nombre_hasta', 'cgrupo', 'sort_by', 'solo_con_stock', 'buscar'])) {
            $rows      = $this->buildProductQuery($request)->get();
            $productos = $this->enrichProductos($rows, $depositos, $soloConStock);
            $totales   = $this->calcularTotales($productos, $depositos);
            $variantes = $this->detectarVariantesTalla($productos);
        }

        return view('admin.reportes.inventario', compact(
            'depositos', 'grupos', 'productos', 'totales', 'variantes'
        ));
    }

    /**
     * Reporte filtrado por un único depósito.
     */
    public function porDeposito(Request $request)
    {
        $todosDepositos = $this->getDepositos();
        $grupos         = $this->getGrupos();
        $soloConStock   = $request->boolean('solo_con_stock');

        $cdepos    = $request->get('cdepos');
        $deposito  = $cdepos ? $todosDepositos->firstWhere('CDEPOS', $cdepos) : null;

        // Mostrar solo el depósito seleccionado en la tabla
        $depositos = $deposito ? collect([$deposito]) : $todosDepositos;

        $productos = collect();
        $totales   = [];
        $variantes = [];

        if ($request->filled('buscar') || $request->filled('cdepos')) {
            $rows      = $this->buildProductQuery($request)->get();
            $productos = $this->enrichProductos($rows, $depositos, $soloConStock);
            $totales   = $this->calcularTotales($productos, $depositos);
            $variantes = $this->detectarVariantesTalla($productos);
        }

        return view('admin.reportes.inventario_deposito', compact(
            'todosDepositos', 'grupos', 'depositos', 'deposito', 'productos', 'totales', 'variantes'
        ));
    }

    /**
     * Dashboard gerencial con gráficos.
     */
    public function dashboard(Request $request)
    {
        $db     = DB::connection('company');
        $grupos = $this->getGrupos();

        // ── Distribución de stock por grupo (cantidad de unidades) ──────────
        $stockPorGrupo = $db->table('ARTDEPOS as ad')
            ->join('INVEN as i', 'i.CODIGO', '=', 'ad.CODIGO')
            ->join('GRUPO as g', 'g.CGRUPO', '=', 'i.CGRUPO')
            ->select(['g.DGRUPO', DB::raw('SUM(ad.EUNIDAD) as total')])
            ->groupBy('g.CGRUPO', 'g.DGRUPO')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();

        // ── Top 15 productos por valor en inventario (units × BASE2 USD) ──────
        $topValorUSD = $db->table('ARTDEPOS as ad')
            ->join('INVEN as i', 'i.CODIGO', '=', 'ad.CODIGO')
            ->where('i.BASE2', '>', 0)
            ->select([
                'i.CODIGO', 'i.DESCR',
                DB::raw('SUM(ad.EUNIDAD) as total_unidades'),
                DB::raw('SUM(ad.EUNIDAD) * i.BASE2 as valor_usd'),
            ])
            ->groupBy('i.CODIGO', 'i.DESCR', 'i.BASE2')
            ->having('total_unidades', '>', 0)
            ->orderByDesc('valor_usd')
            ->limit(15)
            ->get();

        // ── Stock total por depósito ─────────────────────────────────────────
        $stockPorDeposito = $db->table('ARTDEPOS as ad')
            ->join('DEPOSITO as d', 'd.CDEPOS', '=', 'ad.CDEPOS')
            ->select(['d.DDEPOS', 'ad.CDEPOS', DB::raw('SUM(ad.EUNIDAD) as total')])
            ->groupBy('ad.CDEPOS', 'd.DDEPOS')
            ->orderByDesc('total')
            ->get();

        // ── Productos con stock crítico (total ≤ SMIN) ───────────────────────
        $criticos = $db->table('ARTDEPOS as ad')
            ->join('INVEN as i', 'i.CODIGO', '=', 'ad.CODIGO')
            ->leftJoin('GRUPO as g', 'g.CGRUPO', '=', 'i.CGRUPO')
            ->where('i.SMIN', '>', 0)
            ->select([
                'i.CODIGO', 'i.DESCR', 'i.SMIN', 'i.SMAX', 'g.DGRUPO',
                DB::raw('SUM(ad.EUNIDAD) as total_stock'),
            ])
            ->groupBy('i.CODIGO', 'i.DESCR', 'i.SMIN', 'i.SMAX', 'g.DGRUPO')
            ->havingRaw('SUM(ad.EUNIDAD) <= i.SMIN')
            ->orderBy('total_stock')
            ->limit(50)
            ->get();

        // ── Todos los productos con inventario inicial ──────────────────────
        $productos = $db->table('INVEN as i')
            ->leftJoin('GRUPO as g', 'g.CGRUPO', '=', 'i.CGRUPO')
            ->select(['i.CODIGO', 'i.DESCR', 'i.BASE1', 'i.BASE2', 'i.SMIN', 'i.SMAX', 'i.CGRUPO', 'g.DGRUPO'])
            ->where('i.ACTIVO', 1)
            ->orderBy('i.DESCR')
            ->get();

        // Agregar inventario inicial a cada producto
        $inventariosIniciales = $db->table('inventario_inicial')
            ->select('codigo', DB::raw('SUM(cantidad) as total_inicial'))
            ->groupBy('codigo')
            ->get()
            ->keyBy('codigo');

        $productos = $productos->map(function ($prod) use ($inventariosIniciales) {
            $prod->inventario_inicial = isset($inventariosIniciales[$prod->CODIGO]) 
                ? (float)$inventariosIniciales[$prod->CODIGO]->total_inicial 
                : 0;
            return $prod;
        });

        // ── Resumen general ───────────────────────────────────────────────────
        $resumen = [
            'total_productos'  => $db->table('INVEN')->count(),
            'con_stock'        => $db->table('ARTDEPOS')->where('EUNIDAD', '>', 0)->distinct('CODIGO')->count('CODIGO'),
            'valor_total_usd'  => $db->table('ARTDEPOS as ad')
                ->join('INVEN as i', 'i.CODIGO', '=', 'ad.CODIGO')
                ->sum(DB::raw('ad.EUNIDAD * i.BASE2')),
            'total_criticos'   => $criticos->count(),
        ];

        // ── KPIs de Inventario Inicial ────────────────────────────────────────
        $productosConInvInicial = $productos->filter(fn($p) => $p->inventario_inicial > 0);
        $totalProductos = $productosConInvInicial->count();
        $totalValorUSD = $productosConInvInicial->sum(fn($p) => $p->inventario_inicial * $p->BASE2);
        $totalInvInicial = $productosConInvInicial->sum(fn($p) => $p->inventario_inicial);

        return view('admin.reportes.inventario_dashboard', compact(
            'stockPorGrupo', 'topValorUSD', 'stockPorDeposito', 'criticos', 'resumen', 'grupos', 'productos',
            'totalProductos', 'totalValorUSD', 'totalInvInicial'
        ));
    }
}
