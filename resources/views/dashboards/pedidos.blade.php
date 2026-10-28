


@php
  $vendedorNotificacionesId = \App\Models\Vendedor::query()
    ->whereRaw('LOWER(TRIM(COALESCE(email, ""))) = ?', [strtolower(trim((string) auth()->user()->email))])
    ->value('id');

  $pagosRechazados = \App\Models\Pago::select('pagos.*')
    ->selectSub(function ($query) {
      $query->from('pagos_pedidos as pp')
        ->join('pedidos as ped', 'ped.id', '=', 'pp.pedido_id')
        ->join('CLIENTE as c', function ($join) {
          $join->whereRaw('c.RIF COLLATE utf8mb4_unicode_ci = ped.rif COLLATE utf8mb4_unicode_ci');
        })
        ->whereColumn('pp.pago_id', 'pagos.id')
        ->selectRaw("GROUP_CONCAT(DISTINCT c.NOMBRE ORDER BY c.NOMBRE SEPARATOR ', ')");
    }, 'cliente_nombre')
    ->where(function ($query) use ($vendedorNotificacionesId) {
        $query->where('user_id', auth()->id());

        if ($vendedorNotificacionesId) {
            $query->orWhere('seller_id', $vendedorNotificacionesId);
        }
    })
        ->whereRaw('UPPER(TRIM(COALESCE(estatus, ""))) = "RECHAZADO"')
        ->where('updated_at', '>=', now()->subDays(7))
        // No mostrar la alerta cuando cada pedido de este pago rechazado
        // ya fue cubierto por un pago posterior aprobado del mismo vendedor.
        /*
        ->whereNotExists(function ($query) {
            $query->from('pagos_pedidos as pp_rech')
                ->whereColumn('pp_rech.pago_id', 'pagos.id')
                ->whereNotExists(function ($inner) {
                    $inner->from('pagos_pedidos as pp_apr')
                        ->join('pagos as p_apr', 'p_apr.id', '=', 'pp_apr.pago_id')
                        ->whereColumn('pp_apr.pedido_id', 'pp_rech.pedido_id')
                        ->whereColumn('p_apr.user_id', 'pagos.user_id')
                        ->where('p_apr.estatus', 'APROBADO')
                        ->whereColumn('p_apr.created_at', '>', 'pagos.created_at');
                });
        })
        */
        ->orderByDesc('updated_at')
        ->take(5)
        ->get();
@endphp

