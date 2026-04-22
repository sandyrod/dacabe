<?php

namespace App\Models;

use App\Models\{Company, ArtDepos};
use App\User;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $connection = 'company';
    protected $table = 'pedidos';

    protected $fillable = [
        'referencia',
        'descripcion',
        'estatus',
        'user_id',
        'seller_code',
        'rif',
        'telefono',
        'email',
        'cdepos',
        'observations',
        'conditions',
        'descuento',
        'factura',
        'porc_retencion',
        'retencion',
        'cliageret',
        'fecha_despacho',
        'descuento_id',
        'monto_descuento',
        'codcli',
        'total_descuento',
        'tasa',
        'base',
        'iva_bs',
        'saldo_base',
        'saldo_iva_bs',
        'total_ajustes',
        'saldo_ajustes',
        'comprobante_retencion'
    ];

    protected $casts = [
        'total_descuento' => 'float',
        'monto_descuento' => 'float',
        'porc_retencion' => 'float',
        'retencion' => 'float',
        'tasa' => 'float',
        'base' => 'float',
        'iva_bs' => 'float',
        'saldo_base' => 'float',
        'saldo_iva_bs' => 'float',
        'total_ajustes' => 'float',
        'saldo_ajustes' => 'float',
    ];

    /**
     * Obtener el total de ajustes formateado
     */
    public function getTotalAjustesFormateadoAttribute(): string
    {
        return number_format($this->total_ajustes ?? 0, 2, ',', '.');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (!empty($pedido->rif)) {
                $rif = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($pedido->rif));
                if (strlen($rif) >= 3) {
                    $pedido->rif = substr($rif, 0, 1) . '-' . substr($rif, 1, -1) . '-' . substr($rif, -1);
                }
            }
        });
    }

    public function pedido_detalle()
    {
        return $this->hasMany(PedidoDetalle::class);
    }

    public function pedido_factura()
    {
        return $this->hasOne(PedidoFactura::class, 'pedido_id', 'id');
    }

    public function deposito()
    {
        return $this->belongsTo(Deposito::class, 'cdepos', 'CDEPOS');
    }


    public function createNew($referencia, $descripcion, $rif, $seller_code, $telefono, $email, $estatus = 'CARGANDO', $cdepos = '', $observations = '', $conditions = '', $descuento = 0, $factura = 'NO', $porc_retencion = 0, $retencion = 0, $cliageret = 0, $fecha_despacho = null, $codcli = '')
    {
        $rif = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($rif));
        if (strlen($rif) >= 3) {
            $rif = substr($rif, 0, 1) . '-' . substr($rif, 1, -1) . '-' . substr($rif, -1);
        }

        // Obtener la tasa del día
        $todayTasa = \App\Models\Tasa::where('fecha', now()->format('Y-m-d'))->first();
        $valorTasa = $todayTasa ? $todayTasa->valor : 0;

        $id = $this->insertGetId([
            'referencia' => $referencia,
            'descripcion' => $descripcion,
            'estatus' => $estatus,
            'user_id' => auth()->user()->id,
            'seller_code' => $seller_code,
            'created_at' => now(),
            'fecha' => now(),
            'rif' => $rif,
            'telefono' => $telefono,
            'email' => $email,
            'cdepos' => $cdepos,
            'observations' => $observations,
            'conditions' => $conditions,
            'descuento' => $descuento,
            'factura' => $factura,
            'porc_retencion' => $porc_retencion,
            'retencion' => $retencion,
            'cliageret' => $cliageret,
            'fecha_despacho' => $fecha_despacho,
            'codcli' => $codcli,
            'tasa' => $valorTasa
        ]);

        return $this->find($id);
    }

    public function addProduct($request)
    {
        return $this->insert([
            'pedido_id' => $request->pedido_id,
            'user_id' => auth()->user()->id,
            'cantidad' => $request->cantidad,
            'precio' => $request->precio,
            'precio_dolar' => $request->precio_dolar,
            'tasa' => $request->tasa,
            'codigo_inven' => $request->codigo_inven,
            'created_at' => now()
        ]);
    }

    public function deleteRecord($id)
    {
        return $this->where('id', $id)->delete();
    }

    public function dropOrder()
    {
        $item = $this
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->first();

        $detalles = (new PedidoDetalle)->where('pedido_id', $item->id)->get();
        foreach ($detalles as $detalle) {
            $pedido = (new Pedido)->find($item->id);
            $user = (new User)->find($pedido->user_id);
            $seller = (new Vendedor)->where('email', $user->email)->first();
            if ($seller) {
                $cdepos = $pedido->cdepos;
                $artdepos = (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->first();
                if ($artdepos) {
                    $qty = $artdepos->RESERVA - $detalle->cantidad;
                    (new ArtDepos)->where('CODIGO', $detalle->codigo_inven)->where('CDEPOS', $cdepos)->update(['RESERVA' => $qty]);
                }
            }
        }

        (new PedidoDetalle)
            ->where('pedido_id', $item->id)
            ->delete();

        $this
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->delete();
    }


    public function getActiveOrder()
    {
        return $this
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
    }

    public function searchPendingOrder()
    {
        return $this
            ->with('deposito')
            ->where('user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->first();
    }

    public function getActiveOrderDetail()
    {
        return (new PedidoDetalle)
            ->select('pedido_detalle.*')
            ->join('pedidos', 'pedidos.id', 'pedido_detalle.pedido_id')
            ->where('pedidos.user_id', auth()->user()->id)
            ->where('estatus', 'CARGANDO')
            ->get();
    }

    public function getData($id = null)
    {
        if ($id) {
            return $this->with('pedido_detalle')->with('deposito')->where('pedidos.id', $id)->first();
            //return $this->join('pedido_detalle', 'pedidos.id', 'pedido_id')->where('pedidos.id', $id)->first();
        }
        //return $this->join('pedido_detalle', 'pedidos.id', 'pedido_id')->get();
        return $this->with('pedido_detalle')->with('deposito')->get();
    }

    public function getDataDespacho($id = null)
    {
        if ($id) {
            $pedidos = $this->with('pedido_detalle')->with('deposito')->where('pedidos.id', $id)->where('pedidos.estatus', 'APROBADO')->get();
        } else {
            $pedidos = $this->with('pedido_detalle')->with('deposito')->where('pedidos.estatus', 'APROBADO')->get();
        }
        
        // Agregar número de factura a cada pedido
        foreach ($pedidos as $pedido) {
            $factura = DB::connection('company')
                ->table('pedidos_facturas')
                ->where('pedido_id', $pedido->id)
                ->first();
            $pedido->numero_factura = $factura ? $factura->factura : null;
        }
        
        return $id ? $pedidos->first() : $pedidos;
    }

    public function getOrderById($id = null)
    {
        return $this->with('deposito')->where('pedidos.id', $id)->first();
    }

    public function getMyLastOrders($limit = 5)
    {
        $data = $this->with('deposito')->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get()->take($limit);
        foreach ($data as $item) {
            $total = (new PedidoDetalle)
                ->where('pedido_id', $item->id)
                ->selectRaw('SUM(precio_dolar * cantidad) as total')
                ->value('total');
            $item->total = $total;
        }

        return $data;
    }

    public function getPendingOrders()
    {
        return $this
            ->select('pedidos.id', 'pedidos.created_at', 'pedidos.estatus', 'pedidos.descripcion', 'pedidos.seller_code', 'pedidos.rif', 'pedido_detalle.cantidad', 'pedido_detalle.codigo_inven', 'pedido_detalle.inven_descr', 'pedido_detalle.precio_dolar', 'pedidos.telefono', 'pedidos.email', 'pedidos.rif_foto', 'pedido_detalle.pago', 'pedidos.cdepos', 'pedidos.descuento', 'pedidos.factura', 'pedidos.porc_retencion', 'pedidos.retencion', 'pedidos.cliageret', 'pedidos.fecha_despacho', 'pedido_detalle.iva', 'pedidos.iva_bs', 'pedidos.base')
            ->join('pedido_detalle', 'pedidos.id', 'pedido_id')
            ->where('estatus', 'PENDIENTE')
            ->orderBy('pedidos.created_at', 'DESC')
            ->orderBy('pedidos.seller_code')
            ->get();
    }


    public function updateItem($id, $request, $estatus)
    {
        if ($request->estatus) {
            $estatus = $request->estatus;
        }
        /*
        return $this->where('id', $id)->update([
            'rif' => $request->rif,
            'descripcion' => $request->descripcion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'observations' => $request->observations,
            'conditions' => $request->conditions,
            'estatus' => $estatus
        ]);
        */
        $rif = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($request->rif));
        if (strlen($rif) >= 3) {
            $rif = substr($rif, 0, 1) . '-' . substr($rif, 1, -1) . '-' . substr($rif, -1);
        }

        $dataToUpdate = [
            'rif' => $rif,
            'descripcion' => $request->descripcion,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'estatus' => $estatus,
            'codcli' => $request->codcli
        ];

        if (!empty($request->observations)) {
            $dataToUpdate['observations'] = $request->observations;
        }

        if (!empty($request->conditions)) {
            $dataToUpdate['conditions'] = $request->conditions;
        }
        if (!empty($request->factura)) {
            $dataToUpdate['factura'] = $request->factura;
        }
        if (!empty($request->porc_retencion)) {
            $dataToUpdate['porc_retencion'] = $request->porc_retencion;
        }
        if (!empty($request->retencion)) {
            $dataToUpdate['retencion'] = $request->retencion;
        }
        if (!empty($request->cliageret)) {
            $dataToUpdate['cliageret'] = $request->cliageret;
        }
        if (!empty($request->fecha_despacho)) {
            $dataToUpdate['fecha_despacho'] = $request->fecha_despacho;
        }
        $this->where('id', $id)->update($dataToUpdate);

        return $this->where('id', $id)->first();
    }

    public function updateDescuento($id)
    {
        $discount = obtenerDescuentoGlobal() ?? 0;
        $dataToUpdate = [
            'descuento' => $discount
        ];

        return $this->where('id', $id)->update($dataToUpdate);
    }

    public function getReportConfig()
    {
        return [
            'title' => 'Listado de Pedido',
            'company' => Auth::user()->company
        ];
    }


    public function calculateBalance()
    {
        // Calcular el monto total del pedido
        $detallesPedido = $this->pedido_detalle;
        $subtotal = 0;
        $ivaTotal = 0;
        foreach ($detallesPedido as $det) {
            $base = $det->cantidad * $det->precio_dolar;
            $subtotal += $base;
            $ivaTotal += $base * (($det->iva ?? 0) / 100);
        }
        $totalConIva = $subtotal + $ivaTotal;

        $retencion = 0;
        if (($this->porc_retencion ?? 0) > 0) {
            $retencion = $ivaTotal * ($this->porc_retencion / 100);
        }

        $descuento = $this->descuento ?? 0;
        $montoTotalPedido = $totalConIva - $retencion - $descuento;

        // Calcular el total pagado para este pedido (SUMA DE TODOS LOS PAGOS + DESCUENTOS)
        $totalPagado = \App\Models\PagoPedido::where('pedido_id', $this->id)
            ->whereHas('pago', function ($q) {
                $q->where('estatus', '!=', 'RECHAZADO');
            })
            ->sum('monto');

        $totalDescuentosPagos = \App\Models\PagoPedido::where('pedido_id', $this->id)
            ->whereHas('pago', function ($q) {
                $q->where('estatus', '!=', 'RECHAZADO');
            })
            ->sum('descuento');

        // El saldo real es el total del pedido menos (lo pagado + los descuentos otorgados en pagos)
        $saldoPendiente = $montoTotalPedido - ($totalPagado + $totalDescuentosPagos);

        if (abs($saldoPendiente) < 0.01) {
            $saldoPendiente = 0;
        }

        return $saldoPendiente;
    }

    public function updateTotals($id)
    {
        \Illuminate\Support\Facades\Log::info("updateTotals called for Pedido ID: {$id}");
        $pedido = $this->find($id);
        if (!$pedido) {
            \Illuminate\Support\Facades\Log::error("Pedido with ID {$id} not found in updateTotals");
            return;
        }

        $detalles = \App\Models\PedidoDetalle::where('pedido_id', $id)->get();

        $baseTotal = 0; // En Dólares
        $ivaTotalBs = 0; // En Bolívares

        foreach ($detalles as $detalle) {
            $montoItemDolar = $detalle->precio * $detalle->cantidad;
            $baseTotal += $montoItemDolar;

            if ($detalle->iva > 0) {
                // Cálculo del IVA en Bolívares: (Monto en $ * % IVA / 100) * Tasa del item
                $ivaItemBs = ($montoItemDolar * ($detalle->iva / 100)) * $detalle->tasa;
                $ivaTotalBs += $ivaItemBs;
            }
        }

        \Illuminate\Support\Facades\Log::info("Recalculated totals for Pedido {$id}: Base={$baseTotal} $, IVA={$ivaTotalBs} Bs.");

        $res = $pedido->update([
            'base' => $baseTotal,
            'iva_bs' => $ivaTotalBs,
            'saldo_iva_bs' => $ivaTotalBs,
            'saldo_base' => $baseTotal
        ]);

        if ($res) {
            \Illuminate\Support\Facades\Log::info("Pedido {$id} totals updated successfully in database.");
        } else {
            \Illuminate\Support\Facades\Log::error("Failed to update Pedido {$id} totals in database.");
        }

        return $pedido;
    }
}
