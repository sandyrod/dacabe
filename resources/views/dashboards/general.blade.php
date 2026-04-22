@if (Auth::user()->company_id == 1)
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 class="invoices-companies-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p class="invoices-companies-text">Empresas Registradas</p>
        </div>
        <div class="icon">
          <i class="fa fa-building"></i>
        </div>
        <a href="{{url('companies')}}" class="small-box-footer update-invoices-indicator">Ir a Empresas <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3 class="invoices-users-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i><sup style="font-size: 20px"></sup></h3>

          <p>Usuarios Registrados</p>
        </div>
        <div class="icon">
          <i class="fa fa-users"></i>
        </div>
        <a href="{{url('users')}}" class="small-box-footer">Ver Usuarios <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-danger">
        <div class="inner">
          <h3 class="serials-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p>Seriales SD registrados</p>
        </div>
        <div class="icon">
          <i class="fa fa-desktop"></i>
        </div>
        <a href="{{url('serials')}}" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <h3 class="payments-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p>Pagos reportados</p>
        </div>
        <div class="icon">
          <i class="fas fa-handshake"></i>
        </div>
        <a href="#" class="small-box-footer">Reportar Pago <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>
  
    <div class="row">
      <div class="col-md-8 col-sm-12 col-xs-12">
        <div class="card card-primary card-outline">
        <div class="card-header border-0">
          <h5 class="text-primary">Estadísticas FARMAX</h5>              
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-valign-middle">
            <thead>
            <tr>
              <th>Farmacia</th>
              <th>Fact. descargadas</th>
              
            </tr>
            </thead>
            <tbody id="farmax_details">
                
            </tbody>
          </table>

          
        </div>
      </div>
      <!--
      <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
          <a href="#" class="btn btn-lg btn-outline-primary btn-block">Ver Todas las facturas</a>
        </div>
      </div>
  -->
    </div>

    <div class="col-md-4">
      <!-- Widget: user widget style 1 -->
      <div class="card card-widget widget-user">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header company-status-color bg-gray">
          <h3 class="widget-user-username">{{ Auth::user()->company->name }}</h3>
          <h5 class="widget-user-desc company-status-name">CLOUD</h5>
        </div>
        <div class="widget-user-image">
          <img class="img-circle elevation-2" src="{{ mainCompanyLogo() }}" alt="Logo">
        </div>
        <div class="card-footer p-0">
          <ul class="nav flex-column permission-list">
            <li class="nav-item">
              <a href="{{url('modules')}}" class="nav-link">
                Módulos Cloud
                <span class="float-right badge bg-success modules-counter"></span>
              </a>
            </li>
            
          </ul>
        </div>
      </div>
      
      
      <input type="hidden" id="invoice_id" />

      <!-- PRODUCT LIST -->
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title pending-title">FARMAX</h3>

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
          <br>
          <span class="mt-2">
            
            <a href="{{url('ftp')}}" class="btn btn-app">
              <span class="badge bg-info pharmacy-counter"></span>
              <i class="fa fa-cogs"></i> Config. FTP
            </a>

            <a href="{{url('drugstores')}}" class="btn btn-app">
              <span class="badge bg-info drugstore-counter"></span>
              <i class="fa fa-building"></i> Droguerias
            </a>

            <!--
            <a href="#" class="btn btn-app">
              <span class="badge bg-info "></span>
              <i class="fa fa-download"></i> Descargar
            </a>

            <a href="#" class="btn btn-app">
              <span class="badge bg-info "></span>
              <i class="fa fa-play"></i> Procesar
            </a>
          -->
            
          </span>
          
        </div>
        <!-- /.card-body -->
        <div class="card-footer text-center">
          <a href="#" class="uppercase quit-filter"></a>
        </div>
        <!-- /.card-footer -->
      </div>              
      </div>
    </div>
