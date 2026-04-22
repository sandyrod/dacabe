<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{OrderClient, ClienteVendedor, Vendedor};

class SincronizarClientesController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = DB::connection('company')->table('pedidos')
            ->where(function ($q) {
                $q->whereNull('pedidos.codcli')
                  ->orWhere('pedidos.codcli', '')
                  ->orWhere('pedidos.codcli', '0');
            });

        $statuses = $baseQuery->clone()->whereNotNull('estatus')->distinct()->pluck('estatus')->filter()->values();

        $query = $baseQuery->clone();

        if ($request->filled('pedido_rif')) {
            $query->where('pedidos.rif', 'LIKE', '%' . $request->pedido_rif . '%');
        }

        if ($request->filled('pedido_nombre')) {
            $query->join(DB::raw('CLIENTE as c'), function ($join) {
                $join->on(DB::raw('pedidos.rif COLLATE utf8mb4_unicode_ci'), '=', DB::raw('c.RIF COLLATE utf8mb4_unicode_ci'));
            })->where('c.NOMBRE', 'LIKE', '%' . $request->pedido_nombre . '%');
        }

        if ($request->filled('pedido_status')) {
            $query->where('pedidos.estatus', $request->pedido_status);
        }

        if ($request->filled('vendedor')) {
            $rifsAsociados = ClienteVendedor::whereRaw('LOWER(email_vendedor) COLLATE utf8mb4_unicode_ci = LOWER(? COLLATE utf8mb4_unicode_ci)', [$request->vendedor])
                ->pluck('rif')
                ->unique()
                ->toArray();

            if (empty($rifsAsociados)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('rif', $rifsAsociados);
            }
        }

        $pedidos = $query->orderBy('pedidos.id', 'desc')->get();

        $vendedores = DB::connection('company')
            ->table('vendedores as v')
            ->join(DB::raw(config('database.connections.mysql.database') . '.users as u'), function ($join) {
                $join->on(DB::raw('v.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('u.email COLLATE utf8mb4_unicode_ci'));
            })
            ->select('v.email', 'v.codigo', DB::raw("CONCAT(u.name, ' ', u.last_name) as nombre_completo"))
            ->orderBy('v.codigo')
            ->get()
            ->map(function ($item) {
                $item->display_name = trim($item->codigo . ' - ' . $item->nombre_completo . ' (' . $item->email . ')');
                return $item;
            });

        $rifs = $pedidos->pluck('rif')->filter()->unique()->values()->all();
        $relacionesPorRif = collect();
        if (!empty($rifs)) {
            $relacionesPorRif = ClienteVendedor::with('vendedor.user')
                ->whereIn('rif', $rifs)
                ->get()
                ->groupBy('rif')
                ->map(function ($group) {
                    return $group->first();
                });
        }

        return view('vendedor.sincronizar-clientes.index', compact('pedidos', 'vendedores', 'relacionesPorRif', 'statuses'))
            ->with([
                'pedido_rif' => $request->pedido_rif,
                'pedido_nombre' => $request->pedido_nombre,
                'pedido_status' => $request->pedido_status,
                'vendedor_filter' => $request->vendedor,
            ]);
    }

    public function buscarCliente(Request $request)
    {
        try {
            $q = $request->get('q', '');

            $clientes = (new OrderClient)
                ->select(['RIF', 'NOMBRE', 'CODCLI', 'TELEFONO'])
                ->where(function ($qb) use ($q) {
                    $qb->where('RIF', 'LIKE', "%{$q}%")
                       ->orWhere('NOMBRE', 'LIKE', "%{$q}%")
                       ->orWhere('CODCLI', 'LIKE', "%{$q}%");
                })
                ->limit(20)
                ->get();

            return response()->json(['success' => true, 'clientes' => $clientes]);

        } catch (\Exception $e) {
            Log::error('Error al buscar cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function asignarCliente($pedidoId, Request $request)
    {
        try {
            $request->validate([
                'rif'             => 'required|string',
                'codcli'          => 'required|string',
                'nombre'          => 'required|string',
                'solo_pedido'     => 'required|in:0,1',
                'vendedor_email'  => 'nullable|email',
            ]);

            $db = DB::connection('company');

            $pedido = $db->table('pedidos')->where('id', $pedidoId)->first(['rif']);

            if (!$pedido) {
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado.'], 404);
            }

            $rifOriginal = $pedido->rif;

            $nuevosValores = [
                'rif'         => $request->rif,
                'codcli'      => $request->codcli,
                'descripcion' => $request->nombre,
            ];

            if ($request->solo_pedido == '1') {
                // Solo actualizar el pedido seleccionado
                $actualizados = $db->table('pedidos')
                    ->where('id', $pedidoId)
                    ->update($nuevosValores);

                $mensaje = "Se actualizó 1 pedido (#$pedidoId).";
            } else {
                // Actualizar todos los pedidos con el mismo RIF original
                $actualizados = $db->table('pedidos')
                    ->where(function ($q) use ($rifOriginal, $pedidoId) {
                        $q->where('rif', $rifOriginal)
                          ->orWhere('id', $pedidoId);
                    })
                    ->update($nuevosValores);

                $mensaje = "Se actualizaron {$actualizados} pedido(s) con RIF \"{$rifOriginal}\".";
            }

            if ($request->filled('vendedor_email')) {
                $vendedor = Vendedor::whereRaw('LOWER(email) COLLATE utf8mb4_unicode_ci = ?', [strtolower($request->vendedor_email)])->first();
                if ($vendedor) {
                    ClienteVendedor::firstOrCreate([
                        'rif'            => $request->rif,
                        'email_vendedor' => $vendedor->email,
                    ]);
                }
            }

            return response()->json([
                'success'      => true,
                'message'      => $mensaje,
                'actualizados' => $actualizados,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al asignar cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Legacy method kept for backwards compatibility
    public function relacionarCliente(Request $request)
    {
        try {
            $request->validate([
                'rif_actual' => 'required|string',
                'rif_nuevo'  => 'required|string',
                'codcli'     => 'required|string',
                'nombre'     => 'required|string',
            ]);

            $actualizados = DB::connection('company')->table('pedidos')
                ->where('rif', $request->rif_actual)
                ->update([
                    'rif'         => $request->rif_nuevo,
                    'descripcion' => $request->nombre,
                    'codcli'      => $request->codcli,
                ]);

            return response()->json([
                'success'      => true,
                'message'      => "Se han actualizado {$actualizados} pedidos correctamente",
                'actualizados' => $actualizados,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al relacionar cliente: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
