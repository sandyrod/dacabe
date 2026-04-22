<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{ClienteVendedor, OrderClient, Vendedor};
use App\User;
use Illuminate\Support\Facades\DB;

class ClienteVendedorController extends Controller
{
    public function index(Request $request)
    {
        $query = ClienteVendedor::with(['cliente', 'vendedor.user']);

        // Aplicar filtros
        if ($request->filled('vendedor')) {
            $query->whereRaw('LOWER(email_vendedor) = ?', [strtolower($request->vendedor)]);
        }

        if ($request->filled('cliente')) {
            $query->where('rif', $request->cliente);
        }

        $asociaciones = $query->get();

        $clientes = OrderClient::select('RIF', 'NOMBRE')->orderBy('NOMBRE')->get();

        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('v.email', 'v.codigo', DB::raw("CONCAT(u.name, ' ', u.last_name) as nombre_completo"))
            ->orderBy('v.codigo')
            ->get()
            ->map(function ($item) {
                $item->display_name = trim($item->codigo . ' - ' . $item->nombre_completo . ' (' . $item->email . ')');
                return $item;
            });

        // Obtener listas para filtros
        $clientesFiltro = OrderClient::select('RIF', 'NOMBRE')
            ->whereIn('RIF', ClienteVendedor::pluck('rif')->unique())
            ->orderBy('NOMBRE')
            ->get();

        $vendedoresFiltro = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), 'v.email', '=', 'u.email')
            ->select('v.email', 'v.codigo', DB::raw("CONCAT(u.name, ' ', u.last_name) as nombre_completo"))
            ->whereIn('v.email', ClienteVendedor::pluck('email_vendedor')->unique())
            ->orderBy('v.codigo')
            ->get()
            ->map(function ($item) {
                $item->display_name = trim($item->codigo . ' - ' . $item->nombre_completo . ' (' . $item->email . ')');
                return $item;
            });

        return view('admin.cliente_vendedor.index', compact('asociaciones', 'clientes', 'vendedores', 'clientesFiltro', 'vendedoresFiltro'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rif' => 'required|string|max:15',
            'email_vendedor' => 'required|email',
        ]);

        // Verificar que el cliente existe
        $cliente = OrderClient::where('RIF', strtoupper($request->rif))->first();
        if (!$cliente) {
            return back()->withErrors(['rif' => 'Cliente no encontrado.']);
        }

        // Verificar que el vendedor existe sin diferenciar mayúsculas/minúsculas
        $emailVendedorInput = $request->email_vendedor;
        $vendedor = Vendedor::whereRaw('LOWER(email) = ?', [strtolower($emailVendedorInput)])->first();
        if (!$vendedor) {
            return back()->withErrors(['email_vendedor' => 'Vendedor no encontrado.']);
        }

        // Verificar si el cliente ya está asociado a este vendedor
        $asociacionExistente = ClienteVendedor::where('rif', strtoupper($request->rif))
            ->whereRaw('LOWER(email_vendedor) = ?', [strtolower($emailVendedorInput)])
            ->first();

        if ($asociacionExistente) {
            return back()->withErrors([
                'rif' => 'Este cliente ya está asociado a este vendedor.',
                'email_vendedor' => 'Este cliente ya está asociado a este vendedor.'
            ])->withInput();
        }

        ClienteVendedor::create([
            'rif' => strtoupper($request->rif),
            'email_vendedor' => $emailVendedorInput,
        ]);

        return back()->with('success', 'Asociación creada exitosamente.');
    }

    public function destroy($id)
    {
        $asociacion = ClienteVendedor::findOrFail($id);
        $asociacion->delete();

        return back()->with('success', 'Asociación eliminada exitosamente.');
    }
}
