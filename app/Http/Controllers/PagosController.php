<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\PagoPedido;
use App\Models\PedidoAjuste;
use DB;
use App\Models\Pedido;

class PagosController extends Controller
{
    // Listar pagos pendientes
    public function pendientes()
    {
        $pagos = Pago::where('estatus', 'PENDIENTE')->orWhere('estatus', 'EN REVISION')->get();
        $pagos = $pagos->map(function($pago) {
            $user = \App\User::find($pago->user_id);
            $pago->vendedor_nombre = $user ? $user->name : null;

            // Obtener los pedidos asociados a este pago
            $pedidoIds = $pago->pago_pedidos()->pluck('pedido_id')->toArray();
            $pago->pedidos = implode(',', $pedidoIds);
            $pago->monto_bs = $pago->monto_bs;
            $pago->rate = $pago->rate;
            $pago->referencia = $pago->referencia;
            $pago->banco_codigo = $pago->banco_codigo;
            $pago->tipo_pago = $pago->tipo_pago;
            $pago->banco = $pago->banco;

            // Indicar si algún pedido asociado tiene retención de IVA pendiente
            $pago->tiene_retencion_pendiente = !empty($pedidoIds) && Pedido::on('company')
                ->whereIn('id', $pedidoIds)
                ->where('porc_retencion', '>', 0)
                ->where('saldo_iva_bs', '>', 0)
                ->exists();

            return $pago;
        });
        return response()->json($pagos);
    }

