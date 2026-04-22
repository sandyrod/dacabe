<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetencionController extends Controller
{
    private function companyDb(): string
    {
        return DB::connection('company')->getDatabaseName();
    }

    private function mainDb(): string
    {
        return DB::connection('mysql')->getDatabaseName();
    }

    public function index(Request $request)
    {
        $cdb = $this->companyDb();
        $mdb = $this->mainDb();

        $query = DB::connection('company')
            ->table(DB::raw("{$cdb}.pedidos as p"))
            ->leftJoin(DB::raw("{$mdb}.users as u"), 'u.id', '=', 'p.user_id')
            ->leftJoin(DB::raw("{$cdb}.vendedores as v"), 'v.codigo', '=', 'p.seller_code')
            ->where('p.porc_retencion', '>', 0)
            ->where('p.saldo_iva_bs', '>', 0)
            ->where('p.estatus', '!=', 'CANCELADO')
            ->select([
                'p.id',
                'p.fecha',
                'p.descripcion as cliente',
                'p.rif',
                'p.base',
                'p.iva_bs',
                'p.saldo_base',
                'p.saldo_iva_bs',
                'p.porc_retencion',
                'p.retencion',
                'p.comprobante_retencion',
                'p.tasa',
                'p.estatus',
                'u.name as vendedor_nombre',
                'u.email as vendedor_email',
                'v.codigo as vendedor_codigo',
            ]);

        if ($request->filled('vendedor')) {
            $query->where('u.id', $request->vendedor);
        }

        if ($request->filled('cliente')) {
            $search = $request->cliente;
            $query->where(function ($q) use ($search) {
                $q->where('p.descripcion', 'like', "%{$search}%")
                  ->orWhere('p.rif', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('p.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('p.fecha', '<=', $request->fecha_fin);
        }

        if ($request->input('estado_base') === 'pagada') {
            $query->where('p.saldo_base', '<=', 0);
        } elseif ($request->input('estado_base') === 'pendiente') {
            $query->where('p.saldo_base', '>', 0);
        }

        $pedidos = $query->orderBy('p.fecha', 'desc')->paginate(20);
        $pedidos->appends($request->query());

        // Stats totales (sin filtros para mostrar el estado global)
        $statsBase = DB::connection('company')
            ->table(DB::raw("{$cdb}.pedidos"))
            ->where('porc_retencion', '>', 0)
            ->where('saldo_iva_bs', '>', 0)
            ->where('estatus', '!=', 'CANCELADO');

        $stats = [
            'total_pedidos'     => (clone $statsBase)->count(),
            'total_retencion_bs' => (clone $statsBase)->sum('saldo_iva_bs'),
            'solo_iva_pendiente' => (clone $statsBase)->where('saldo_base', '<=', 0)->count(),
        ];

        // Vendedores para el filtro
        $vendedores = DB::connection('mysql')
            ->table('users')
            ->whereExists(function ($q) use ($cdb) {
                $q->from(DB::raw("{$cdb}.pedidos"))
                  ->whereColumn('pedidos.user_id', 'users.id')
                  ->where('pedidos.porc_retencion', '>', 0)
                  ->where('pedidos.saldo_iva_bs', '>', 0);
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return view('admin.retenciones.index', compact('pedidos', 'stats', 'vendedores'));
    }

    public function detalle($pedidoId)
    {
        $cdb = $this->companyDb();
        $mdb = $this->mainDb();

        $pedido = DB::connection('company')
            ->table(DB::raw("{$cdb}.pedidos as p"))
            ->leftJoin(DB::raw("{$mdb}.users as u"), 'u.id', '=', 'p.user_id')
            ->leftJoin(DB::raw("{$cdb}.vendedores as v"), 'v.codigo', '=', 'p.seller_code')
            ->where('p.id', $pedidoId)
            ->select([
                'p.id', 'p.fecha', 'p.descripcion as cliente', 'p.rif',
                'p.base', 'p.iva_bs', 'p.saldo_base', 'p.saldo_iva_bs',
                'p.porc_retencion', 'p.retencion', 'p.comprobante_retencion', 'p.tasa', 'p.estatus',
                'u.name as vendedor_nombre', 'u.email as vendedor_email',
                'v.codigo as vendedor_codigo',
            ])
            ->first();

        if (!$pedido) {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        // Pagos del pedido
        $pagos = DB::connection('company')
            ->table(DB::raw("{$cdb}.pagos_pedidos as pp"))
            ->join(DB::raw("{$cdb}.pagos as pag"), 'pag.id', '=', 'pp.pago_id')
            ->leftJoin(DB::raw("{$cdb}.TPAGO as t"), 't.CPAGO', '=', 'pag.tpago_id')
            ->where('pp.pedido_id', $pedidoId)
            ->select([
                'pag.id as pago_id',
                'pag.fecha',
                'pag.referencia',
                'pag.moneda_pago',
                'pag.estatus',
                'pag.rate as tasa',
                't.DPAGO as tipo_pago',
                'pp.monto',
                'pp.iva',
                'pp.retencion',
                'pp.descuento',
            ])
            ->orderBy('pag.fecha', 'asc')
            ->get();

        // Comisiones vinculadas a los pagos del pedido
        $pagoIds = $pagos->pluck('pago_id')->filter()->values()->toArray();

        $comisiones = [];
        if (!empty($pagoIds)) {
            $comisiones = DB::connection('company')
                ->table(DB::raw("{$cdb}.comision_vendedores as cv"))
                ->whereIn('cv.pago_id', $pagoIds)
                ->select([
                    'cv.pago_id',
                    'cv.codigo_producto',
                    'cv.nombre_producto',
                    'cv.cantidad',
                    'cv.porcentaje_comision',
                    'cv.monto_comision',
                    'cv.estatus_comision',
                ])
                ->get();
        }

        return response()->json([
            'pedido'    => $pedido,
            'pagos'     => $pagos,
            'comisiones' => $comisiones,
        ]);
    }

    public function aprobar($pedidoId)
    {
        $pedido = Pedido::on('company')->findOrFail($pedidoId);

        if ((float) $pedido->saldo_iva_bs <= 0) {
            return response()->json(['error' => 'Este pedido no tiene retención de IVA pendiente'], 422);
        }

        $update = ['saldo_iva_bs' => 0, 'updated_at' => now()];

        // Si la base también está saldada, marcar el pedido como PAGADO
        if ((float) $pedido->saldo_base <= 0) {
            $update['estatus'] = 'PAGADO';
        }

        DB::connection('company')->table('pedidos')->where('id', $pedidoId)->update($update);

        $message = isset($update['estatus'])
            ? 'Comprobante validado. El pedido queda marcado como PAGADO.'
            : 'Comprobante de retención validado. El IVA queda registrado como pagado.';

        return response()->json(['success' => true, 'message' => $message, 'pagado' => isset($update['estatus'])]);
    }
}
