@include('order_inven.partials.functions')

@php($pending_commission = (new \App\Models\ComisionVendedor)->getPendingCommissions())

@if($pending_commission->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle"></i> Tienes nuevas comisiones recibidas. Debes confirmar la recepción del pago para poder continuar. 
      <a href="{{ url('comisiones-recibidas') }}" class="ml-3 ">
          <b>
            <i class="fas fa-check"></i> Confirmar Comisiones Recibidas
          </b>
          </a>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @else


    <div class="row">

        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <div class="card card-seller bg-blue-ligth">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="card-body">
                        <h5 class="card-text text-info"><b><i class="fa fa-address-card"></i> Información del Cliente </b>
                        </h5>
                        <input type="hidden" value="{{ @$pedido->id }}" id="pedido_id" />
                        <div class="row pb-3" style="text-align: left;">
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('search', 'Buscar Cliente', ['class' => 'control-label']) }}
                                    <div class="input-group input-group">
                                        <select class="form-control select2" data-placeholder="Seleccione"
                                            style="width: 100%;" id="search_client" name="search_client">
                                            @foreach (@$clients as $item)
                                                <option value="{{ $item->RIF }}">{{ $item->NOMBRE }}
                                                    ({{ $item->RIF }})
                                                </option>
                                            @endforeach
                                            <option value="" selected="selected">Seleccione cliente...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('rif', 'RIF', ['class' => 'control-label']) }}
                                    <div class="input-group input-group">
                                        {!! Form::text('rif', @$pedido->rif, [
                                            'class' => 'form-control ',
                                            'placeholder' => 'RIF',
                                            'required',
                                            'id' => 'rif',
                                        ]) !!}
                                        <span class="input-group-append hint--top" aria-label="Crear nuevo">
                                            <button id="btn_new" type="button" class="btn btn-info btn-flat"><i
                                                    class="fa fa-plus"></i></button>
                                        </span>
                                        <span class="input-group-append hint--top" aria-label="Buscar en SENIAT">
                                            <button id="btn_search" type="button" class="btn btn-success btn-flat"><i
                                                    class="fa fa-search"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('descripcion', 'Razón Social', ['class' => 'control-label']) }}
                                    {!! Form::text('descripcion', @$pedido->descripcion, [
                                        'class' => 'form-control ',
                                        'placeholder' => 'Razón Social',
                                        'required',
                                        'id' => 'descripcion',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('telefono', 'Teléfono', ['class' => 'control-label']) }}
                                    {!! Form::text('telefono', @$pedido->telefono, [
                                        'class' => 'form-control ',
                                        'placeholder' => 'Teléfono',
                                        'required',
                                        'id' => 'telefono',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('email', 'Email', ['class' => 'control-label']) }}
                                    {!! Form::text('email', @$pedido->email, [
                                        'class' => 'form-control ',
                                        'placeholder' => 'Email',
                                        'required',
                                        'id' => 'email',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-12" id="div_foto_rif">
                                <div class="form-group">
                                    {{ Form::label('photo', 'Foto del RIF', ['class' => 'control-label']) }}
                                    <div class="col-md-12 col-sm-12 col-xs-12 hint--top"
                                        aria-label="Click para cambiar la imagen">
                                        {{ Form::file('photo', ['class' => 'btn btn-outline-primary btn-block', 'id' => 'photo']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('conditions', 'Condiciones del pedido', ['class' => 'control-label']) }}
                                    {!! Form::text('conditions', @$pedido->conditions, [
                                        'class' => 'form-control ',
                                        'placeholder' => 'Condiciones del pedido',
                                        'id' => 'conditions',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    {{ Form::label('observations', 'Observaciones', ['class' => 'control-label']) }}
                                    {!! Form::text('observations', @$pedido->observations, [
                                        'class' => 'form-control ',
                                        'placeholder' => 'Observaciones',
                                        'id' => 'observations',
                                    ]) !!}
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 d-none">
                                <div class="form-group">
                                    {{ Form::label('factura', 'Factura', ['class' => 'control-label']) }}
                                    {!! Form::select('factura', ['SI' => 'SI', 'NO' => 'NO'], @$pedido->factura ?? 'SI', [
                                        'class' => 'form-control',
                                        'id' => 'factura',
                                    ]) !!}
                                </div>
                            </div>

                            <input type="hidden" name="codcli" id="codcli">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 d-none">
                                <div class="form-group">
                                    <input type="hidden" name="cliageret" id="cliageret">
                                    {{ Form::label('porc_retencion', '% Retención', ['class' => 'control-label']) }}
                                    {!! Form::number('porc_retencion', @$pedido->porc_retencion, [
                                        'class' => 'form-control',
                                        'placeholder' => '% Retención',
                                        'id' => 'porc_retencion',
                                        'step' => '0.01',
                                        'min' => '0',
                                    ]) !!}
                                </div>
                            </div>

                        </div>

                        @if (@$detalle && sizeof($detalle))
                            <a href="{{ url('shoppingcart') }}"
                                class="btn btn-outline-primary btn-block btn-primary-seller"><i
                                    class="fas fa-arrow-left"></i> Modificar Pedido</a>
                            <a href="{{ url('drop-order') }}"
                                class="btn btn-outline-danger btn-block btn-primary-seller"><i class="fas fa-times"></i>
                                Anular Pedido</a>
                            
                            <button class="btn btn-primary btn-block btn-primary-seller" id="btn_send" type="submit"><i
                                    class="fas fa-paper-plane"></i> Enviar Pedido</button>
                            
                        @else
                            <a href="{{ url('shoppingcart') }}"
                                class="btn btn-outline-primary btn-block btn-primary-seller"><i
                                    class="fas fa-arrow-left"></i> Cargar Productos</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
            <div class="row pt-3">
                <div class="container">
                    <div class="alert alert-info alert-dismissible w-100">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-eye"></i> Al modificar cantidades no olvide seleccionar <b>Guardar</b>
                    </div>
                </div>
            </div>
            <div class="card card-seller">
                <div class="card-header border-transparent">
                    <h3 class="card-title text-info">
                        <b>
                            <i class="fa fa-shopping-cart"></i> Productos
                            @if (@$pedido->deposito)
                                Depósito: {{ $pedido->deposito->DDEPOS }}
                            @endif
                        </b>
                    </h3>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if (@$detalle)
                            @php($total = 0)
                            @php($n = 0)
                            <ul class="products-list product-list-in-card pl-2 pr-2" style="text-align: left;">
                                @foreach ($detalle as $item)
                                    @php($total += $item->cantidad * $item->precio_dolar)
                                    @php($style = $item->pago == 'Bs' ? 'primary' : 'success')
                                    @php($pago = '$/' . $item->pago)
                                    @php($pago = 'Ref')
                                    @php($n += $item->cantidad)
                                    <li class="item" id="row_{{ $item->id }}">
                                        <div class="product-info pt-2 pl-3 pr-3">
                                            <a href="javascript:void(0)" class="product-title">{{ $item->inven_descr }}
                                                <span
                                                    class="badge badge-{{ $style }} badge-lg float-right">{{ $pago }}
                                                    <span id="subtotal_{{ $item->id }}">
                                                        {{ $item->cantidad * $item->precio_dolar }}</span></span>
                                            </a>
                                            <span class="product-description">
                                                <span class="row">
                                                    <span class="col-8 col-md-6 col-lg-6 w-100">
                                                        <div class="input-group mb-3">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-danger"
                                                                    onclick="deleteProduct('{{ $item->id }}')"><i
                                                                        class="fa fa-trash"></i></button>
                                                            </div>
                                                            <input type="number" class="form-control"
                                                                id="qty_{{ $item->id }}"
                                                                value="{{ $item->cantidad }}">
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-primary"
                                                                    id="btn_qty_{{ $item->id }}"
                                                                    onclick="saveQuantity('{{ $item->id }}')"><i
                                                                        class="fa fa-save"></i></button>
                                                            </div>
                                                        </div>
                                                    </span>

                                                </span>
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <hr>
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="container">
                                        <div class="alert alert-default alert-dismissible w-100">
                                            <i class="icon fas fa-shopping-cart"></i>
                                            <span class="text-small" id="n">
                                                Items: {{ $n }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                    <div class="container">
                                        <div class="alert alert-danger alert-dismissible w-100">
                                            <i class="icon fa fa-calculator"></i>
                                            <b id="grand_total">
                                                TOTAL: Ref {{ $total }}
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <h5 class="text-info"><i class="fa fa-eye"></i> No hay información para mostrar...</h5>
                        @endif
                    </div>

                    <!-- /.table-responsive -->
                </div>
                <!-- /.card-body -->

            </div>
        </div>
    </div>
@endif
