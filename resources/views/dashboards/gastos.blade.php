@include('dashboards.modulos')

@php ($data = (new App\Models\Expense)->orderBy('date_at', 'DESC')->get()->take(20))

  <div class="row d-none">
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
          
              <div class="card-header border-transparent">
                <h3 class="card-title text-primary"><i class="fa fa-arrow-circle-right"></i> <b> Últimos Gastos registrados </b></h3>

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
                  <table class="table m-0">
                    <thead>
                    <tr>
                      <th>Fecha</th>
                      <th>Grupo</th>
                      <th>Descripción</th>
                      <th>Bs.</th>
                      <th>Divisa</th>
                    </tr>
                    </thead>
                    <tbody>
                      @forelse(@$data as $item)
                        <tr>
                          <td><a href="#">{{formatoFechaDMASimple($item->date_at)}}</a></td>
                          <td>{{@$item->expense_group->name}}</td>
                          <td>{{$item->name}}</td>
                          <td><span class="badge badge-primary">Bs. {{ number_format((float)$item->amount, 2, ',', '.') }}</span></td>                      
                          <td><span class="badge badge-success">$ {{ number_format((float)$item->dollar_amount, 2, ',', '.') }}</span></td> 
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5">No hay información para mostrar...</td>                          
                        </tr>

                      @endforelse                    
                    </tbody>
                  </table>
                </div>
                <!-- /.table-responsive -->
              </div>
              <!-- /.card-body -->
              <div class="card-footer clearfix">
                <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-info float-left">Agregar Gasto</a>
                <a href="{{ url('expenses') }}" class="btn btn-sm btn-secondary float-right">Ver Todos</a>
              </div>
              <!-- /.card-footer -->
            
        
      </div>
      <!--
      <div class="row mb-3">
        <div class="col-md-6 offset-md-3">
          <a href="#" class="btn btn-lg btn-outline-primary btn-block">Ver Todas las facturas</a>
        </div>
      </div>
  -->
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
          <img class="img-circle elevation-2" src="{{ myCompanyLogo() }}" alt="Logo">
        </div>
        <div class="card-footer p-0">
          @if($statuses[$company->company_status_id] == 'ACTIVO')
            <ul class="nav flex-column permission-list">
              <li class="nav-item">
                <a href="{{ url('expenses') }}" class="nav-link">
                  <i class="fas fa-dollar-sign"></i> Gastos
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('expense-groups') }}" class="nav-link">
                  <i class="fas fa-layer-group"></i> Grupos de Gastos 
                </a>
              </li> 
              <li class="nav-item">
                <a href="{{ url('expense-statistics') }}" class="nav-link">
                  <i class="fas fa-chart-pie"></i> Estadísticas 
                </a>
              </li> 
              @ability('admin,owner', 'branches')
                <li class="nav-item">
                  <a href="{{ url('branches') }}" class="nav-link">
                    <i class="fas fa-building"></i> Sucursales
                  </a>
                </li> 
              @endability
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

      <div class="card d-none">
              <div class="card-header">
                <h3 class="card-title">Estadísticas</h3>

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
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <div class="chart-responsive"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
                      <canvas id="pieChart" height="107" style="display: block; width: 215px; height: 107px;" width="215" class="chartjs-render-monitor"></canvas>
                    </div>
                    <!-- ./chart-responsive -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-4">
                    <ul class="chart-legend clearfix">
                      <li><i class="far fa-circle text-danger"></i> SERVICIOS</li>
                      <li><i class="far fa-circle text-success"></i> IMPUESTOS</li>
                      <li><i class="far fa-circle text-warning"></i> NÓMINA</li>
                      <li><i class="far fa-circle text-info"></i> MAT. OFICINA</li>
                      <li><i class="far fa-circle text-primary"></i> ALQUILER</li>
                      <li><i class="far fa-circle text-secondary"></i> VEHICULOS</li>
                    </ul>
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
              <!-- /.card-body -->
              <div class="card-footer p-0">
                <ul class="nav nav-pills flex-column">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      SERVICIOS
                      <span class="float-right text-danger">
                        <i class="fas fa-arrow-down text-sm"></i>
                        12%</span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      IMPUESTOS
                      <span class="float-right text-success">
                        <i class="fas fa-arrow-up text-sm"></i> 4%
                      </span>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      NÓMINA
                      <span class="float-right text-warning">
                        <i class="fas fa-arrow-left text-sm"></i> 0%
                      </span>
                    </a>
                  </li>
                </ul>
              </div>
              <!-- /.footer -->
            </div>

                 
      </div>
    </div>

  
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