<!-- Modal para Ver Detalle de Productos -->
<div class="modal fade" id="modalDetalleComisiones" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title font-weight-bold" id="modalDetalleLabel">
                    <i class="fas fa-list-ul mr-2"></i>Detalle de Comisión por Productos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Código</th>
                                <th style="width: 40%">Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Comisión</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody id="detalleComisionesBody">
                            <!-- Se carga vía AJAX -->
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3" class="text-right">TOTAL COMISIÓN:</td>
                                <td class="text-right text-success" id="totalDetalleComision">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Pago de Comisión (Diseño Premium) -->
<div class="modal fade modal-premium" id="modalVerPagoComision" tabindex="-1" role="dialog"
    aria-labelledby="modalVerPagoComisionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl">
            <!-- Sello de Recibido (Sólo visible cuando está confirmado) -->
            <div id="ver_pago_recibido_wrapper" class="recibido-stamp" style="display: none;">
                <i class="fas fa-check-double mr-1"></i> Confirmado
            </div>

            <div class="modal-header bg-gradient-navy text-white border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-white-10 p-2 rounded-circle mr-3">
                        <i class="fas fa-file-invoice-dollar fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalVerPagoComisionLabel">
                            Comprobante de Comisión
                        </h5>
                        <small class="text-white-50">Consulta detallada del desembolso</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4 bg-white">
                <!-- Header de Montos -->
                <div class="payment-amount-header text-center" style="background-color: #3275b8;">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="detail-label mb-0 text-white">Monto en Divisas</div>
                            <div class="h2 font-weight-bold text-white mb-0" id="ver_pago_monto_divisa">$0.00</div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light rounded-pill py-1 px-2 text-sm text-muted border">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="detail-label mb-0 text-white">Equivalente en Bolívares</div>
                            <div class="h2 font-weight-bold text-white mb-0" id="ver_pago_monto_bs">0.00 Bs</div>
                        </div>
                    </div>
                </div>

                <!-- Detalle en Grid -->
                <div class="row g-3">
                    <div class="col-md-4 mb-3">
                        <div class="payment-detail-item">
                            <div class="detail-label">Fecha de Operación</div>
                            <div class="detail-value text-navy" id="ver_pago_fecha">-</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="payment-detail-item">
                            <div class="detail-label">Tasa de Cambio</div>
                            <div class="detail-value" id="ver_pago_tasa">0.0000</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="payment-detail-item border-left-info" style="border-left-width: 3px !important;">
                            <div class="detail-label">Nro. Referencia</div>
                            <div class="detail-value text-navy" id="ver_pago_referencia">-</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="payment-detail-item">
                            <div class="detail-label">Banco / Destino</div>
                            <div class="detail-value" id="ver_pago_destino">-</div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="payment-detail-item">
                            <div class="detail-label">Método de Pago</div>
                            <div class="detail-value" id="ver_pago_forma">-</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="payment-detail-item bg-light border-0">
                            <div class="detail-label"><i class="fas fa-sticky-note mr-1"></i> Notas Administrativas
                            </div>
                            <div class="detail-value font-italic text-muted small" id="ver_pago_observaciones">Sin
                                observaciones adicionales</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 py-3">
                <input type="hidden" id="ver_pago_id_oculto">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <p class="text-muted small mb-0"><i class="fas fa-shield-alt mr-1"></i> Documento verificado por
                        el sistema</p>
                    <div>
                        <button type="button" class="btn btn-outline-secondary px-4 hov-danger" data-dismiss="modal"
                            style="border-radius: 10px;">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Grupo de Comisiones (Pagos Agrupados) -->
<div class="modal fade" id="modalGrupoComisiones" tabindex="-1" role="dialog" aria-labelledby="modalGrupoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-info text-white border-0">
                <h5 class="modal-title font-weight-bold" id="modalGrupoLabel">
                    <i class="fas fa-layer-group mr-2"></i>Pedidos Incluidos en este Pago
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha Pedido</th>
                                <th>ID Pedido</th>
                                <th>Cliente</th>
                                <th class="text-right">Comisión</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="grupoComisionesBody">
                            <!-- Se carga vía AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <input type="hidden" id="modal_grupo_identificador">
                <div class="d-flex w-100 justify-content-between align-items-center px-3">
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Verifique los pedidos antes de
                        confirmar</small>
                    <div>
                        <button type="button" class="btn btn-success px-4 font-weight-bold shadow-sm"
                            id="btnConfirmarRecibidoGrupo" style="display: none; border-radius: 10px;">
                            <i class="fas fa-check-double mr-1"></i> CONFIRMAR RECEPCIÓN
                        </button>
                        <button type="button" class="btn btn-secondary px-4 hov-danger" data-dismiss="modal"
                            style="border-radius: 10px;">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
