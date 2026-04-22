<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderInven;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display inventory dashboard.
     */
    public function index(Request $request)
    {
        // Get products with inventory initial data
        $productos = DB::connection('company')
            ->table('INVEN as i')
            ->leftJoin('inventario_inicial as inv', 'inv.codigo', '=', 'i.CODIGO')
            ->select([
                'i.CODIGO', 
                'i.DESCR', 
                'i.BASE1', 
                'i.BASE2',
                'i.CGRUPO',
                'i.SMIN',
                'i.SMAX',
                DB::raw('COALESCE(SUM(inv.cantidad), 0) as inventario_inicial')
            ])
            ->groupBy('i.CODIGO', 'i.DESCR', 'i.BASE1', 'i.BASE2', 'i.CGRUPO', 'i.SMIN', 'i.SMAX')
            ->get();

        // Calculate summary statistics
        $totalProductos = $productos->count();
        $totalInvInicial = $productos->sum('inventario_inicial');
        $totalValorUSD = $productos->sum(function($p) {
            return ($p->BASE1 + $p->inventario_inicial) * (float)$p->BASE1;
        });

        return view('admin.reportes.inventario_dashboard', compact(
            'productos', 'totalProductos', 'totalInvInicial', 'totalValorUSD'
        ));
    }
}
