<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Pago;
use App\Models\PagoPedido;
use App\Models\PagoGrupo;
use App\Models\PagoGrupoDetalles;
use App\Models\PedidoDetalle;
use App\Models\InvenInformacion;
use App\Models\OrderInven;
use App\Models\ComisionVendedor;
use App\Models\Vendedor;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentDistributionService
{
    /**
     * Distributes a set of payments across a set of orders.
     */
    public function distributePaymentsAcrossOrders(PagoGrupo $pagoGrupo, $pagos, $pedidos, array $detallePedidos, string $moneda_pago)
    {
        foreach ($pagos as $pago) {
            $montoAsignadoPago = PagoPedido::where('pago_id', $pago->id)->sum('monto');
            $montoDisponiblePago = $pago->monto - $montoAsignadoPago;

            if ($montoDisponiblePago <= 0.01) {
                continue;
            }

            foreach ($pedidos as $pedido) {
                $detalle = collect($detallePedidos)->firstWhere('pedido_id', $pedido->id);
                if (!$detalle) {
                    continue;
                }

                $montoAsignadoPedido = PagoPedido::where('pedido_id', $pedido->id)
                    ->whereIn('pago_id', $pagos->pluck('id'))
                    ->sum('monto');

                $saldoReal = $this->calculateSaldoReal($pedido, $detalle, $moneda_pago);
                $saldoPendientePedido = $saldoReal - $montoAsignadoPedido;

                if ($saldoPendientePedido <= 0.01) {
                    continue;
                }

                $montoAsignadoPago = PagoPedido::where('pago_id', $pago->id)->sum('monto');
                $montoDisponiblePago = $pago->monto - $montoAsignadoPago;

                if ($montoDisponiblePago <= 0.01) {
                    break;
                }

                $montoParaAsignar = min($saldoPendientePedido, $montoDisponiblePago);
                $proporcion = $saldoReal > 0 ? ($montoParaAsignar / $saldoReal) : 0;

                $this->createPagoPedido($pago->id, $pedido->id, $montoParaAsignar, $detalle, $proporcion);
            }
        }

        $this->processOrderGroupsAndCommissions($pagoGrupo, $pedidos, $detallePedidos, $moneda_pago);
    }

    protected function calculateSaldoReal(Pedido $pedido, array $detalle, string $moneda_pago): float
    {
        $detallesPedido = PedidoDetalle::where('pedido_id', $pedido->id)->get();
        $porc_iva_det = ($detallesPedido->isNotEmpty() && $detallesPedido[0]->iva > 0) ? $detallesPedido[0]->iva / 100 : 0;

        $saldo = $detalle['saldo'] ?? 0;
        $descuento = $detalle['descuento'] ?? 0;
        $retencion = $detalle['retencion'] ?? 0;

        if ($moneda_pago === 'Bolívares') {
            $iva_det = ($saldo * $porc_iva_det) - $retencion;
            return (float) ($saldo + $iva_det);
        }

        $iva_det = (($saldo - $descuento) * $porc_iva_det) - $retencion;
        return (float) ($saldo - $descuento + $iva_det);
    }

    protected function createPagoPedido(int $pagoId, int $pedidoId, float $monto, array $detalle, float $proporcion): void
    {
        PagoPedido::create([
            'pago_id' => $pagoId,
            'pedido_id' => $pedidoId,
            'monto' => round($monto, 2),
            'iva' => round(($detalle['iva'] ?? 0) * $proporcion, 2),
            'retencion' => round(($detalle['retencion'] ?? 0) * $proporcion, 2),
            'descuento' => round(($detalle['descuento'] ?? 0) * $proporcion, 2),
        ]);
    }

    protected function processOrderGroupsAndCommissions(PagoGrupo $pagoGrupo, $pedidos, array $detallePedidos, string $moneda_pago): void
    {
        foreach ($pedidos as $pedido) {
            $detalle = collect($detallePedidos)->firstWhere('pedido_id', $pedido->id);
            if (!$detalle) continue;

            $pagoGrupoDetalle = $this->createPagoGrupoDetalle($pagoGrupo->id, $pedido, $detalle);
            $this->calculateAndStoreCommissions($pagoGrupoDetalle, $pedido);
            $this->updateOrderStatus($pedido);
        }
    }

    protected function createPagoGrupoDetalle(int $pagoGrupoId, Pedido $pedido, array $detalle): PagoGrupoDetalles
    {
        $detallesPedido = PedidoDetalle::where('pedido_id', $pedido->id)->first();
        $porc_iva_det = ($detallesPedido && $detallesPedido->iva > 0) ? $detallesPedido->iva / 100 : 0;

        return PagoGrupoDetalles::create([
            'pago_grupo_id' => $pagoGrupoId,
            'pedido_id' => $pedido->id,
            'monto' => $detalle['saldo'] ?? 0,
            'base' => $detalle['saldo'] ?? 0,
            'descuento' => $detalle['descuento'] ?? 0,
            'iva' => ($detalle['saldo'] - ($detalle['descuento'] ?? 0)) * $porc_iva_det,
            'retencion' => $detalle['retencion'] ?? 0,
            'total' => ($detalle['saldo'] ?? 0) + (($detalle['iva'] ?? 0) - ($detalle['retencion'] ?? 0)),
        ]);
    }

    protected function calculateAndStoreCommissions(PagoGrupoDetalles $pagoGrupoDetalle, Pedido $pedido): void
    {
        $detallesPedido = PedidoDetalle::where('pedido_id', $pedido->id)->get();
        $vendedor = Vendedor::where('id', $pedido->seller_id)->first();
        $vendedorName = User::where('email', $vendedor->email)->value('name');

        foreach ($detallesPedido as $detallePed) {
            $inven = InvenInformacion::where('codigo', $detallePed->codigo_inven)->first();
            $comisionPorcentaje = $inven ? $inven->comision : 0;

            if ($comisionPorcentaje <= 0) continue;

            $invenOrder = OrderInven::where('CODIGO', $detallePed->codigo_inven)->first();

            ComisionVendedor::create([
                'pago_id' => $pagoGrupoDetalle->id,
                'codigo_producto' => $detallePed->codigo_inven,
                'nombre_producto' => $invenOrder ? $invenOrder->DESCR : '',
                'cantidad' => $detallePed->cantidad,
                'porcentaje_comision' => $comisionPorcentaje,
                'monto_comision' => ($detallePed->precio_dolar * $detallePed->cantidad) * ($comisionPorcentaje / 100),
                'correo_vendedor' => $vendedor->email,
                'nombre_vendedor' => $vendedorName,
            ]);
        }
    }

    protected function updateOrderStatus(Pedido $pedido): void
    {
        $montoTotalSimple = $pedido->pedido_detalle->sum(function ($detalle) {
            return $detalle->precio * $detalle->cantidad;
        });

        $totalPagado = PagoPedido::where('pedido_id', $pedido->id)->sum('monto');

        if (($montoTotalSimple - $totalPagado) < 0.01) {
            $pedido->update(['estatus' => 'EN REVISION']);
        }
    }
}