@if (hasOrderPermission() )

      @include('dashboards.modulos')
       
        <div class="row">
            <div class="col-md-8 col-sm-12 col-xs-12">
              <div class="card card-primary card-outline">
                <div class="card card-primary card-tabs">
                  <div class="card-header p-0 pt-1">
                      <ul class="nav nav-tabs" id="custom-tabs-two-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-two-home-tab" data-toggle="pill" href="#custom-tabs-two-home" role="tab" aria-controls="custom-tabs-two-home" aria-selected="true">Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-pagos-tab" data-toggle="pill" href="#custom-tabs-pagos" role="tab" aria-controls="custom-tabs-pagos" aria-selected="false">Pagos Recibidos</a>
                        </li>
                                                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-three-profile-tab" data-toggle="pill" href="#custom-tabs-three-profile" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">Vendedores</a>
                        </li>
                       
                      </ul>                                
                    </div>
                  <div class="card-body">
                <div class="tab-content" id="custom-tabs-two-tabContent">
                  <div class="tab-pane fade" id="custom-tabs-pagos" role="tabpanel" aria-labelledby="custom-tabs-pagos-tab">
                      <div class="card-body" id="table-pagos">
                          <div class="row">
                              <div class="col-12">
                                  <h4 class="text-primary">Pagos Recibidos Pendientes</h4>
                              </div>
                          </div>
                          <div class="row">
                              <div class="col-12">
                                  <script>
                                      window.URL_BASE_PAGOS = "{{ url('pagos') }}";
                                      window.CSRF_TOKEN = "{{ csrf_token() }}";
                                  </script>
                                  <table class="table table-striped table-responsive table-valign-middle" id="tabla-pagos">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th>Vendedor</th>
                                            <th>Ref.</th>
                                            <th>Tipo Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="tab-pane fade active show" id="custom-tabs-two-home" role="tabpanel" aria-labelledby="custom-tabs-two-home-tab">
                      <div class="card-body" id="table-cxc">
                        
                        <div class="row">
                          <div class="col-12">
                            <h4 class="text-primary">Listado de Pedidos Pendientes</h4>
                          </div>
                        </div>

                        
                        {{-- PEDIDOS --}}
                        @php
                          $impuesto = DB::connection('company')->table('IMPUEST')->where('DIMPUEST', 'IVA')->value('PORCEN');
                          $orders = (new \App\Models\Pedido)->getPendingOrders();
                          $groupedOrders = $orders->groupBy('seller_code')->map(function ($orders) {
                              return $orders->groupBy('id'); // Agrupar por cliente (RIF)
                          });
                        @endphp

                        <div class="modal" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-dialog-centered" role="document">
                              <div class="modal-content text-center">
                                  <div class="modal-body">
                                      <div class="spinner-border text-primary" role="status">
                                          <span class="sr-only">Procesando...</span>
                                      </div>
                                      <p class="mt-2">Procesando...</p>
                                  </div>
                              </div>
                          </div>
                      </div>

                        <div class="row">
                          <div class="col-12">
                              @foreach($groupedOrders as $clients)
                                  @php
                                      // Obtener el nombre del vendedor basado en el user_id
                                      //$seller = \App\User::find($userId); // Ajusta según tu modelo de usuario
                                      
                                      $clientsCollection = collect($clients);
                                      $firstClient = $clientsCollection->first();
                                  @endphp
                                  <div class="card card-info collapsed-card">
                                      <div class="card-header">
                                          <h3 class="card-title">
                                              {{ @$firstClient[0]->seller_code }}
                                          </h3>
                                          <div class="card-tools">
                                              <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                  <i class="fas fa-plus"></i>
                                              </button>
                                          </div>
                                      </div>
                                      <div class="card-body p-0" style="display: none;">
                                          <div class="client-info container pt-4">
                                                  <small class="text-danger"><i class="fa fa-info-circle"></i> Para modificar la cantidad o precio de un producto solo debe seleccionarlo e ingresar los valores deseados</small>
                                              </div>
                                          @foreach($clients as $rif => $orders)
                                              @php
                                                  // Convertir a colección para poder usar first()
                                                  $ordersCollection = collect($orders);
                                                  // Obtener el primer pedido para acceder a la descripción
                                                  $firstOrder = $ordersCollection->first();
                                              @endphp
                                              
                                              <div class="client-info container pt-4" id="div_{{@$firstOrder->id}}">
                                                  @if (@$firstOrder->rif_foto)
                                                      <a href="#" onclick="event.preventDefault(); showRif('{{ $firstOrder->rif_foto }}')">
                                                        <span class="text-danger hint--top" aria-label="Click para ver foto del RIF">
                                                          <i class="fa fa-camera"></i>                                            
                                                      </span>
                                                      </a>
                                                  @endif
                                                  <a href="#" onclick="event.preventDefault(); changeClient('{{ $firstOrder }}')">
                                                    <strong class="text-primary hint--top" aria-label="Click para cambiar el cliente">Cliente: {{ $firstOrder->descripcion ?? 'No disponible' }} (RIF: {{ $firstOrder->rif }})</strong>
                                                  </a>
                                                  <br>
                                                  @if (@$firstOrder->telefono)
                                                    <i class="fa fa-phone"></i> {{ $firstOrder->telefono }} 
                                                  @endif
                                                  @if (@$firstOrder->email)
                                                    <i class="fa fa-envelope pl-3"></i> {{ $firstOrder->email }}
                                                  @endif

                                                  @if (@$firstOrder->cdepos)
                                                      @php
                                                          $depos = (new \App\Models\Deposito)->where('CDEPOS', $firstOrder->cdepos)->first();
                                                      @endphp
                                                      @if (@$depos)
                                                          <i class="fa fa-warehouse pl-3"></i> {{ $depos->DDEPOS }}
                                                      @endif
                                                  @endif
                                                  <span class="float-right">
                                                    <span class="badge badge-info">
                                                      <i class="fa fa-calendar"> {{formatoFechaDMA(@$firstOrder->created_at)}}</i>      
                                                    </span>
                                                  </span>
                                                  @if(@$firstOrder->descuento)
                                                      <div class="row pt-2">
                                                          <div class="col-md-12 text-danger">Descuento por Pago en Divisa: <b>
                                                              {{@$firstOrder->descuento}}%</b></div>
                                                      </div> 
                                                  @endif
                                                  @if(filled($firstOrder->observations))
                                                    <div class="alert alert-info border-left border-info mt-3 mb-2" role="note" style="border-left-width: 4px !important; white-space: pre-wrap;"><i class="far fa-sticky-note mr-1"></i><strong>Observaciones del pedido:</strong> {{ $firstOrder->observations }}</div>
                                                  @endif
                                                  <hr>
                                                  <ul class="products-list product-list-in-card pl-2 pr-2">
                                                      @php
                                                          $total = 0;
                                                          $n = 0;
                                                          $iva = 0;
                                                      @endphp
                                                      @foreach($ordersCollection as $order)
                                                          @php
                                                              $total += ($order->precio_dolar * $order->cantidad);
                                                              $n += $order->cantidad;
                                                              $estilo = $order->pago == 'Bs' ? 'primary' : 'success';
                                                              $pago = $order->pago == 'Bs' ? 'Bs' : '$';
                                                              //$simbolo = '$/' . $pago; 
                                                              $simbolo = 'Ref'; 
                                                              $iva += $order->iva>0 && $order->factura=='SI' ? ($order->precio_dolar * $order->cantidad)*($order->iva/100) : 0;
                                                          @endphp
                                                          <li class="item">
                                                            <a href="#" onclick="event.preventDefault(); changeProduct('{{ $order->id }}', '{{ $order->codigo_inven }}', '{{ $order->cantidad }}','{{ $order->precio_dolar }}', '{{ $order->pago }}')" style="color: unset !important">
                                                              <div class="product-info">
                                                                  <span class="product-title text-default"> {{ $order->codigo_inven }} - {!! $order->inven_descr !!}
                                                                    <span class="badge badge-{{$estilo}} float-right" id="p_tot_{{$order->id}}_{{$order->codigo_inven}}">{{ $simbolo }} {{ number_format($order->precio_dolar * $order->cantidad, 2, ',', '.') }}</span>
                                                                  </span>
                                                                  <span class="product-description">
                                                                      <p id="p_product_{{$order->id}}_{{$order->codigo_inven}}">
                                                                        Cant.: {{$order->cantidad}}
                                                                        <span class="pl-4"> Monto: {{$simbolo}} {{ number_format($order->precio_dolar, 2, ',', '.') }}</span>
                                                                      </p>
                                                                  </span>
                                                                </div>
                                                              </a>
                                                            </li>
                                                          @endforeach
                                                      </ul>
                                                      <div class="row">
                                                        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 w-100">
                                                          <div class="">
                                                               <span class="text-small w-100" id="n_{{$order->id}}">
                                                                <b>Items:</b> {{@$n}}
                                                              </span>
                                                              
                                                              <br>
                                                                <?php
                                                                  $base = $total;
                                                                  //$porc_iva = $impuesto ? $impuesto : 0; 
                                                                  //$iva= @$total*($porc_iva/100); 
                                                                  $retencion = @$firstOrder->cliageret ? ($iva*(@$firstOrder->porc_retencion/100)) : 0;
                                                                  $total = $base + ($iva - $retencion);
                                                                ?>
                                                                <b>IVA:</b> 
                                                                <span
                                                                    id="iva_{{ $order->id }}_{{ $order->codigo_inven }}">
                                                                    {{ number_format($iva, 2, ',', '.') }}
                                                                </span>
                                                                <br>
                                                                @if ($order->factura=='SI')
                                                                  <span class="badge badge-info">Factura</span>
                                                                @else
                                                                  <span class="badge badge-warning">Nota</span>
                                                                @endif
                                                              
                                                            </div>            
                                                          
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                                          <div class="">
                                                            <span class="text-small" id="iva_{{$order->id}}">
                                                              @if (@$firstOrder->porc_retencion && $firstOrder->porc_retencion > 0)
                                                                <b>% Retenc.</b> {{ @$firstOrder->porc_retencion }}%
                                                                <br>
                                                                (Ref: 
                                                                <span
                                                                    id="retencion_{{ $order->id }}_{{ $order->codigo_inven }}">
                                                                    {{ number_format($retencion, 2, ',', '.') }}
                                                                </span>
                                                                )
                                                              @endif
                                                            </span>            
                                                          </div>
                                                        </div>
                                                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                                                          <div class="container">
                                                            <div class="alert alert-danger alert-dismissible w-100">
                                                              <i class="icon fa fa-calculator"></i> 
                                                              <b id="gran_total_{{$order->id}}">
                                                                TOTAL: Ref {{number_format($total, 2, ',', '.')}}                               
                                                              </b>
                                                            </div>            
                                                          </div>
                                                        </div>
                                                      </div>

                                                      <div class="row">
                                                          <div class="col-xs-12 col-sm-12 col-md-4 pt-2">

                                                              <a id="btn_APROBADO_{{@$firstOrder->id}}" href="javascript:void(0)" onclick="event.preventDefault(); approveOrCancelOrder('{{@$firstOrder->id}}', 'APROBADO')" class="btn btn-outline-success btn-block pt-2 hint--top" aria-label="Aprobar Pedido"><i class="fa fa-check"></i> Aprobar</a>
                                                          </div>
                                                          <div class="col-xs-12 col-sm-12 col-md-4 pt-2">
                                                              <a id="btn_RECHAZADO_{{@$firstOrder->id}}" href="javascript:void(0)" onclick="event.preventDefault(); approveOrCancelOrder('{{@$firstOrder->id}}', 'RECHAZADO')" class="btn btn-outline-danger btn-block pt-2 hint--top" aria-label="Rechazar Pedido"><i class="fa fa-trash"></i> Rechazar</a>
                                                          </div>
                                                          <div class="col-xs-12 col-sm-12 col-md-4 pt-2">
                                                              <a target="_blank" href="{{url('print-order/' . @$firstOrder->id)}}" class="btn btn-outline-info btn-block pt-2 hint--top" aria-label="Imprimir Pedido"><i class="fa fa-print"></i> Imprimir</a>
                                                          </div>
                                                      </div>
                                                  </div>
                                              @endforeach
                                          </div>
                                          <div class="card-footer text-center" style="display: none;">
                                          </div>
                                      </div>
                                  @endforeach
                              </div>
                          </div>

                            {{-- FIN PEDIDOS --}}
                                          


                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-two-profile" role="tabpanel" aria-labelledby="custom-tabs-two-profile-tab">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5 pt-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                       <span class="input-group-text hint--top" id="start_at_btn" aria-label="Desde"><i class="far fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input class="form-control  birthday" placeholder="Desde" id="start_at" readonly="readonly" name="start_at" type="text" value="">
                                                    <div class="input-group-append">
                                                          <span class="input-group-text text-danger hint--top" id="clear_start_at_btn" aria-label="Limpiar"><i class="fa fa-times"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5 pt-2">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                       <span class="input-group-text hint--top" id="end_at_btn" aria-label="Hasta"><i class="far fa-calendar-alt"></i></span>
                                                    </div>
                                                    <input class="form-control  birthday" placeholder="Hasta" id="end_at" readonly="readonly" name="end_at" type="text" value="">
                                                    <div class="input-group-append">
                                                          <span class="input-group-text text-danger hint--top" id="clear_end_at_btn" aria-label="Limpiar"><i class="fa fa-times"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2 pt-2">
                                                <button id="btn-in-out" class="btn btn-block btn-primary hint--top" aria-label="Actualizar"><i class="fa fa-filter"></i></button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card-body table-responsive p-0">
                                                    <table class="table table-striped table-valign-middle">
                                                        <thead>
                                                            <tr>
                                                                <th>Pago</th>
                                                                <th>Ingresos</th>
                                                                <th>Egresos</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-in-out"><tr><td>Transferencia Bancaria</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr><tr><td>Divisa</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr><tr><td>Pago con Servicios</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr><tr><td>ZELLE</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr><tr><td>Pago Móvil</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr><tr><td>EUROS</td><td><a class="text-left btn btn-sm btn-block btn-outline-success hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/payment-relation-students"><b>$ 0</b></a></td><td><a class="text-left btn btn-sm btn-block btn-outline-danger hint--top" aria-label="Bs. 0" target="_blank" href="http://localhost/proyectos/sanfranciscodeasis_10/public/expenses"><b>$ 0</b></a></td></tr></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade show" id="custom-tabs-three-profile" role="tabpanel" aria-labelledby="custom-tabs-three-profile-tab">
                                        <div class="card-body" id="table-cxc">
                                          <div class="row">
                                            <div class="col-12">
                                              <h4 class="text-primary">Balances</h4>
                                            </div>
                                          </div>

                                          {{-- VENDEDORES --}}
                                          @php
                                            $sellers = (new \App\Models\Vendedor)->getData();                                            
                                          @endphp

                                          <ul class="users-list clearfix">
                                            @foreach($sellers as $seller)
                                                @php($photo = @$seller->user->photo ? 'storage/users/' . $seller->user->photo : 'imgs/users/nofoto.jpg')
                                                <li>
                                                  @php($seller_name = '')
                                                  @if (@$seller->user && (@$seller->user->name) || @$seller->user->last_name)
                                                    @php($seller_name = @$seller->user->name . ' ' . @$seller->user->last_name)
                                                  @endif
                                                  <img onclick="showClientTransactions('{{$seller->id}}');" class="img-seller" alt="{{$seller_name}}" data-cfsrc="{{asset($photo)}}" src="{{asset($photo)}}">
                                                  <a onclick="event.preventDefault(); showClientTransactions('{{@$seller->id}}')" class="users-list-name" href="#">{{ $seller_name }}</a>
                                                  <span class="users-list-date" id="totals_{{$seller->id}}">VENDIDO: Ref {{ number_format(@$seller->totales->sum('total'), 2, ',', '.') }}</span>
                                                  <span class="badge badge-danger" id="balance_{{$seller->id}}">SALDO: Ref {{ number_format(@$seller->saldo, 2, ',', '.') }}</span>
                                                </li>
                                            @endforeach                                            
                                          </ul>
                                        </div>
                                    </div>
                                    
                                    </div>
                            </div>
                        </div>
            </div>
            
          </div>

          @php($styles = ['warning', 'success', 'warning', 'danger', 'info'])
          @php($statuses = ['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EN REVISION'])
          @php($new_class = 'bg-warning')
          @php($new_status = 'SIN CONEXION')
          @php($new_class = 'bg-' . $styles[$company->company_status_id])

          <div class="col-md-4">
            <!-- Widget: user widget style 1 -->
            <div class="card card-widget widget-user">
              <!-- Add the bg color to the header using any of the bg-* classes -->
              <div class="widget-user-header company-status-color {{$new_class}}">
                <h3 class="widget-user-username">{{ Auth::user()->company->name }}</h3>
                <h5 class="widget-user-desc company-status-name">{{$statuses[$company->company_status_id]}}</h5>
              </div>
              <div class="widget-user-image">
                <img class="img-circle elevation-2" src="{{ asset('storage/logos/P7UKagWJlYFvoPZT7w5Wdseu0kJWeWifM93yF5c0.jpg') }}" alt="Logo">
              </div>
              <div class="card-footer p-0">
                @if($statuses[$company->company_status_id] == 'ACTIVO')
                  <ul class="nav flex-column permission-list">
                    <li class="nav-item">
                      <a href="{{ url('descuento-global')}}" class="nav-link">
                        <i class="fas fa-cogs"></i> Configuración
                      </a>
                    </li>
                    <li class="nav-item">
                    <li class="nav-item">
                      <a href="{{ url('order-inven')}}" class="nav-link">
                        <i class="fas fa-box"></i> Inventario
                      </a>
                    </li>
                    <li class="nav-item" style="background-color: #e2f1f8;">
                      <a href="{{ url('modificar-precios')}}" class="nav-link">
                        <i class="fas fa-tags"></i> Modificar Precios
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                      </a>
                    </li>
                    <li class="nav-item" style="background-color: #e2f1f8;">
                      <a href="{{ url('modificar-comisiones')}}" class="nav-link">
                        <i class="fas fa-percentage"></i> Modificar Comisiones
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('productos-futuros')}}" class="nav-link">
                        <i class="fas fa-rocket"></i> Inventario Futuro
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('marketing')}}" class="nav-link">
                        <i class="fas fa-envelope"></i> Marketing
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('pedidos') }}" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Pedidos
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('despachos') }}" class="nav-link">
                        <i class="fas fa-truck"></i> Despachos
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('descuentos')}}" class="nav-link">
                        <i class="fas fa-percent"></i> Descuentos para Vendedores
                        
                      </a>
                    </li>
                    <li class="nav-item" style="background-color: #e2f1f8;">
                        <a href="{{ url('admin/pagos/aprobar')}}" class="nav-link">
                        <i class="fas fa-file-excel" style="margin-right: 0.3em;"></i> Gestión de Pagos
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                        </a>
                      </li>     
                    <li class="nav-item" style="background-color: #e2f1f8;">
                        <a href="{{ url('admin/pagos')}}" class="nav-link">
                        <i class="fas fa-file-excel" style="margin-right: 0.3em;"></i> Listado de Pagos
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                        </a>
                      </li>      
                      <li class="nav-item">
                      <a href="{{ url('comisiones')}}" class="nav-link">
                        <i class="fas fa-chart-pie" style="margin-right: 0.3em;"></i> Comisiones para Vendedores
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('pago_destinos')}}" class="nav-link">
                        <i class="fas fa-dollar-sign"></i> Tipos de Pago/Banco recibidos
                        
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('vendedores') }}" class="nav-link">
                        <i class="fas fa-users"></i> Vendedores
                      </a>
                    </li>
                    <li class="nav-item" style="background-color: #e2f1f8;">
                      <a href="{{ url('banks')}}" class="nav-link">
                        <i class="fas fa-tags"></i> Bancos Clientes
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('order-clients') }}" class="nav-link">
                        <i class="fas fa-address-card"></i> Clientes
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{url('zonas')}}" class="nav-link">
                        <i class="fas fa-map-marker"></i> Zonas
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="tipprod" class="nav-link">
                        <i class="fas fa-layer-group"></i> Tipos de Producto
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{url('dpto')}}" class="nav-link">
                        <i class="fas fa-building"></i> Departamentos
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{url('unimed')}}" class="nav-link">
                        <i class="fas fa-object-group"></i> Unidades de Medida
                      </a>
                    </li>
                  </ul>
                @else
                  <ul class="nav flex-column permission-list">
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Debe ponerse al dia...
                      </a>
                    </li>                  
                  </ul>
                @endif
              </div>
            </div>
            <!-- /.widget-user -->

          
                       
            </div>
          </div>
         @ability('admin,owner', 'permission')
          <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              
            </div>
          </div>
          @endability
        
    </section>
    <!-- /.content -->
  </div>


 
    @include('layouts.partials.order_modal') 
    @include('layouts.partials.seller_modal') 
    @include('layouts.partials.order_functions') 
