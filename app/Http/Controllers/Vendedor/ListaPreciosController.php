<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\InvenFoto;
use App\Models\Vendedor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListaPreciosController extends Controller
{
    public function index(Request $request)
    {
        [$depositos, $depositoSeleccionado] = $this->resolveDepositos($request);
        $recargo = $this->getRecargoVendedor();
        $pricing = $this->getPricingContext($recargo);
        $query = $this->buildQuery($request, $depositoSeleccionado, $pricing);
        $productos = $query->paginate(24)->appends($request->query());
        $this->attachPhotos(collect($productos->items()));

        $grupos = DB::connection('company')->table('GRUPO')->orderBy('DGRUPO')->get();

        return view('vendedor.lista_precios.index', compact(
            'productos', 'depositos', 'depositoSeleccionado', 'grupos', 'recargo', 'pricing'
        ));
    }

    public function pdf(Request $request)
    {
        [$depositos, $depositoSeleccionado] = $this->resolveDepositos($request);
        $recargo = $this->getRecargoVendedor();
        $pricing = $this->getPricingContext($recargo);
        $productos = $this->buildQuery($request, $depositoSeleccionado, $pricing)->get();
        $this->attachPhotos($productos);

        $deposito = $depositos->firstWhere('CDEPOS', $depositoSeleccionado);
        $company = auth()->user()->company;
        $filtros = $request->only([
            'buscar', 'cgrupo', 'stock', 'precio_desde', 'precio_hasta', 'orden',
        ]);

        return Pdf::loadView('vendedor.lista_precios.pdf', compact(
            'productos', 'deposito', 'company', 'recargo', 'filtros'
        ))->setPaper('letter', 'portrait')
            ->download('lista-precios-' . now()->format('Y-m-d-His') . '.pdf');
    }

    private function resolveDepositos(Request $request): array
    {
        $query = DB::connection('company')->table('DEPOSITO as d')
            ->select('d.CDEPOS', 'd.DDEPOS')
            ->orderBy('d.DDEPOS');

        $vendedor = Vendedor::where('email', auth()->user()->email)->first();
        abort_unless((bool) $vendedor, 403, 'El usuario no está asociado a un vendedor.');

        $query->join('vendedor_deposito as vd', 'vd.CDEPOS', '=', 'd.CDEPOS')
            ->where('vd.vendedor_id', $vendedor->id);

        $depositos = $query->get();
        abort_if($depositos->isEmpty(), 403, 'No tiene depósitos asignados.');

        $seleccionado = (string) $request->input('cdepos', $depositos->first()->CDEPOS);
        abort_unless($depositos->contains('CDEPOS', $seleccionado), 403, 'Depósito no autorizado.');

        return [$depositos, $seleccionado];
    }

    private function buildQuery(Request $request, string $cdepos, array $pricing)
    {
        $recargo = $pricing['recargo'];
        $grupoNacional = $pricing['grupo_nacional'];
        $precioDivisaBase = $recargo > 0
            ? '(CASE WHEN COALESCE(i.BASE3, 0) > 0 THEN i.BASE3 ELSE i.BASE1 + (i.BASE1 * ' . $recargo . ' / 100) END)'
            : 'i.BASE1';
        $precioBsBase = $recargo > 0
            ? '(CASE WHEN COALESCE(i.BASE4, 0) > 0 THEN i.BASE4 ELSE i.BASE2 + (i.BASE2 * ' . $recargo . ' / 100) END)'
            : 'i.BASE2';
        if ($grupoNacional) {
            $grupoNacionalSql = addslashes($grupoNacional);
            $precioDivisaBase = "(CASE WHEN i.CGRUPO = '" . $grupoNacionalSql . "' THEN i.BASE1 ELSE " . $precioDivisaBase . ' END)';
            $precioBsBase = "(CASE WHEN i.CGRUPO = '" . $grupoNacionalSql . "' THEN i.BASE2 ELSE " . $precioBsBase . ' END)';
        }
        $factorIva = '(1 + (CASE WHEN COALESCE(i.IMPUEST, 0) > 0 THEN i.IMPUEST ELSE 0 END / 100))';
        $precioRef1Expression = '(' . $precioDivisaBase . ' * ' . $factorIva . ')';
        $precioRef2Expression = '(' . $precioBsBase . ' * ' . $factorIva . ')';

        $query = DB::connection('company')->table('INVEN as i')
            ->join('ARTDEPOS as ad', function ($join) use ($cdepos) {
                $join->on('ad.CODIGO', '=', 'i.CODIGO')->where('ad.CDEPOS', '=', $cdepos);
            })
            ->leftJoin('GRUPO as g', 'g.CGRUPO', '=', 'i.CGRUPO')
            ->select([
                'i.CODIGO', 'i.DESCR', 'i.CGRUPO', 'i.FOTO', 'g.DGRUPO',
                DB::raw('SUM(ad.EUNIDAD) as stock'),
                DB::raw($precioRef1Expression . ' as precio_ref1'),
                DB::raw($precioRef2Expression . ' as precio_ref2'),
            ])
            ->groupBy('i.CODIGO', 'i.DESCR', 'i.CGRUPO', 'i.FOTO', 'i.BASE1', 'i.BASE2', 'i.BASE3', 'i.BASE4', 'i.IMPUEST', 'g.DGRUPO');

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);
            $query->where(function ($subquery) use ($buscar) {
                $subquery->where('i.CODIGO', 'like', '%' . $buscar . '%')
                    ->orWhere('i.DESCR', 'like', '%' . $buscar . '%');
            });
        }
        $grupos = array_values(array_filter((array) $request->input('cgrupo', [])));
        if ($grupos) {
            $query->whereIn('i.CGRUPO', $grupos);
        }
        if ($request->filled('precio_desde')) {
            $query->whereRaw($precioRef1Expression . ' >= ?', [(float) $request->precio_desde]);
        }
        if ($request->filled('precio_hasta')) {
            $query->whereRaw($precioRef1Expression . ' <= ?', [(float) $request->precio_hasta]);
        }

        if ($request->stock === 'con_stock') {
            $query->havingRaw('SUM(ad.EUNIDAD) > 0');
        } elseif ($request->stock === 'sin_stock') {
            $query->havingRaw('SUM(ad.EUNIDAD) <= 0');
        } elseif ($request->stock === 'bajo') {
            $query->havingRaw('SUM(ad.EUNIDAD) > 0 AND SUM(ad.EUNIDAD) <= 10');
        }

        switch ($request->input('orden', 'descripcion')) {
            case 'codigo':
                $query->orderBy('i.CODIGO');
                break;
            case 'precio_asc':
                $query->orderByRaw($precioRef1Expression . ' ASC');
                break;
            case 'precio_desc':
                $query->orderByRaw($precioRef1Expression . ' DESC');
                break;
            case 'stock_desc':
                $query->orderByRaw('SUM(ad.EUNIDAD) DESC');
                break;
            default:
                $query->orderBy('i.DESCR');
        }

        return $query;
    }

    private function attachPhotos($productos): void
    {
        $sinFoto = $productos->filter(function ($producto) {
            return empty($producto->FOTO);
        })->pluck('CODIGO');

        if ($sinFoto->isEmpty()) {
            return;
        }

        $fotos = InvenFoto::whereIn('codigo', $sinFoto)
            ->orderBy('id')
            ->get()
            ->unique('codigo')
            ->pluck('foto', 'codigo');

        $productos->each(function ($producto) use ($fotos) {
            $producto->FOTO = $producto->FOTO ?: $fotos->get($producto->CODIGO);
        });
    }

    private function getRecargoVendedor(): float
    {
        return (float) (optional(Vendedor::where('email', auth()->user()->email)->first())->recargo ?? 0);
    }

    private function getPricingContext(float $recargo): array
    {
        $grupoNacional = DB::connection('company')->table('GRUPO')
            ->where('DGRUPO', 'like', '%NACIONAL%')
            ->value('CGRUPO');

        return [
            'recargo' => $recargo,
            'grupo_nacional' => $grupoNacional,
        ];
    }
}