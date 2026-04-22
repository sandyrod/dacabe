@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Pedido')

@section('titulo_header', 'Pedido')
@section('subtitulo_header', 'Pedido')

@section('content')

  @php($user = (new \App\User)->find(@$order->user_id))
  @php($vendedor = @$user ? (new \App\Models\Vendedor)->where('email', @$user->email)->first() : null)
  @php($client = (new \App\Models\OrderClient)->where('RIF', @$order->rif)->first())

  <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            <!-- Main content -->
            <div class="invoice p-3 mb-3">
              <!-- title row -->
              <div class="row">
                <div class="col-3">
                  <h4>
                    <img class="responsive" width="400" src="{{ asset('storage/logos/'.$report_data['company']->logo) }}" alt="">
                  </h4>
                </div>
                <div class="col-7 invoice-col text-center">
                  <b>{{ auth()->user()->company->name }}</b> <br>
                  <b>{{ auth()->user()->company->code }}</b> <br>
                  <small>
                    @if (auth()->user()->company->phone)
                      Teléfono: {{ auth()->user()->company->phone }} <br>
                    @endif
                    @if (auth()->user()->company->email)
                      Email: {{ auth()->user()->company->email }} <br>
                    @endif
                    {!! auth()->user()->company->location !!} <br>
                  </small>
                </div>
                <div class="col-2">
                  <h4>
                    <small class="float-right pt-3">
                        <div class="callout callout-info">
                          <p style="font-size: 12px;">
                            Fecha: {{ formatoFechaDMASimple(@$order->fecha) }} <br />                            
                          </p>
                          <h5>Nro: {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h5> 
                        </div>
                    </small>
                  </h4>
                </div>
                <!-- /.col -->
              </div>
              <!-- info row -->
              <div class="info-box bg-light">
                <div class="info-box-content">
                  <div class="row invoice-info">
                    
                    <div class="col-sm-5 invoice-col">
                      Cliente:
                      <address>
                        @if ($client)
                          <strong>{{ $client->NOMBRE }}</strong><br>
                          <strong>RIF: {{ $client->RIF }}</strong><br>
                          Teléfono: {{ $client->TELEFONO }}<br>
                          @if (@$client->EMAIL)
                          Email: {{ $client->EMAIL }}<br>
                          @endif
                          <small>
                            {{ $client->DIRECCION }}<br>                            
                          </small>                          
                        @else
                          <strong>{{ $order->descripcion }}</strong><br>
                          <strong>RIF: {{ $order->rif }}</strong><br>
                          Teléfono: {{ $order->telefono }}<br>
                          @if (@$order->email)
                          Email: {{ $order->email }}<br>
                          @endif
                        @endif
                      </address>
                    </div>

                    <div class="col-sm-5 invoice-col">
                      Vendedor:
                      <address>
                        @if (@$user)
                          <strong>{{ @$user->name }} {{ @$user->last_name }}</strong><br>
                          <b>Teléfono: </b>{{ @$vendedor->telefono }}<br>
                          <b>Email: </b>{{ @$vendedor->email }}
                        @else
                          <strong>{{ @$order->seller_code }}</strong><br>                          
                        @endif
                        @if (@$order->deposito)
                          <br>
                          <b>Depósito: </b>{{ @$order->deposito->DDEPOS }}
                        @endif
                      </address>
                    </div>

                    <div class="col-sm-2 invoice-col">   
                      @php ( $style = getOrderStatusColor($order->estatus) )
                      <span class="badge badge-{{$style}}">
                        {{ $order->estatus }}
                      </span>
                        @if(isset($order->factura))
                        @if(strtoupper($order->factura) === 'SI')
                          <div class="alert alert-success d-flex align-items-center mt-2" role="alert" style="font-size: 14px; print-color-adjust: exact;">
                          <i class="fas fa-file-invoice-dollar fa-lg mr-2 d-print-none"></i>
                          <strong>Lleva Factura</strong>
                          </div>
                        @else
                          <div class="alert alert-warning d-flex align-items-center mt-2" role="alert" style="font-size: 14px; print-color-adjust: exact;">
                          <i class="fas fa-file-invoice fa-lg mr-2 d-print-none"></i>
                          <strong>No Lleva Factura</strong>
                          </div>
                        @endif
                        @endif
                    </div>
                    
                  </div>
                </div>
              </div>
              <!-- /.row -->

              <!-- Table row -->
              <div class="row">
                <div class="col-12 table-responsive">
                  <table class="table table-striped">
                    <thead>
                    <tr>
                      <th>Cant.</th>
                      <th>Código</th>
                      <th>Descripción</th>
                      <th>Ref</th>
                      <th>Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                      @php($total = 0)
                      @foreach (@$order->pedido_detalle as $item)
                        <tr>
                          <td>{{ $item->cantidad }}</td>
                          <td>{{ $item->codigo_inven }}</td>
                          <td>{{ $item->inven_descr }}</td>
                          <td>{{ number_format($item->precio_dolar, 2) }}</td> 
                          <td>{{ number_format($item->precio_dolar * $item->cantidad, 2) }}</td>
                          @php($total += $item->precio_dolar * $item->cantidad)
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <div class="row">
                <!-- accepted payments column -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 table-responsive">
                  <p class="lead d-none">Payment Method:</p>
                  <table class="table table-bordered d-none">
                    <thead>
                      <tr>
                          <th>YYY</th>
                        
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                          <td>
                            XX
                          </td>
                      </tr>
                    </tbody>
                  </table>

                  <p class="text-muted well well-sm shadow-none" style=" margin-top: 10px; font-size: 12px;">
                    @if ($order->conditions)
                      <b>
                        Condiciones:
                      </b>
                      {{$order->conditions}}
                    @endif

                    @if ($order->observations)
                      <br>
                      <b>
                        Observaciones:
                      </b>
                      {{$order->observations}}
                    @endif
                  </p>
                </div>
                <!-- /.col -->
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                  <p class="lead">&nbsp;</p>

                  <div class="table-responsive">
                    <table class="table">
                      <tr>
                        <th class="pull-right" style="width:50%">TOTAL:</th>
                        <td><b>Ref {{number_format($total, 2)}}</b></td>
                      </tr>
                      
                    </table>
                  </div>
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->

              <hr />
              <div class="row pt-5 d-none">
                <div class="col-xs-6 col-sm-6 col-5">
                  <p class="lead pull-right mb-0">Nombre y Firma:</p>
                </div>
                <div class="col-3 text-center" style="border-bottom: solid 2px;">
                  sadsd
                </div>
              </div>


              
            </div>
            <!-- /.invoice -->
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div>

    @if (isset($print))
        <script type="text/javascript"> 
          window.addEventListener("load", window.print());
        </script>
    @endif

@endsection
