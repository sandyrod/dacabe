<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoIvaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with('pedido_factura');

        // Filtering
        if ($request->filled('id')) {
            $query->where('id', 'like', '%' . $request->id . '%');
        }

        if ($request->filled('rif')) {
            $query->where('rif', 'like', '%' . $request->rif . '%');
        }

        if ($request->filled('descripcion')) {
            $query->where('descripcion', 'like', '%' . $request->descripcion . '%');
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        if ($request->has('base_zero') && $request->base_zero == '1') {
            $query->where('base', 0);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'rif', 'descripcion', 'estatus', 'tasa', 'base', 'iva_bs', 'saldo_base', 'saldo_iva_bs', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Statistics for the dashboard
        $stats = [
            'total_pedidos' => Pedido::count(),
            'avg_iva' => Pedido::avg('iva_bs'),
            'max_iva' => Pedido::max('iva_bs'),
        ];

        $pedidos = $query->paginate(50);

        return view('admin.pedidos.iva_modificar', compact('pedidos', 'stats'));
    }

    public function batchUpdate(Request $request)
    {
        try {
            $updates = $request->updates; // Array of {id, field, value}
            $numericFields = ['iva_bs', 'base', 'tasa', 'saldo_base', 'saldo_iva_bs'];
            $statusFields = ['estatus'];
            $editableFields = array_merge($numericFields, $statusFields);
            $allowedStatuses = ['PENDIENTE', 'APROBADO', 'EN REVISION', 'PAGADO', 'RECHAZADO', 'ANULADO', 'CANCELADO', 'ENTREGADO'];
            $estatusConSaldoCero = ['PAGADO', 'RECHAZADO', 'ANULADO', 'CANCELADO'];

            if (!is_array($updates) || empty($updates)) {
                return response()->json(['success' => false, 'message' => 'No hay cambios para procesar'], 400);
            }

            DB::beginTransaction();

            foreach ($updates as $update) {
                $id = isset($update['id']) ? (int) $update['id'] : 0;
                $field = $update['field'] ?? '';

                if ($id <= 0 || !in_array($field, $editableFields, true)) {
                    continue;
                }
                
                $pedido = Pedido::find($id);

                if ($pedido) {
                    if (in_array($field, $numericFields, true)) {
                        $value = (float) ($update['value'] ?? 0);

                        if (in_array($field, ['saldo_base', 'saldo_iva_bs'], true) && in_array($pedido->estatus, $estatusConSaldoCero, true)) {
                            $value = 0;
                        }

                        $pedido->update([
                            $field => $value
                        ]);
                    }

                    if (in_array($field, $statusFields, true)) {
                        $value = strtoupper(trim((string) ($update['value'] ?? '')));

                        if (!in_array($value, $allowedStatuses, true)) {
                            continue;
                        }

                        $data = ['estatus' => $value];
                        if (in_array($value, $estatusConSaldoCero, true)) {
                            $data['saldo_base'] = 0;
                            $data['saldo_iva_bs'] = 0;
                        }

                        $pedido->update($data);
                    }
                }
            }

            DB::commit();

            $uniquePedidosCount = count(array_unique(array_column($updates, 'id')));
            return response()->json(['success' => true, 'message' => $uniquePedidosCount . ' registro(s) actualizado(s) correctamente']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function calcularIvaBase($id, Request $request)
    {
        try {
            $tasa = floatval($request->query('tasa', 0));
            if ($tasa <= 0) {
                return response()->json(['success' => false, 'message' => 'Tasa inválida']);
            }

            $pedido = Pedido::find($id);
            if (!$pedido) {
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado']);
            }

            $detalles = \App\Models\PedidoDetalle::where('pedido_id', $id)->get();

            $baseTotal = 0; // en dólares
            $ivaTotalBs = 0; // en bolívares

            foreach ($detalles as $detalle) {
                $montoItemDolar = $detalle->precio_dolar * $detalle->cantidad;
                $baseTotal += $montoItemDolar;

                $ivaPorcentaje = floatval($detalle->iva ?? 0);
                if ($ivaPorcentaje > 0) {
                    $ivaItemBs = ($montoItemDolar * ($ivaPorcentaje / 100)) * $tasa;
                    $ivaTotalBs += $ivaItemBs;
                }
            }

            return response()->json([
                'success' => true,
                'base' => round($baseTotal, 2),
                'iva_bs' => round($ivaTotalBs, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