    // Cambiar estatus de un pago
    public function cambiarEstatus(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required|in:APROBADO,RECHAZADO',
        ]);

        DB::beginTransaction();
        try {
            $pago = Pago::findOrFail($id);
            $estatusAnterior = $pago->estatus; // guardar antes de sobreescribir
            $pago->estatus = $request->estatus;
            $pago->save();

            $esDivisa = $pago->moneda_pago != 'Bolívares';

            // Si el pago es rechazado, devolver los saldos al estado anterior.
            if ($request->estatus === 'RECHAZADO') {
                $pagosPedidos = $pago->pago_pedidos()->get();

                foreach ($pagosPedidos as $pagoPedido) {
                    $pedido = Pedido::on('company')->find($pagoPedido->pedido_id);
                    if (!$pedido) {
                        continue;
                    }

                    if ($esDivisa) {
                        // Divisa: saldo_base y saldo_iva_bs se redujeron al APROBAR, no al registrar.
                        // Solo restaurar si el pago fue previamente APROBADO.
                        if ($estatusAnterior === 'APROBADO') {
                            $pedido->saldo_base = min(
                                (float) $pedido->saldo_base + (float) ($pagoPedido->monto ?? 0) + (float) ($pagoPedido->descuento ?? 0),
                                (float) $pedido->base
                            );
                            // Restaurar saldo_ajustes (solo si fue reducido al aprobar)
                            $pedido->saldo_ajustes = min(
                                (float) ($pedido->saldo_ajustes ?? 0) + (float) ($pagoPedido->ajustes_monto ?? 0),
                                (float) ($pedido->total_ajustes ?? 0)
                            );
                            // Restaurar saldo_iva_bs si se pagó IVA en divisa
                            if ((float) ($pagoPedido->iva ?? 0) > 0.001) {
                                $pedido->saldo_iva_bs = min(
                                    (float) $pedido->saldo_iva_bs + (float) $pagoPedido->iva,
                                    (float) $pedido->iva_bs
                                );
                            }
                        }
                    } else {
                        // Bolívares: el saldo se redujo al registrar (EN REVISION), restaurar siempre.
                        $pedido->saldo_base = min(
                            (float) $pedido->saldo_base + (float) ($pagoPedido->monto ?? 0) + (float) ($pagoPedido->descuento ?? 0),
                            (float) $pedido->base
                        );
                        // Restaurar saldo_ajustes (se redujo al registrar el pago BS)
                        $pedido->saldo_ajustes = min(
                            (float) ($pedido->saldo_ajustes ?? 0) + (float) ($pagoPedido->ajustes_monto ?? 0),
                            (float) ($pedido->total_ajustes ?? 0)
                        );
                        $pedido->saldo_iva_bs = min(
                            (float) $pedido->saldo_iva_bs + (float) ($pagoPedido->iva ?? 0),
                            (float) $pedido->iva_bs
                        );
                    }

                    $pedido->estatus = $this->determinarEstatus($pedido);
                    $pedido->save();
                    if ($pedido->estatus === 'PAGADO') {
                        PedidoAjuste::marcarPagados((int) $pedido->id);
                    }
                }
            }

            // Si el pago es aprobado:
            // - Bolívares: saldo ya fue reducido al registrar; solo actualizar estatus del pedido.
            // - Divisa:    aplicar monto al saldo_base (y saldo_iva_bs si se pagó IVA en divisa).
            if ($request->estatus === 'APROBADO') {
                $pagosPedidos = $pago->pago_pedidos()->get();

                foreach ($pagosPedidos as $pagoPedido) {
                    $pedido = Pedido::on('company')->find($pagoPedido->pedido_id);
                    if (!$pedido) {
                        continue;
                    }

                    if ($esDivisa) {
                        // Reducir saldo_base (incluye el descuento en divisa registrado en pagoPedido->descuento)
                        $montoReduccion = (float) ($pagoPedido->monto + $pagoPedido->descuento);
                        $pedido->saldo_base = max((float) $pedido->saldo_base - $montoReduccion, 0);

                        // Reducir saldo_ajustes por el monto explícitamente registrado en el pagoPedido
                        $ajustesAplicados = (float) ($pagoPedido->ajustes_monto ?? 0);
                        if ($ajustesAplicados > 0.001) {
                            $pedido->saldo_ajustes = max((float) ($pedido->saldo_ajustes ?? 0) - $ajustesAplicados, 0);
                        }

                        // Safety net: si la base quedó en 0, cerrar cualquier remanente de ajustes también
                        if ($pedido->saldo_base <= 0.01) {
                            $pedido->saldo_base    = 0;
                            $pedido->saldo_ajustes = 0;
                        }

                        // Aplicar IVA en divisa si fue registrado
                        if ((float) ($pagoPedido->iva ?? 0) > 0.001) {
                            $pedido->saldo_iva_bs = max(
                                (float) $pedido->saldo_iva_bs - (float) $pagoPedido->iva,
                                0
                            );
                        }
                    }

                    $pedido->estatus = $this->determinarEstatus($pedido);
                    $pedido->save();
                    if ($pedido->estatus === 'PAGADO') {
                        PedidoAjuste::marcarPagados((int) $pedido->id);
                    }
                }

                // ── Liquidación forzada para pagos multi-pedido ────────────────────────────
                // Regla de negocio: si el grupo de pago cubre más de un pedido, el monto
                // registrado DEBE cubrir el total de todos los saldos.  Al aprobarse el
                // último pago pendiente del grupo, se fuerzan todos los saldos a 0 para
                // garantizar el cierre completo (incluyendo diferencias de redondeo y
                // descuentos en divisa que no redujeron saldo_base en el momento del registro).
                $pedidosALiquidar = collect();

                if ($pago->pago_grupo_id) {
                    // Releer el grupo completo (ya incluye el pago recién aprobado)
                    $pagosDelGrupo = Pago::where('pago_grupo_id', $pago->pago_grupo_id)->get();

                    $pedidosEnGrupo = PagoPedido::whereIn('pago_id', $pagosDelGrupo->pluck('id'))
                        ->distinct()
                        ->pluck('pedido_id');

                    $esMultiPedido       = $pedidosEnGrupo->count() > 1;
                    $todosPagosAprobados = $pagosDelGrupo->every(fn ($p) => $p->estatus === 'APROBADO');

                    if ($esMultiPedido && $todosPagosAprobados) {
                        $pedidosALiquidar = $pedidosEnGrupo;
                    }
                } else {
                    // Pago sin grupo: si cubre varios pedidos directamente, liquidar todos
                    $pedidosEnPago = $pagosPedidos->pluck('pedido_id')->unique();
                    if ($pedidosEnPago->count() > 1) {
                        $pedidosALiquidar = $pedidosEnPago;
                    }
                }

                foreach ($pedidosALiquidar as $pedidoId) {
                    $p = Pedido::on('company')->find($pedidoId);
                    if (!$p) continue;
                    $p->saldo_base    = 0;
                    $p->saldo_ajustes = 0;
                    // Si el pedido tiene retención de IVA pendiente, NO forzar saldo_iva_bs a 0.
                    // El saldo_iva_bs se saldará cuando el admin valide el comprobante de retención.
                    if ((float) $p->porc_retencion > 0 && (float) $p->saldo_iva_bs > 0.01) {
                        $p->estatus = $this->determinarEstatus($p);
                    } else {
                        $p->saldo_iva_bs = 0;
                        $p->estatus      = 'PAGADO';
                    }
                    $p->save();
                    if ($p->estatus === 'PAGADO') {
                        PedidoAjuste::marcarPagados((int) $pedidoId);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'estatus' => $pago->estatus,
                'message' => $request->estatus === 'RECHAZADO' ? 'Pago rechazado y pedidos devueltos a APROBADO' : 'Pago aprobado'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estatus del pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determina el estatus correcto de un pedido según sus saldos.
     * Un pedido es PAGADO solo si base, IVA y ajustes son todos cero.
     */
    private function determinarEstatus(Pedido $pedido): string
    {
        $baseOk    = (float) $pedido->saldo_base <= 0.01;
        $ivaOk     = (float) $pedido->saldo_iva_bs <= 0.01;
        $ajustesOk = (float) ($pedido->saldo_ajustes ?? 0) <= 0.01;

        return ($baseOk && $ivaOk && $ajustesOk) ? 'PAGADO' : 'APROBADO';
    }

    /**
     * Aprueba el comprobante de retención de IVA de un pago.
     * Al validar la retención, se salda el saldo_iva_bs pendiente (la retención).
     */
    public function aprobarRetencion(Request $request, $id)
    {
        $request->validate([
            'comprobante_retencion' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $pago = Pago::findOrFail($id);

            // Guardar comprobante si fue adjuntado
            $rutaComprobante = null;
            if ($request->hasFile('comprobante_retencion')) {
                $rutaComprobante = $request->file('comprobante_retencion')
                    ->store('comprobantes/retenciones', 'public');
            }

            $pagosPedidos = $pago->pago_pedidos()->get();

            foreach ($pagosPedidos as $pagoPedido) {
                $pedido = Pedido::on('company')->find($pagoPedido->pedido_id);
                if (!$pedido) continue;

                // La retención en pagos_pedidos representa el monto Bs pendiente de comprobante
                $retencionBs = (float) ($pagoPedido->retencion ?? 0);
                if ($retencionBs <= 0.001) continue;

                // Saldar el saldo_iva_bs con la retención validada
                $pedido->saldo_iva_bs = max((float) $pedido->saldo_iva_bs - $retencionBs, 0);

                // Guardar comprobante en el pedido
                if ($rutaComprobante) {
                    $pedido->comprobante_retencion = $rutaComprobante;
                }

                $pedido->estatus = $this->determinarEstatus($pedido);
                $pedido->save();

                if ($pedido->estatus === 'PAGADO') {
                    PedidoAjuste::marcarPagados((int) $pedido->id);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Retención de IVA aprobada correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar retención: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Obtener detalles de un pago
    public function detalle($id)
    {
        $pago = Pago::with('tipo_pago', 'banco', 'pago_destino')->findOrFail($id);
        $tasaPago = (float) ($pago->rate ?? 0);
        
        // Obtener los pagos_pedidos asociados
        $pagosPedidos = $pago->pago_pedidos()->with('pedido')->get();
        
        $detalles = $pagosPedidos->map(function($pagoPedido) use($pago, $tasaPago) {
            // Calcular el monto total del pedido
            $pedido = $pagoPedido->pedido;
            $detallesPedido = $pedido->pedido_detalle;
            $subtotal = 0;
            $ivaTotal = 0;
            foreach($detallesPedido as $det) {
                $base = $det->cantidad * $det->precio_dolar;
                $subtotal += $base;
                $ivaTotal += $base * (($det->iva ?? 0) / 100);
            }
            $totalConIva = $subtotal + $ivaTotal;
            
            $retencion = 0;
            if (($pedido->porc_retencion ?? 0) > 0) {
                $retencion = $ivaTotal * ($pedido->porc_retencion / 100);
            }
            
            $descuento = $pedido->descuento ?? 0;
            $montoTotalPedido = $totalConIva - $retencion - $descuento;

            // Calcular el total pagado para este pedido (SUMA DE TODOS LOS PAGOS + DESCUENTOS)
            $totalPagado = \App\Models\PagoPedido::where('pedido_id', $pedido->id)->sum('monto');
            $totalDescuentosPagos = \App\Models\PagoPedido::where('pedido_id', $pedido->id)->sum('descuento');
            
            // El saldo real es el total del pedido menos (lo pagado + los descuentos otorgados en pagos)
            $saldoPendiente = $montoTotalPedido - ($totalPagado + $totalDescuentosPagos);
            
            if (abs($saldoPendiente) < 0.01) {
                $saldoPendiente = 0;
            }

            $montoBaseUsd = round((float) ($pagoPedido->monto ?? 0), 2);
            $montoAjusteUsd = round((float) ($pagoPedido->ajustes_monto ?? 0), 2);
            $montoBaseBs = round($montoBaseUsd * $tasaPago, 2);
            $montoAjusteBs = round($montoAjusteUsd * $tasaPago, 2);

            return [
                'cliente' => $pagoPedido->pedido->descripcion,
                'tipo_pago' => $pago->tipo_pago,
                'pago_destino' => $pago->pago_destino,
                'fecha_pedido' => formatoFechaDMASimple($pagoPedido->pedido->fecha),
                'monto_pagado' => number_format($pagoPedido->monto, 2, ',', '.'),
                'descuento' => $pagoPedido->pedido->monto_descuento,
                'id' => $pagoPedido->pedido->id,
                'monto' => $pagoPedido->monto,
                'monto_bs' => $montoBaseBs,
                'iva' => $pagoPedido->iva,
                'retencion' => $pagoPedido->retencion,
                'dcto' => $pagoPedido->descuento,
                'ajustes_monto' => $montoAjusteUsd,
                'ajustes_bs' => $montoAjusteBs,
                'monto_total_pedido' => $montoTotalPedido,
                'saldo_pendiente' => $saldoPendiente
            ];
        });

        $totalCargosAjustes = round($detalles->sum(function ($detalle) {
            $ajuste = (float) ($detalle['ajustes_monto'] ?? 0);

            return $ajuste > 0 ? $ajuste : 0;
        }), 2);

        $totalDescuentosAjustes = round($detalles->sum(function ($detalle) {
            $ajuste = (float) ($detalle['ajustes_monto'] ?? 0);

            return $ajuste < 0 ? abs($ajuste) : 0;
        }), 2);

        $netoAjustes = round($totalCargosAjustes - $totalDescuentosAjustes, 2);
        $totalCargosAjustesBs = round($totalCargosAjustes * $tasaPago, 2);
        $totalDescuentosAjustesBs = round($totalDescuentosAjustes * $tasaPago, 2);
        $netoAjustesBs = round($netoAjustes * $tasaPago, 2);

        $resumenCalculos = [
            'subtotal_bs' => round($detalles->sum('monto_bs') + $netoAjustesBs, 2),
            'exento_bs' => 0,
            'base_bs' => round($detalles->sum('monto_bs'), 2),
            'impuesto_bs' => round($detalles->sum(function ($detalle) {
                return (float) ($detalle['iva'] ?? 0);
            }), 2),
            'retencion_bs' => round($detalles->sum(function ($detalle) {
                return (float) ($detalle['retencion'] ?? 0);
            }), 2),
            'total_bs' => round(
                $detalles->sum('monto_bs') +
                $netoAjustesBs +
                $detalles->sum(function ($detalle) {
                    return (float) ($detalle['iva'] ?? 0);
                }) -
                $detalles->sum(function ($detalle) {
                    return (float) ($detalle['retencion'] ?? 0);
                }),
                2
            ),
            'tasa' => $tasaPago,
            'ajustes' => $detalles->filter(function ($detalle) {
                return abs((float) ($detalle['ajustes_monto'] ?? 0)) > 0.001;
            })->values()->map(function ($detalle) {
                $ajuste = (float) ($detalle['ajustes_monto'] ?? 0);

                return [
                    'pedido_id' => $detalle['id'],
                    'concepto' => 'Ajuste neto del pedido',
                    'monto' => abs($ajuste),
                    'monto_bs' => abs((float) ($detalle['ajustes_bs'] ?? 0)),
                    'es_cargo' => $ajuste >= 0,
                ];
            })->toArray(),
            'totales_ajustes' => [
                'cargos_usd' => $totalCargosAjustes,
                'descuentos_usd' => $totalDescuentosAjustes,
                'neto_usd' => $netoAjustes,
                'cargos_bs' => $totalCargosAjustesBs,
                'descuentos_bs' => $totalDescuentosAjustesBs,
                'neto_bs' => $netoAjustesBs,
            ],
        ];
        
        // Obtener archivos del pago_grupo
        $archivos = [];
        if ($pago->pago_grupo_id) {
            $pagoGrupo = \App\Models\PagoGrupo::with('archivos')->find($pago->pago_grupo_id);
            if ($pagoGrupo) {
                $archivos = $pagoGrupo->archivos->map(function($archivo) {
                    $rutaArchivo = ltrim((string) $archivo->ruta, '/');

                    return [
                        'nombre_original' => $archivo->nombre_original,
                        'ruta'            => asset('storage/' . $rutaArchivo),
                        'tipo_mime'       => $archivo->tipo_mime,
                        'es_imagen'       => $archivo->esImagen(),
                        'es_pdf'          => $archivo->esPdf(),
                    ];
                })->toArray();
            }
        }

        // Texto informativo para la modal: prioriza observaciones y luego descripcion.
        $detallePago = trim((string) ($pago->observaciones ?? ''));
        if ($detallePago === '') {
            $detallePago = trim((string) ($pago->descripcion ?? ''));
        }

        return response()->json([
            'detalles' => $detalles->toArray(),
            'pago'     => $pago,
            'archivos' => $archivos,
            'detalle_pago' => $detallePago,
            'resumen_calculos' => $resumenCalculos,
        ]);
    }
}
