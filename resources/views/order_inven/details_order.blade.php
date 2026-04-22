@include('order_inven.partials.functions')

<div class="row">
    
    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
      <div class="card card-seller bg-blue-ligth">
        <div class="card-body">
          <h5 class="card-title text-primary"></h5>
          <h5 class="card-text text-info"><b><i class="fa fa-address-card"></i> Información del Cliente </b></h5>
          <input type="hidden" value="{{@$pedido->id}}" id="pedido_id" />
          <div class="row pb-3" >
              <div class="col-12">
                    <b>{{@$pedido->rif}} - {{@$pedido->descripcion}} </b>
              </div>
              <div class="col-12">
                    <i class="fa fa-calendar"></i> {{formatoFechaDMA(@$pedido->created_at)}}  
              </div>
          </div>    
          <button onclick="javascript:history.back()" class="btn btn-outline-primary btn-block btn-primary-seller"><i class="fas fa-arrow-left"></i> Regresar</button>          
        </div>
      </div>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
        <div class="row pt-3">
          
          
        </div>
        <div class="card card-seller">
              <div class="card-header border-transparent">
                <h3 class="card-title text-info">
                  <b>
                    <i class="fa fa-shopping-cart"></i> 
                    @if (@$pedido->deposito)
                      Depósito: {{$pedido->deposito->DDEPOS}}
                    @else
                      Información del Pedido
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
                    <ul class="products-list product-list-in-card pl-2 pr-2" style="text-align: left;">
                        @php($total = 0)
                        @foreach ($detalle as $item)
                            @php($total += ($item->cantidad * $item->precio_dolar))
                            <li class="item">                        
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        <span class="badge badge-primary mr-2">{{$item->cantidad}}</span>
                                        {{$item->inven_descr}}
                                        <span class="badge badge-primary badge-lg float-right">${{$item->cantidad * $item->precio_dolar}}</span>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
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
