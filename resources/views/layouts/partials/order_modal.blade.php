<div class="modal fade" id="modal_products">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modificar Producto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('modal_cantidad', 'Cantidad', ['class' => 'control-label']) }}
                            {!! Form::number('modal_cantidad', 1, [
                                'class' => 'form-control ',
                                'placeholder' => 'Cantidad',
                                'id' => 'modal_cantidad',
                            ]) !!}
                            <input type="hidden" id="modal_order_id" />
                            <input type="hidden" id="modal_codigo_inven" />
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('modal_cantidad_nota', 'Cant.  Nota', ['class' => 'control-label']) }}
                            {!! Form::number('modal_cantidad_nota', 0, [
                                'class' => 'form-control ',
                                'placeholder' => 'Cantidad',
                                'id' => 'modal_cantidad_nota',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            {{ Form::label('modal_precio_dolar', 'Precio', ['class' => 'control-label']) }}
                            {!! Form::text('modal_precio_dolar', 1, [
                                'class' => 'form-control ',
                                'placeholder' => 'Precio',
                                'id' => 'modal_precio_dolar',
                            ]) !!}
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 mt-2">
                        <button class="btn btn-primary btn-block" onclick="saveProduct()"><i class="fa fa-check"></i>
                            Actualizar</button>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 mt-2">
                        <button type="button" class="btn btn-outline-danger btn-block" data-dismiss="modal"
                            aria-label="Close">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
