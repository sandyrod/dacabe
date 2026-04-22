@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Balance')

@section('titulo_header', 'Balance')
@section('subtitulo_header', 'Balance')

@section('content')

  @php($user = (new \App\User)->find(@$order->user_id))
  @php($vendedor = @$user ? (new \App\Models\Vendedor)->where('email', @$user->email)->first() : null)
  
  <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            <!-- Main content -->
            <div class="invoice p-3 mb-3">
              <!-- title row -->
              <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                  <h4>
                    <img class="responsive" width="400" src="{{ asset('storage/logos/'.$report_data['company']->logo) }}" alt="">
                  </h4>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 invoice-col text-center">
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
                <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
                  <h4>
                    <small class="float-right pt-3">
                        <div class="callout callout-info">
                          <p style="font-size: 12px;">
                            Fecha: {{ formatoFechaDMASimple(@$order->fecha) }} <br />                            
                          </p>                          
                        </div>
                    </small>
                  </h4>
                </div>
                <!-- /.col -->
              </div>
              <!-- info row -->
              <div class="info-box bg-light pt-5">
                <div class="info-box-content">
                  <div class="row invoice-info">                    
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 invoice-col text-center">
                      <h4>Vendedor: {{@$balance->user->name}} {{@$balance->user->last_name}}</h4>
                    </div>
                  </div>

                  <div class="row invoice-info text-center">
                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 invoice-col">
                      <h5>
                        Total Vendido: $ {{ number_format(@$balance->ventas, 2, ',', '.') }}
                      </h5>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4  invoice-col">
                      <h5>
                        Total Pagado: $ {{ number_format(@$balance->payments, 2, ',', '.') }}
                      </h5>                      
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4  invoice-col">   
                      <h5>
                        Saldo $ {{ number_format(@$balance->saldo, 2, ',', '.') }}                        
                      </h5>
                    </div>
                    
                  </div>
                </div>
              </div>
              <!-- /.row -->

              
              
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