@else
  @if (hasOnlyDispatch() )
    <div class="row">
      @php($styles = ['warning', 'success', 'warning', 'danger', 'info'])
          @php($statuses = ['PENDIENTE', 'ACTIVO', 'INACTIVO', 'SUSPENDIDO', 'EN REVISION'])
          @php($new_class = 'bg-warning')
          @php($new_status = 'SIN CONEXION')
          @php($new_class = 'bg-' . $styles[$company->company_status_id])

          <div class="col-md-4">
            <!-- Widget: user widget style 1 -->
            <div class="card card-widget widget-user">
              <!-- Add the bg color to the header using any of the bg-* classes -->
              <div class="widget-user-header company-status-color {{$new_class}}">
                <h3 class="widget-user-username">{{ Auth::user()->company->name }}</h3>
                <h5 class="widget-user-desc company-status-name">{{$statuses[$company->company_status_id]}}</h5>
              </div>
              <div class="widget-user-image">
                <img class="img-circle elevation-2" src="{{ asset('storage/logos/P7UKagWJlYFvoPZT7w5Wdseu0kJWeWifM93yF5c0.jpg') }}" alt="Logo">
              </div>
              <div class="card-footer p-0">
                @if($statuses[$company->company_status_id] == 'ACTIVO')
                  <ul class="nav flex-column permission-list">
                    <li class="nav-item">
                        &nbsp;
                    </li>
                    <li class="nav-item">
                      <a href="{{ url('despachos') }}" class="nav-link">
                        <i class="fas fa-truck"></i> Despachos
                        <span class="badge badge-success ml-2" style="font-size: 0.8em;">Nuevo</span>
                      </a>
                    </li>
                  </ul>
                @else
                  <ul class="nav flex-column permission-list">
                    <li class="nav-item">
                      <a href="#" class="nav-link">
                        Debe ponerse al dia...
                      </a>
                    </li>                  
                  </ul>
                @endif
              </div>
            </div>
                       
            </div>
  </div>

