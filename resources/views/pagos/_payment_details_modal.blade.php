<!-- Modal Detalles del Pago -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">Detalles del Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Información del Pago</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Monto del Pago:</label>
                                    <p id="paymentAmount" class="form-control-plaintext"></p>
                                </div>
                                <div class="form-group">
                                    <label>Fecha del Pago:</label>
                                    <p id="paymentDate" class="form-control-plaintext"></p>
                                </div>
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <p id="paymentDescription" class="form-control-plaintext"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Pedido Asociado</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Cliente:</label>
                                    <p id="orderClient" class="form-control-plaintext"></p>
                                </div>
                                <div class="form-group">
                                    <label>Fecha del Pedido:</label>
                                    <p id="orderDate" class="form-control-plaintext"></p>
                                </div>
                                <div class="form-group">
                                    <label>Estado del Pedido:</label>
                                    <p id="orderStatus" class="form-control-plaintext"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
