<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductoBulto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoBultoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        // Join with inven to get product description
        $query = DB::connection('company')
            ->table('producto_bultos as pb')
            ->join('INVEN as i', 'i.CODIGO', '=', 'pb.codigo')
            ->select(['pb.id', 'pb.codigo', 'pb.unidades_por_bulto', 'pb.updated_at', 'i.DESCR', 'i.CGRUPO'])
            ->orderBy('pb.codigo');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('pb.codigo', 'like', "%{$search}%")
                  ->orWhere('i.DESCR', 'like', "%{$search}%");
            });
        }

        $bultos = $query->paginate(50)->withQueryString();

        return view('admin.producto_bultos.index', compact('bultos', 'search'));
    }

    public function buscarProductos(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = DB::connection('company')
            ->table('INVEN as i')
            ->leftJoin('producto_bultos as pb', 'pb.codigo', '=', 'i.CODIGO')
            ->whereNull('pb.id')
            ->select(['i.CODIGO', 'i.DESCR', 'i.CGRUPO']);

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('i.CODIGO', 'like', "%{$q}%")
                  ->orWhere('i.DESCR', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('i.CODIGO')->limit(50)->get();

        return response()->json([
            'results' => $items->map(fn($p) => [
                'id'   => $p->CODIGO,
                'text' => "{$p->CODIGO} — {$p->DESCR}",
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo'             => 'required|string|max:15',
            'unidades_por_bulto' => 'required|integer|min:1',
        ]);

        ProductoBulto::updateOrCreate(
            ['codigo' => $request->codigo],
            ['unidades_por_bulto' => $request->unidades_por_bulto]
        );

        return back()->with('success', "Configuración de bulto guardada para {$request->codigo}.");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unidades_por_bulto' => 'required|integer|min:1',
        ]);

        ProductoBulto::findOrFail($id)->update([
            'unidades_por_bulto' => $request->unidades_por_bulto,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        ProductoBulto::findOrFail($id)->delete();
        return back()->with('success', 'Configuración eliminada.');
    }
}
