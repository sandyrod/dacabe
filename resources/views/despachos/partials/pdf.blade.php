@extends('layouts.app_pdf')

@section('titulo', config('app.name', 'Laravel')  . ' - Pedidos')


@section('content')

  <div class="container-fluid" style="background: #FFFFFF;">
        <div class="row" style="margin-top: 40px;">
          <div class="col-12">
            
            <!-- Main content -->
            <div class="">
              <!-- title row -->
              <div class="row">
                <div class="col-12 table-responsive">
                  <table class="table ">
                    <thead>
                    <tr>
                      <th>
                        {{--
                        <img class="responsive" width="65" src="{{storage_path('app/public/logo_redondo.png')}}" alt="">
                        <img class="responsive" width="100" src="{{ asset('imgs/logos/'.$report_data['company']->logo) }}" alt="">                      
                        --}}
                        <img class="responsive" width="170" src="{{storage_path('app/public/logos/'.auth()->user()->company->logo)}}" alt="">
                      </th>
                      <th style="font-size: 11px; padding-left: 20px;">
                        <p style="font-size: 11px;">
                          <strong>
                            {{auth()->user()->company->name}}
                          </strong>
                          <br>
                            {!! auth()->user()->company->location !!}
                        </p>
                      </th>
                      <th>
                        <p style="font-size: 11px; text-align: left; padding-left: 50px;">
                            Fecha: {{ formatoFechaDMASimple($order->fecha) }} <br />                            
                          Nro: {{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                          </p>
                      </th>                      
                    </tr>
                    </thead>
                    
                  </table>
                </div>
              </div>
              <hr>
              <div class="row" style="font-size: 12px;">
                <div class="col-12">
                  <table class="table">
                    <thead>
                    <tr>
                      <th style="text-align: left;">
                        Cliente:
                        @if (@$client)
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
                            
                          
                      </th>   
                      <th style="font-size: 12px; text-align: left;">
                        Vendedor:
                        <address>
                          @if (@$user)
                            <strong>{{ @$user->name }} {{ @$user->last_name }}</strong><br>
                            <b>Teléfono: </b>{{ @$vendedor->telefono }}<br>
                            <b>Email: </b>{{ @$vendedor->email }}
                          @else
                            <strong>{{ @$order->seller_code }}</strong><br>                          
                          @endif
                          
                        </address>
                      </th>                      
                    </tr>
                    </thead>
                    
                  </table>
                </div>
              </div>

              <hr>
              <!-- info row -->
              

              <!-- Table row -->
              <div class="row" style="font-size: 12px; padding-top: 20px;">
                <div class="col-12 ">
                  <table class="table">
                    <thead>
                    <tr>
                      <th style="font-size: 12px; text-align: left;">Cant.</th>
                      <th style="font-size: 12px; text-align: left;">Código</th>
                      <th style="font-size: 12px; text-align: left;">Descripción</th>
                      <th style="font-size: 12px; text-align: left;">Ref</th>
                      <th style="font-size: 12px; text-align: left;">Subtotal</th>
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
              <hr>
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

                  @if(@$order->descuento)
                    <p class="text-muted well well-sm shadow-none text-center" style="margin-top: 10px; font-size: 12px;">
                      Descuento por Pago en Divisa: <b>{{@$order->descuento}}%</b><br>
                      <small>
                        15 dias de crédito a la tasa BCV correspondiente a la fecha del presente documento. <br>
                        30 dias de crédito para pago en divisa ({{@$order->descuento}}%)
                      </small>
                    </p>
                  @endif
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
              


          </div><!-- /.col -->
        </div><!-- /.row -->
      </div>


@endsection