@else

<!-- VENDEDORES -->
  @php($pending_commission = (new \App\Models\ComisionVendedor)->getPendingCommissions())
  @php($orders = (new \App\Models\Pedido)->getMyLastOrders(6))

  @if($pagosRechazados->isNotEmpty() || (isset($pedidosVencidos) && count($pedidosVencidos) > 0))
    <style>
      .seller-notifications {
        overflow: hidden;
        border: 1px solid #dfe8e5;
        border-top: 4px solid #28756c;
        border-radius: 10px;
        box-shadow: 0 8px 22px rgba(27, 45, 54, 0.08);
      }

      .seller-notifications-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1rem 1.15rem;
        background: linear-gradient(110deg, #f1f7f5 0%, #fff 72%);
      }

      .seller-notifications-mark {
        display: grid;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 50%;
        background: #dfefeb;
        color: #28756c;
      }

      .seller-notifications-total,
      .seller-notification-count {
        padding: 0.35rem 0.65rem;
        border: 1px solid #dce8e4;
        border-radius: 20px;
        background: #fff;
        color: #52635f;
        font-size: 0.78rem;
        font-weight: 700;
        white-space: nowrap;
      }

      .seller-notification-category + .seller-notification-category {
        border-top: 1px solid #e6e9ed;
      }

      .seller-notification-toggle {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 0.85rem 1.15rem;
        border: 0;
        background: transparent;
        color: #303943;
        text-align: left;
        font-weight: 600;
        transition: background-color 0.2s ease, color 0.2s ease;
      }

      .seller-notification-toggle:hover,
      .seller-notification-toggle:focus {
        color: #236a61;
        background: #f4f8f7;
        text-decoration: none;
      }

      .seller-notification-chevron {
        margin-left: auto;
        color: #77818b;
        transition: transform 0.2s ease;
      }

      .seller-notification-toggle[aria-expanded="true"] .seller-notification-chevron {
        transform: rotate(180deg);
      }

      .seller-notification-card {
        height: 100%;
        padding: 1rem;
        border: 1px solid #e6e9ed;
        border-left: 5px solid #bd4b3b;
        border-radius: 10px;
        background: #fbefed;
        box-shadow: 0 4px 12px rgba(27, 45, 54, 0.06);
        transition: opacity 0.35s ease, transform 0.35s ease;
      }

      .seller-notification-card.overdue {
        border-left-color: #d69b16;
        background: #fff9e8;
      }

      .seller-notification-card-heading {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin-bottom: 0.75rem;
      }

      .seller-notification-card-heading h6 {
        flex: 1;
        margin: 0;
        font-weight: 700;
      }

      .seller-notification-card-body {
        padding: 0.75rem;
        border-radius: 7px;
        background: rgba(255, 255, 255, 0.7);
        overflow-wrap: anywhere;
      }

      .seller-notification-card-body ul {
        max-height: 180px;
        overflow-y: auto;
      }

      @media (max-width: 575.98px) {
        .seller-notifications-header {
          gap: 0.55rem;
          padding: 0.85rem;
        }

        .seller-notifications-total {
          font-size: 0.7rem;
        }
      }
    </style>
    @php($totalNotificacionesVendedor = $pagosRechazados->count() + (isset($pedidosVencidos) ? count($pedidosVencidos) : 0))
    <section class="seller-notifications card mb-3" aria-labelledby="seller-notifications-title">
      <div class="seller-notifications-header">
        <span class="seller-notifications-mark"><i class="fas fa-bell" aria-hidden="true"></i></span>
        <div class="flex-fill">
          <h5 class="mb-0" id="seller-notifications-title">Notificaciones</h5>
          <small class="text-muted">Avisos importantes sobre pedidos y pagos</small>
        </div>
        <span class="seller-notifications-total"><span data-notification-total>{{ $totalNotificacionesVendedor }}</span> pendientes</span>
      </div>
      <div class="card-body p-0" id="sellerNotificationsAccordion">
        @if($pagosRechazados->isNotEmpty())
          <div class="seller-notification-category" data-notification-category>
            <h6 class="mb-0">
              <button class="seller-notification-toggle collapsed" type="button" data-toggle="collapse"
                data-target="#sellerRejectedPayments" aria-expanded="false" aria-controls="sellerRejectedPayments">
                <i class="fas fa-times-circle text-danger mr-2" aria-hidden="true"></i>
                Pagos rechazados
                <span class="seller-notification-count ml-2" data-category-count>{{ $pagosRechazados->count() }}</span>
                <i class="fas fa-chevron-down seller-notification-chevron" aria-hidden="true"></i>
              </button>
            </h6>
            <div id="sellerRejectedPayments" class="collapse" data-parent="#sellerNotificationsAccordion">
              <div class="row px-3 pt-2 pb-1">
                @foreach ($pagosRechazados as $pagoRec)
                  <div class="col-12 col-lg-6 mb-3 seller-notification-item" data-notification-count="1" data-pago-id="{{ $pagoRec->id }}">
                    <article class="seller-notification-card">
                      <div class="seller-notification-card-heading">
                        <i class="fas fa-times-circle text-danger mt-1" aria-hidden="true"></i>
                        <h6 style="color: #922b21;">Pago rechazado</h6>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" data-dismiss-notification title="Cerrar notificación" aria-label="Cerrar notificación">
                          <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                      </div>
                      <div class="small text-muted mb-2">
                        {{ \Carbon\Carbon::parse($pagoRec->updated_at)->format('d/m/Y H:i') }} &middot;
                        Cliente: <strong>{{ $pagoRec->cliente_nombre ?: 'No identificado' }}</strong> &middot;
                        Ref: <strong>{{ $pagoRec->referencia ?? 'N/A' }}</strong> &middot;
                        ${{ number_format($pagoRec->monto, 2) }}
                      </div>
                      <div class="seller-notification-card-body">
                        <div class="small text-uppercase font-weight-bold text-danger mb-1">
                          <i class="far fa-sticky-note mr-1" aria-hidden="true"></i>Observación del administrador
                        </div>
                        <div style="color: #5d2020; font-size: 0.88rem; line-height: 1.5;">{{ $pagoRec->observaciones ?: 'El administrador no agregó una observación.' }}</div>
                      </div>
                    </article>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        @if(isset($pedidosVencidos) && count($pedidosVencidos) > 0)
          <div class="seller-notification-category" data-notification-category>
            <h6 class="mb-0">
              <button class="seller-notification-toggle collapsed" type="button" data-toggle="collapse"
                data-target="#sellerOverdueOrders" aria-expanded="false" aria-controls="sellerOverdueOrders">
                <i class="fas fa-clock text-warning mr-2" aria-hidden="true"></i>
                ¡Tiene Pedidos Vencidos!
                <span class="seller-notification-count ml-2" data-category-count>{{ count($pedidosVencidos) }}</span>
                <i class="fas fa-chevron-down seller-notification-chevron" aria-hidden="true"></i>
              </button>
            </h6>
            <div id="sellerOverdueOrders" class="collapse" data-parent="#sellerNotificationsAccordion">
              <div class="row px-3 pt-2 pb-1">
                <div class="col-12 mb-3 seller-notification-item" data-notification-count="{{ count($pedidosVencidos) }}">
                  <article class="seller-notification-card overdue">
                    <div class="seller-notification-card-heading">
                      <i class="fas fa-exclamation-triangle text-warning mt-1" aria-hidden="true"></i>
                      <h6 style="color: #856404;">Usted tiene {{ count($pedidosVencidos) }} pedido(s) que han superado el plazo de crédito establecido.</h6>
                      <button type="button" class="btn btn-sm btn-link text-muted p-0" data-dismiss-notification title="Cerrar notificación" aria-label="Cerrar notificación">
                        <i class="fas fa-times" aria-hidden="true"></i>
                      </button>
                    </div>
                    <div class="seller-notification-card-body">
                      <small class="text-muted">Pedidos afectados:</small>
                      <ul class="mb-0 mt-2">
                        @foreach($pedidosVencidos as $pedido)
                          <li class="mb-1" style="color: #856404;">
                            <strong>#{{ str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}</strong> -
                            {{ $pedido->descripcion }}
                            <span class="badge badge-danger ml-2">Vencido hace {{ (int) $pedido->dias_vencidos }} días</span>
                          </li>
                        @endforeach
                      </ul>
                    </div>
                    <div class="mt-3">
                      <a href="{{ route('vendedores.pagos.clientes') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-hand-holding-usd mr-2" aria-hidden="true"></i>Ir a Gestión de Pagos
                      </a>
                    </div>
                  </article>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>
    </section>

    <script>
      document.querySelectorAll('[data-dismiss-notification]').forEach(function(button) {
        button.addEventListener('click', function() {
          var notification = button.closest('.seller-notification-item');
          var category = button.closest('[data-notification-category]');

          notification.style.opacity = '0';
          notification.style.transform = 'translateX(20px)';
          window.setTimeout(function() {
            notification.remove();
            if (!category.querySelector('.seller-notification-item')) {
              category.remove();
            } else {
              var categoryCount = Array.from(category.querySelectorAll('.seller-notification-item'))
                .reduce(function(total, item) {
                  return total + (Number(item.getAttribute('data-notification-count')) || 0);
                }, 0);
              category.querySelector('[data-category-count]').textContent = categoryCount;
            }

            var totalCount = Array.from(document.querySelectorAll('#sellerNotificationsAccordion .seller-notification-item'))
              .reduce(function(total, item) {
                return total + (Number(item.getAttribute('data-notification-count')) || 0);
              }, 0);
            document.querySelector('[data-notification-total]').textContent = totalCount;
            if (totalCount === 0) {
              document.querySelector('.seller-notifications').remove();
            }
          }, 350);
        });
      });
    </script>
  @endif

  @if($pending_commission->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle"></i> Tienes nuevas comisiones recibidas. Debes confirmar la recepción del pago. 
      <a href="{{ url('comisiones-recibidas') }}" class="ml-3 ">
          <b>
            <i class="fas fa-check"></i> Confirmar Comisiones Recibidas
          </b>
          </a>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <div class="row">
    
    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
      <div class="card card-seller">
        <img src="{{ getUserPhoto() }}" class="card-img-top-seller d-none" alt="...">
        <img src="{{ asset('imgs/logos/dacabe.png') }}" class="" alt="...">
        <div class="card-body">
          <h5 class="card-title text-primary"></h5>
          <p class="card-text"><b>Vendedor:</b><br>
          <b>{{auth()->user()->name}} {{auth()->user()->last_name}}</b></p>
          <a href="{{ url('shoppingcart') }}" class="btn btn-outline-primary btn-block btn-primary-seller">
            <i class="fas fa-shopping-cart"></i> Nuevo Pedido
          </a>
          <a href="{{ route('vendedores.pagos.clientes') }}" class="btn btn-outline-success btn-block btn-primary-seller">
            <i class="fas fa-money-bill-wave"></i> Registrar Pagos
          </a>
          <a href="{{ route('vendedor.lista-precios.index') }}" class="btn btn-outline-info btn-block btn-primary-seller">
            <i class="fas fa-tags"></i> Lista de Precios
          </a>
          <a href="{{ url('comisiones-recibidas') }}" class="btn btn-outline-warning btn-block btn-primary-seller">
            <i class="fas fa-money-bill-wave"></i> Comisiones Recibidas
          </a>
          <a href="{{ route('comisiones.mi_estado_cuenta') }}" class="btn btn-outline-info btn-block btn-primary-seller">
            <i class="fas fa-balance-scale"></i> Estado de Cuenta
          </a>
          <a href="{{ url('/vendedor/pedidos/historial') }}" class="btn btn-outline-danger btn-block btn-primary-seller">
            <i class="fas fa-history"></i> Historial de Pedidos
          </a>
          <a href="{{ url('/vendedor/dashboard/logros') }}" class="btn btn-outline-success btn-block btn-primary-seller">
            <i class="fas fa-trophy"></i> Mis Logros
          </a>
          <a href="{{ url('users/' . auth()->user()->id) }}" class="btn btn-outline-primary btn-block btn-primary-seller"><i class="fas fa-user"></i> Ver mi perfil</a>
        </div>
      </div>
      @if(obtenerDescuentoGlobal())
          <div class="card card-seller card-dacabe div-discount">
            <div class="card-body">
                <small>
                  <h4>NOTA IMPORTANTE</h4>
                    Precio 2 BBS tendrá un descuento de <h4 id="info-discount d-none"></h4>{{obtenerDescuentoGlobal()}}% si realiza su pago en divisa, en caso contrario se aplica BBS BCV
                </small>
            </div>
          </div>
        @endif
    </div>


    @php($promotions = (new App\Models\Promocion)->with('producto')->orderBy('promocion', 'DESC')->orderBy('nuevo', 'DESC')->orderBy('codigo')->get())
    @if ($promotions && sizeof(@$promotions))
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
        <div class="row pt-3 d-none">
          <div class="container">
            <div class="alert alert-success alert-dismissible w-100">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <h5><i class="icon fas fa-ban"></i> Atención</h5>
              DACABE ha aprobado su pedido de fecha 01/11/2024
            </div>            
          </div>          
        </div>

        <div class="card card-seller">
              <div class="card-header border-transparent">
                <h3 class="card-title"><b><i class="fa fa-shopping-cart"></i> Novedades</b></h3>

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
                <div class="row">
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 pt-0">
                     <div class="owl-carousel owl-theme">
                      @foreach($promotions as $item)
                        <div class="item">
                          <div class="card-deck">
                            <div class="card card-prod-dashboard" style="box-shadow: unset">
                                @php($foto = @$item->producto->FOTO)
                                @php($descr = @$item->producto->DESCR)
                                @if ($item->nuevo)
                                    <div class="ribbon-wrapper ribbon-lg">
                                      <div class="ribbon bg-success text-lg">
                                        Nuevo
                                      </div>
                                    </div>
                                @else
                                    @if (!$item->promocion)
                                        @php($foto = $item->foto)
                                        @php($descr = $item->descripcion)
                                        @if ($item->pronto)
                                            <div class="ribbon-wrapper ribbon-lg">
                                              <div class="ribbon bg-danger text-lg">
                                                Pronto
                                              </div>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                              <img class="card-img-top w-100" src="{{asset('storage/products/' . $foto)}}" alt="{{$descr}}">
                              <div class="card-body text-center">
                                <p class="text-center text-primary">{{$descr}}</p>
                                
                              
                                  <div class="row">
                                    <div class="col-12">
                                      @if ($item->promocion)
                                        <span class="mt-2 w-100 badge badge-warning" style="font-size: 1.1rem;">PROMOCION</span>
                                      @endif
                                                                          
</div>                                  
                                  </div>
                                </div>
                            </div>
                          </div>
                        </div>
                      @endforeach                    
                    </div>
                  </div>
                </div>
                <!-- /.table-responsive -->
              </div>
              <!-- /.card-body -->
              
            </div>          
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 co-lg-6">
    @else
      <div class="col-xs-12 col-sm-12 col-md-9 co-lg-9">
    @endif
        <div class="card card-seller">
              <div class="card-header border-transparent">
                <h3 class="card-title"><b><i class="fa fa-edit"></i> Últimos Pedidos</b></h3>

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
                  @if(@$orders)

                    <table class="table m-0">
                      <thead>
                      <tr style="text-align: left;">
                        <th>Estatus</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                      </tr>
                      </thead>
                      <tbody>
                        @foreach($orders as $order)
                          <tr style="text-align: left;">
                            @php($styles = ['PENDIENTE'=>'warning', 'APROBADO'=>'success', 'RECHAZADO'=>'danger', 'CARGANDO'=>'warning', 'PAGADO'=>'success', 'EN REVISION'=>'info'])
                            @php($style = $styles[$order->estatus])
                            @php($icons = ['PENDIENTE' => 'eye', 'EN REVISION' => 'money-bill','APROBADO' => 'check', 'RECHAZADO' => 'times','CARGANDO' => 'shopping-cart', 'PAGADO' => 'money-check-alt'])
                            @php($icon = $icons[$order->estatus])
                            @php($url = $order->estatus=='CARGANDO' ? 'view-cart': 'view-order/'.$order->id)
                            <td><span class="badge badge-{{$style}}"><i class="fa fa-{{$icon}}"></i> {{$order->estatus}}</span></td>
                            <td><a href="{{url($url)}}">{{formatoFechaDMASimple($order->fecha)}}</a></td>
                            @php($cliente = $order->rif ? $order->rif . ', ' . $order->descripcion : '')
                            <td><a href="{{url($url)}}">{{$cliente}}</a></td>
                            <td><a href="{{url($url)}}"><span class="badge badge-primary">Ref {{ number_format($order->total, 2, ',', '.') }}</span></a></td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @else
                    <h5 class="text-info"><i class="fa fa-eye"></i> No hay información para mostrar...</h5>
                  @endif
                </div>
              </div>              
            </div>   
      </div>
  </div>
</div>


<!-- Modal Detalle Pago -->
<div class="modal fade" id="modalDetallePago" tabindex="-1" role="dialog" aria-labelledby="modalDetallePagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalle del Pago</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Monto</label>
                            <div id="montoPago" class="form-control"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cliente</label>
                            <div id="clientePago" class="form-control"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha</label>
                            <div id="fechaPago" class="form-control"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal" aria-label="Close">Cerrar</button>
            </div>
        </div>  
    </div>    
</div>

</div>
</div>
@endif
@endif