@else
  <div class="row">
    <div class="col-lg-3 col-6">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 class="invoices-month-counter"><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p class="invoices-month-text">Sin Conexión</p>
        </div>
        <div class="icon">
          <i class="fa fa-download"></i>
        </div>
        <a href="#" class="small-box-footer update-invoices-indicator">Descargar <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-lg-3 col-6">
      <div class="small-box bg-success">
        <div class="inner">
          <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i><sup style="font-size: 20px"></sup></h3>

          <p>Facturas transferidas</p>
        </div>
        <div class="icon">
          <i class="fa fa-award"></i>
        </div>
        <a href="#" class="small-box-footer">Más detalles <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-danger">
        <div class="inner">
          <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p>Productos por enlazar</p>
        </div>
        <div class="icon">
          <i class="fa fa-barcode"></i>
        </div>
        <a href="#" class="small-box-footer">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <h3><i class="fa fa-circle-o-notch fa-spinner fa-fw"></i></h3>

          <p>Pagos realizados</p>
        </div>
        <div class="icon">
          <i class="fas fa-handshake"></i>
        </div>
        <a href="#" class="small-box-footer">Reportar Pago <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>
  
    <div class="row">
      <div class="col-md-8 col-sm-12 col-xs-12">
        <div class="card card-primary card-outline">
        <div class="card-header border-0">
              <a class="btn btn-app text-success btn-save-order">
                <i class="fas fa-save"></i> Guardar
              </a>
              <a class="btn btn-app btn-trash text-danger">   
                <i class="fas fa-times"></i> Cancelar
              </a>
              <a class="btn btn-app text-primary btn-get-order">
                @if (@$count_saveds)
                  <span class="badge bg-danger count_saveds">{{$count_saveds}}</span>
                @endif
                <i class="fas fa-download"></i> Recuperar
              </a>
              <a class="btn btn-app text-primary products">   
                <i class="fas fa-barcode"></i> Productos
              </a> 
              <!--  
              <a class="btn btn-app text-primary">   
                <i class="fas fa-edit"></i> Pedido
              </a>                     
              <a class="btn btn-app text-danger" href="{{ url('inicio') }}">
                <i class="fas fa-sign-out-alt"></i> Salir
              </a>  
              -->
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-striped table-valign-middle">
            <thead>
            <tr>
              <th>Producto</th>
              <th>Cant.</th>
              <th>Subtotal</th>
              <th>Acción</th>
            </tr>
            </thead>
            <tbody id="details">
                <tr><td class="text-center" colspan="4"><a class="btn btn-primary text-white" onclick="showProductModal();"> <i class="fas fa-barcode"></i> Agregar Producto</a></td></tr>
            </tbody>
          </table>

          <div class="row div_total">
            <div class="col-12">
              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <tr class="bg-ligthgray">
                      <th style="width:100%" class="pull-right">Subtotal</th>
                      <td id="field_subtotal">0</td>
                    </tr>
                    <tr class="bg-ligthgray">
                      <th style="width:100%" class="pull-right">IVA</th>
                      <td id="field_iva">0</td>
                    </tr>
                    <tr class="bg-ligthgray text-primary">
                      <th style="width:100%" class="pull-right"><h2>TOTAL</h2></th>
                      <td><h2 id="field_total">0</h2></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--
      <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
          <a href="#" class="btn btn-lg btn-outline-primary btn-block">Ver Todas las facturas</a>
        </div>
      </div>
  -->
    </div>

    <div class="col-md-4">
      <!-- Widget: user widget style 1 -->
      <div class="card card-widget widget-user">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header company-status-color bg-warning">
          <h3 class="widget-user-username">{{ Auth::user()->company->name }}</h3>
          <h5 class="widget-user-desc company-status-name"><i class="fas fa-spinner"></i></h5>
        </div>
        <div class="widget-user-image">
          <img class="img-circle elevation-2" src="{{ mainCompanyLogo() }}" alt="Logo">
        </div>
        <div class="card-footer p-0">
          <ul class="nav flex-column permission-list">
            <li class="nav-item">
              <a href="#" class="nav-link">
                <i class="fas fa-spinner"></i>
              </a>
            </li>                  
          </ul>
        </div>
      </div>
      <!-- /.widget-user -->

      <input type="hidden" id="invoice_id" />

      <!-- PRODUCT LIST -->
      <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title pending-title">Productos No Vinculados</h3>

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
          <ul class="products-list product-list product-list-in-card pl-2 pr-2">
            <li class="item">
              <div>
                <a href="javascript:void(0)" class="product-title"><i class="fa fa-tags"></i> CODIGO BARRA
                  <span class="btn btn-primary float-right"><i class="fa fa-edit"></i></span></a>
                <span class="product-description">
                  PRODUCTO
                </span>
              </div>
            </li>
            
          </ul>
        </div>
        <!-- /.card-body -->
        <div class="card-footer text-center">
          <a href="#" class="uppercase quit-filter"></a>
        </div>
        <!-- /.card-footer -->
      </div>              
      </div>
    </div>
@endif
  
   @ability('admin,owner', 'permission')
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        
      </div>
    </div>
    @endability
   

  @section('scripts')
    <script>
    
      let getCompanyIndicators = () => {
        $('.invoices-companies-counter').text('0');
        $('.invoices-users-counter').text('0');
        $('.serials-counter').text('0');
        $('.payments-counter').text('0');
        $('.pharmacy-counter').text('0');
        $('.drugstore-counter').text('0');
        $('.modules-counter').text('0');
        $.ajax({
            url : '{{url('get-company-indicators')}}', type : 'get', dataType : 'json',
            success : function(response) {
              if (response.companies) {
                $('.invoices-companies-counter').text(response.companies);
              }
              if (response.users) {
                $('.invoices-users-counter').text(response.users);
              }
              if (response.serials) {
                $('.serials-counter').text(response.serials);
              }
              if (response.payments) {
                $('.payments-counter').text(response.payments);
              }
              if (response.pharmacies) {
                $('.pharmacy-counter').text(response.pharmacies);
              }
              if (response.drugstores) {
                $('.drugstore-counter').text(response.drugstores);
              }
              if (response.modules) {
                $('.modules-counter').text(response.modules);
              }
            },
            error : function(xhr, status) {
                console.log('error: ', xhr, status);                  
            }
        });
      };

      getCompanyIndicators();

    let getFarmaxDetails = () => {
        $('.invoices-companies-counter').text('0');
        let html='';
        $.ajax({
            url : '{{url('get-farmax-details')}}', type : 'get', dataType : 'json',
            success : function(response) {
              console.log('farmax')
              console.log(response)
              response.ftp_downloads.forEach((item) => {
                html += '<tr><td>'+item.name+'</td><td>'+item.n+'</td></tr>';
              });
              $('#farmax_details').html(html);
            },
            error : function(xhr, status) {
                console.log('error: ', xhr, status);                  
            }
        });
      };

      getFarmaxDetails();

    </script>
  @endsection