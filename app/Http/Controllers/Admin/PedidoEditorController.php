<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtDepos;
use App\Models\DescuentoGlobal;
use App\Models\OrderClient;
use App\Models\OrderGrupo;
use App\Models\OrderInven;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Rate;
use App\Models\Vendedor;
use App\Models\VendedorDeposito;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoEditorController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::query()
            ->whereRaw('BINARY estatus NOT IN (BINARY ?, BINARY ?)', ['CANCELADO', 'PAGADO'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                    ->orWhere('rif', 'like', '%' . $search . '%')
                    ->orWhere('descripcion', 'like', '%' . $search . '%')
                    ->orWhere('seller_code', 'like', '%' . $search . '%');
            });
        }

        $pedidos = $query->paginate(20);

        return view('admin.pedidos_editor.index', compact('pedidos'));
    }

    public function create()
    {
        $vendedores = $this->getVendedoresConUsuario();
        $grupos = (new OrderGrupo)->getData();
        $deposByVendedor = $this->buildDeposMap($vendedores);

        return view('admin.pedidos_editor.form', [
            'pedido' => null,
            'detalle' => collect(),
            'vendedores' => $vendedores,
            'grupos' => $grupos,
            'deposByVendedor' => $deposByVendedor,
            'clientes' => collect(),
            'depositoNombrePedido' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendedor_id' => 'required|integer',
            'cdepos' => 'required|string|max:20',
            'rif' => 'nullable|string|max:30',
            'descripcion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:60',
            'email' => 'nullable|string|max:120',
            'conditions' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:255',
            'factura' => 'nullable|in:SI,NO',
            'porc_retencion' => 'nullable|numeric|min:0|max:100',
            'cliageret' => 'nullable|integer',
            'codcli' => 'nullable|string|max:50',
            'descuento' => 'nullable|numeric|min:0',
            'fecha_despacho' => 'nullable|date',
        ]);

        $vendedor = Vendedor::findOrFail((int) $data['vendedor_id']);
        $sellerUser = User::where('email', $vendedor->email)->first();

        if (! $sellerUser) {
            return back()->withErrors(['vendedor_id' => 'El vendedor no tiene usuario asociado.'])->withInput();
        }

        //$sellerNombre = trim((string) (($sellerUser->name ?? '') . ' ' . ($sellerUser->last_name ?? '')));
        $sellerNombre = trim((string) (($sellerUser->name ?? '')));
        if ($sellerNombre === '') {
            $sellerNombre = (string) $vendedor->codigo;
        }

        $pedido = new Pedido();
        $pedido->referencia = '';
        $pedido->descripcion = $data['descripcion'] ?? '';
        $pedido->estatus = 'CARGANDO';
        $pedido->user_id = $sellerUser->id;
        $pedido->seller_code = $sellerNombre;
        $pedido->rif = $this->formatRif($data['rif'] ?? '');
        $pedido->telefono = $data['telefono'] ?? '';
        $pedido->email = $data['email'] ?? '';
        $pedido->cdepos = $data['cdepos'];
        $pedido->observations = $data['observations'] ?? '';
        $pedido->conditions = $data['conditions'] ?? '';
        $pedido->descuento = (float) ($data['descuento'] ?? 0);
        $pedido->factura = $data['factura'] ?? 'SI';
        $pedido->porc_retencion = (float) ($data['porc_retencion'] ?? 0);
        $pedido->retencion = 0;
        $pedido->cliageret = (int) ($data['cliageret'] ?? 0);
        $pedido->fecha_despacho = $data['fecha_despacho'] ?? null;
        $pedido->codcli = $data['codcli'] ?? '';
        $pedido->fecha = now();
        $pedido->created_at = now();

        $todayTasa = \App\Models\Tasa::where('fecha', now()->format('Y-m-d'))->first();
        $pedido->tasa = $todayTasa ? $todayTasa->valor : 0;
        $pedido->save();

        return redirect()->route('admin.pedidos_editor.edit', $pedido->id)
            ->with('info', 'Pedido base creado. Ahora puedes agregar o modificar productos.');
    }

    public function edit(int $pedido)
    {
        $pedidoModel = Pedido::findOrFail($pedido);
        $detalle = PedidoDetalle::where('pedido_id', $pedidoModel->id)->orderBy('id')->get();

        $vendedores = $this->getVendedoresConUsuario();
        $grupos = (new OrderGrupo)->getData();
        $deposByVendedor = $this->buildDeposMap($vendedores);

        $vendedor = $this->resolveVendedorPedido($pedidoModel);
        $clientes = collect();
        if ($vendedor) {
            $clientes = (new OrderClient)->getData(null, $vendedor->email);
        }

        // obtener depositos
        $depositoNombrePedido = DB::connection('company')->table('DEPOSITO')
            ->where('CDEPOS', $pedidoModel->cdepos)
            ->value('DDEPOS');

        return view('admin.pedidos_editor.form', [
            'pedido' => $pedidoModel,
            'detalle' => $detalle,
            'vendedores' => $vendedores,
            'grupos' => $grupos,
            'deposByVendedor' => $deposByVendedor,
            'clientes' => $clientes,
            'depositoNombrePedido' => $depositoNombrePedido,
            'vendedorEditId' => optional($vendedor)->id,
        ]);
    }

    public function update(Request $request, int $pedido)
    {
        $pedidoModel = Pedido::findOrFail($pedido);

        $data = $request->validate([
            'vendedor_id' => 'required|integer',
            'cdepos' => 'required|string|max:20',
            'rif' => 'required|string|max:30',
            'descripcion' => 'required|string|max:255',
            'telefono' => 'required|string|max:60',
            'email' => 'nullable|string|max:120',
            'conditions' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:255',
            'factura' => 'required|in:SI,NO',
            'porc_retencion' => 'nullable|numeric|min:0|max:100',
            'cliageret' => 'nullable|integer',
            'codcli' => 'nullable|string|max:50',
            'descuento' => 'nullable|numeric|min:0',
            'fecha_despacho' => 'nullable|date',
            'estatus' => 'nullable|string|max:40',
        ]);

        $vendedor = Vendedor::findOrFail((int) $data['vendedor_id']);
        $sellerUser = User::where('email', $vendedor->email)->first();

        if (! $sellerUser) {
            return back()->withErrors(['vendedor_id' => 'El vendedor no tiene usuario asociado.'])->withInput();
        }

        $sellerNombre = trim((string) (($sellerUser->name ?? '')));
        if ($sellerNombre === '') {
            $sellerNombre = (string) $vendedor->codigo;
        }

        $pedidoModel->user_id = $sellerUser->id;
        $pedidoModel->seller_code = $sellerNombre;
        $pedidoModel->cdepos = $data['cdepos'];
        $pedidoModel->rif = $this->formatRif($data['rif']);
        $pedidoModel->descripcion = $data['descripcion'];
        $pedidoModel->telefono = $data['telefono'];
        $pedidoModel->email = $data['email'] ?? '';
        $pedidoModel->conditions = $data['conditions'] ?? '';
        $pedidoModel->observations = $data['observations'] ?? '';
        $pedidoModel->factura = $data['factura'];
        $pedidoModel->porc_retencion = (float) ($data['porc_retencion'] ?? 0);
        $pedidoModel->cliageret = (int) ($data['cliageret'] ?? 0);
        $pedidoModel->codcli = $data['codcli'] ?? '';
        $pedidoModel->descuento = (float) ($data['descuento'] ?? 0);
        $pedidoModel->fecha_despacho = $data['fecha_despacho'] ?? null;
        $pedidoModel->estatus = $data['estatus'] ?? 'PENDIENTE';
        $pedidoModel->save();

        (new Pedido)->updateTotals($pedidoModel->id);

        return redirect()->route('admin.pedidos_editor.edit', $pedidoModel->id)
            ->with('info', 'Pedido actualizado correctamente.');
    }

    public function productos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vendedor_id' => 'required|integer',
            'cdepos' => 'required|string|max:20',
            'cgrupo' => 'nullable|string|max:20',
            'search' => 'nullable|string|max:120',
        ]);

        $vendedor = Vendedor::findOrFail((int) $data['vendedor_id']);

        $products = (new OrderInven)->getGroupProducts(
            $vendedor,
            $data['cdepos'],
            $data['cgrupo'] ?? 'TODOS',
            $data['search'] ?? null
        );

        $config = DescuentoGlobal::first();
        $rate = (new Rate)->gatLastRate();
        $discount = $rate ? ($rate->discount ?? 0) : 0;

        $payload = $products->map(function ($p) use ($vendedor) {
            $recargo = (float) ($vendedor->recargo ?? 0);
            $baseRef = (float) ($p->BASE2 ?? 0);
            if ($baseRef <= 0) {
                $baseRef = (float) ($p->BASE1 ?? 0);
            }
            $precioSugerido = $baseRef + (($baseRef * $recargo) / 100);

            return [
                'codigo' => (string) $p->CODIGO,
                'descripcion' => (string) $p->DESCR,
                'grupo' => (string) $p->DGRUPO,
                'base1' => (float) ($p->BASE1 ?? 0),
                'base2' => (float) ($p->BASE2 ?? 0),
                'stock' => (float) ($p->EUNIDAD ?? 0),
                'reserva' => (float) ($p->RESERVA ?? 0),
                'impuesto' => (float) ($p->IMPUEST ?? 0),
                'precio_sugerido' => round($precioSugerido, 4),
            ];
        })->values();

        return response()->json([
            'type' => 'success',
            'data' => $payload,
            'show_precio1' => $config && $config->show_precio1 === 'SI' ? 'SI' : 'NO',
            'descuento' => (float) ($config->discount ?? 0),
            'discount' => (float) $discount,
        ]);
    }

    public function storeItem(Request $request, int $pedido): JsonResponse
    {
        $pedidoModel = Pedido::findOrFail($pedido);

        $data = $request->validate([
            'codigo_inven' => 'required|string|max:40',
            'cantidad' => 'required|integer|min:1',
            'precio_dolar' => 'required|numeric|min:0',
            'iva' => 'required|integer|in:0,16',
            'pago' => 'nullable|string|max:10',
        ]);

        $codigo = trim((string) $data['codigo_inven']);
        $cantidad = (int) $data['cantidad'];
        $precio = (float) $data['precio_dolar'];
        $iva = (int) $data['iva'];
        $pago = $data['pago'] ?? 'Bs';

        $producto = (new OrderInven)->getProduct($codigo);
        if (! $producto) {
            return response()->json([
                'type' => 'error',
                'message' => 'Producto no encontrado.',
            ], 404);
        }

        $detalle = PedidoDetalle::where('pedido_id', $pedidoModel->id)
            ->where('codigo_inven', $codigo)
            ->first();

        if ($detalle) {
            $detalle->cantidad = (int) $detalle->cantidad + $cantidad;
            $detalle->precio = $precio;
            $detalle->precio_dolar = $precio;
            $detalle->iva = $iva;
            $detalle->pago = $pago;
            $detalle->save();

            $this->ajustarReserva($pedidoModel, $codigo, $cantidad);
            (new Pedido)->updateTotals($pedidoModel->id);
        } else {
            $tasa = $pedidoModel->tasa ?? 0;
            (new PedidoDetalle)->createNew(
                $pedidoModel->id,
                $cantidad,
                round($precio, 4),
                round((float) ($producto->ACTUALDL ?? $precio), 4),
                $tasa,
                $codigo,
                (string) $producto->DESCR,
                (string) $producto->DUNIMEDD,
                $pago,
                $iva
            );

            $this->ajustarReserva($pedidoModel, $codigo, $cantidad);
        }

        return response()->json([
            'type' => 'success',
            'totales' => $this->buildTotales($pedidoModel->id),
            'detalle' => PedidoDetalle::where('pedido_id', $pedidoModel->id)->orderBy('id')->get(),
        ]);
    }

    public function updateItem(Request $request, int $pedido, int $item): JsonResponse
    {
        $pedidoModel = Pedido::findOrFail($pedido);
        $detalle = PedidoDetalle::where('pedido_id', $pedidoModel->id)->where('id', $item)->firstOrFail();

        $data = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio_dolar' => 'required|numeric|min:0',
            'iva' => 'required|integer|in:0,16',
            'pago' => 'nullable|string|max:10',
        ]);

        $cantidadNueva = (int) $data['cantidad'];
        $cantidadAnterior = (int) $detalle->cantidad;
        $delta = $cantidadNueva - $cantidadAnterior;

        $detalle->cantidad = $cantidadNueva;
        $detalle->precio = (float) $data['precio_dolar'];
        $detalle->precio_dolar = (float) $data['precio_dolar'];
        $detalle->iva = (int) $data['iva'];
        $detalle->pago = $data['pago'] ?? $detalle->pago;
        $detalle->save();

        if (abs($delta) > 0.00001) {
            $this->ajustarReserva($pedidoModel, (string) $detalle->codigo_inven, $delta);
        }

        (new Pedido)->updateTotals($pedidoModel->id);

        return response()->json([
            'type' => 'success',
            'totales' => $this->buildTotales($pedidoModel->id),
            'item' => $detalle,
        ]);
    }

    public function destroyItem(int $pedido, int $item): JsonResponse
    {
        $pedidoModel = Pedido::findOrFail($pedido);
        $detalle = PedidoDetalle::where('pedido_id', $pedidoModel->id)->where('id', $item)->firstOrFail();

        $this->ajustarReserva($pedidoModel, (string) $detalle->codigo_inven, -1 * (float) $detalle->cantidad);
        $detalle->delete();

        (new Pedido)->updateTotals($pedidoModel->id);

        return response()->json([
            'type' => 'success',
            'totales' => $this->buildTotales($pedidoModel->id),
        ]);
    }

    private function buildTotales(int $pedidoId): array
    {
        $totales = PedidoDetalle::where('pedido_id', $pedidoId)
            ->selectRaw('COALESCE(SUM(cantidad * precio_dolar), 0) as total_precio')
            ->selectRaw('COALESCE(SUM(cantidad), 0) as total_cantidad')
            ->selectRaw('COALESCE(SUM(cantidad * precio_dolar * iva / 100), 0) as total_iva')
            ->first();

        return [
            'total_precio' => (float) ($totales->total_precio ?? 0),
            'total_cantidad' => (float) ($totales->total_cantidad ?? 0),
            'total_iva' => (float) ($totales->total_iva ?? 0),
        ];
    }

    private function ajustarReserva(Pedido $pedido, string $codigo, float $delta): void
    {
        if (abs($delta) < 0.00001) {
            return;
        }

        $artDepos = ArtDepos::where('CODIGO', $codigo)
            ->where('CDEPOS', $pedido->cdepos)
            ->first();

        if (! $artDepos) {
            return;
        }

        $nuevaReserva = ((float) $artDepos->RESERVA) + $delta;
        if ($nuevaReserva < 0) {
            $nuevaReserva = 0;
        }

        ArtDepos::where('CODIGO', $codigo)
            ->where('CDEPOS', $pedido->cdepos)
            ->update(['RESERVA' => $nuevaReserva]);
    }

    private function resolveVendedorPedido(Pedido $pedido): ?Vendedor
    {
        $vendedor = null;

        if (! empty($pedido->seller_code)) {
            $vendedor = Vendedor::where('codigo', $pedido->seller_code)->first();
        }

        if (! $vendedor && ! empty($pedido->user_id)) {
            $user = User::find($pedido->user_id);
            if ($user) {
                $vendedor = Vendedor::where('email', $user->email)->first();
            }
        }

        return $vendedor;
    }

    private function buildDeposMap($vendedores): array
    {
        $map = [];

        foreach ($vendedores as $vendedor) {
            $depos = VendedorDeposito::with('deposito')
                ->where('vendedor_id', $vendedor->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'CDEPOS' => $item->CDEPOS,
                        'DDEPOS' => optional($item->deposito)->DDEPOS ?: $item->CDEPOS,
                    ];
                })
                ->values()
                ->all();

            $map[(string) $vendedor->id] = $depos;
        }

        return $map;
    }

    private function getVendedoresConUsuario()
    {
        return Vendedor::orderBy('codigo')
            ->get()
            ->map(function ($v) {
                $user = User::where('email', $v->email)->select('name', 'last_name')->first();
                $v->nombre_completo = $user ? trim(($user->name ?? '') . ' ' . ($user->last_name ?? '')) : $v->email;
                return $v;
            });
    }

    private function formatRif(string $rif): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($rif));
        if (strlen($clean) >= 3) {
            return substr($clean, 0, 1) . '-' . substr($clean, 1, -1) . '-' . substr($clean, -1);
        }

        return $clean;
    }
}
