@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Estado de Cuenta')

@section('titulo_header', 'Estadísticas')
@section('subtitulo_header', 'Gastos')

@section('styles')
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('theme/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{!! asset('theme/plugins/datetimepicker/css/jquery-datetimepicker.min.css') !!}">
@endsection

@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

 		<div class="row">
          
          <!--<div class="col-md-9">-->
          	
           	<div class="col-md-12">
	        	<div class="row">          	
	           		<div class="col-md-12">
	           			<div class="card card-info">
			              <div class="card-header">
			                <h3 class="card-title">Gráfica por Tipo de Ingreso</h3>

			                <div class="card-tools">
			                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
			                  </button>
			                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
			                </div>
			              </div>
			              <div class="card-body">
			              	
			              	<div class="row">
			              		<div class="col-4">
			              			<div class="input-group">
	                            		{!! Form::date('start_at', old('start_at', ''), ['class' => 'form-control  birthday', 'placeholder' => 'Desde', 'id' => 'start_at' ]) !!}
	                            	</div>
			              		</div>
			              		<div class="col-4">
			              			<div class="input-group">
			                            {!! Form::date('end_at', old('end_at', ''), ['class' => 'form-control  birthday', 'placeholder' => 'Hasta', 'id' => 'end_at' ]) !!}
			                            
			                        </div>
	                      		</div>
			              		<div class="col-4">
			              			<input type="button" value="Filtrar" class="btn btn-primary btn-block" onclick="loadGraphics()">
			              		</div>
			              	</div>
			              	
			              	
			              </div>
			            </div> 
	           		</div>
	           	</div>
	           </div>

           <div class="col-md-12">
          	<div class="row">          	
          		<div class="col-6">
          			<div class="card card-primary">
		              <div class="card-header">
		                <h3 class="card-title">Gastos Registrados</h3>

		                <div class="card-tools">
		                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
		                  </button>
		                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
		                </div>
		              </div>
		              <div class="card-body">
		                <table class="table table-striped table-valign-middle">
		                	<thead>
		                		<tr>
		                			<th>Tipo</th>
		                			<th>Gasto</th>
		                			<th>Monto Bs.</th>
		                			<th>Divisa</th>
		                		</tr>
		                	</thead>
		                	<tbody id="general_balance">

		                	</tbody>
		                </table>

		              </div>
		            </div>          			
          		</div>
          		<div class="col-6">
          			<div class="card card-info">
		              <div class="card-header">
		                <h3 class="card-title">Gráfica por Tipo de Ingreso</h3>

		                <div class="card-tools">
		                  <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
		                  </button>
		                  <button type="button" class="btn btn-tool" data-card-widget="remove"><i class="fas fa-times"></i></button>
		                </div>
		              </div>
		              <div class="card-body">
		              	
		              	<div class="row">
		              		<div class="col-12">
		                		<canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
		              		</div>
		                </div>
		              </div>
		            </div>          			
          		</div>

          	</div>
          	
          </div>
          <!-- /.col -->
        </div>
	
	</div>

@endsection


@section('scripts')
	<script src="{{ asset('theme/plugins/inputmask/min/jquery.inputmask.bundle.min.js') }}"></script>
  	<script src="{!! asset('theme/plugins/datetimepicker/js/jquery-datetimepicker.full.min.js')!!}"></script>
  	<script src="{!! asset('theme/plugins/moment/moment.min.js')!!}"></script>
  	<script src="{!! asset('theme/plugins/moment/locale/es.js')!!}"></script>
m
        <script src="{{ asset('theme/plugins/daterangepicker/daterangepicker.js') }}"></script>

  	<!-- jQuery Knob Chart -->
    <script src="{{ asset('theme/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('theme/plugins/chart.js/Chart.min.js') }}"></script>

	<script>
		
			const URL = 'get-expense-statistics';
			const TOKEN = '{{csrf_token()}}';

			
	    let init_calendars = () => {
	    	/*
	        $('#start_at').datetimepicker({
	            format: 'd/m/Y',
	            closeOnDateSelect: true,
	            timepicker: false,
	        });
	        $("#start_at_btn").on('click', function( ) {
	            $('body').find('#start_at').trigger('focus');
	        });

	        $('#end_at').datetimepicker({
	            format: 'd/m/Y',
	            closeOnDateSelect: true,
	            timepicker: false,
	        });
	        $("#end_at_btn").on('click', function( ) {
	            $('body').find('#end_at').trigger('focus');
	        });
	        */
	    };

    	init_calendars();

		const refreshTable = (data) => {
			let html = '';
			data.forEach((item) => {
				let style = 'danger';
				let transaction_type = item.name;
				html += '<tr><td><span class="badge badge-'+style+'">'+transaction_type+'</span></td>';
				html += '<td class="text-'+style+'">'+item.name+'</td>';
				//let bs = item.dollar_amount * data.rate;
				let bs = item.amount;
				html += '<td class="text-'+style+'"><b">' + bs.toLocaleString('ES-es') + '</b></td>';
				html += '<td class="text-'+style+'"><b">$' + item.dollar_amount.toLocaleString('ES-es') + '</b></td>';
				html += '</tr>';
			});
			$('#general_balance').html(html);
		};

			const graphics = (data) => {
				let payments = data;
				let payment_colors = [];
				var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
				let payments_unique = payments//[...new Set(payments.map((item) => item.payment_type_id))];
				//let months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
				let colors = ['rgba(60,141,188,0.9)', '#7DD139', '#FA5D5D', '#EC8A20', '#17D8E9', '#E027E2', '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'];

		    	console.log('data')
				console.log(data)
				//-------------
		    //- BAR CHART -
		    //-------------

				let datos = [];
				let labels = [];
				let data_donut = [];
				let donough_colors = [];
				let i = 0;
				//let payments_unique = [...new Set(payments.map((item) => item.payment_type_id))];
				let p = 0;
				data.forEach((item) => {
					/*
					let record = payments.filter((rec) => rec.payment_type_id === payment_type_id);
					item = record[0];
					payment_colors[item.payment_type_id] = colors[i];
					i = i >= 5 ? 0 : i + 1;
					
					let values = payments.filter((pay) => pay.payment_type_id === item.payment_type_id);
					let receiveds = [0,0,0,0,0,0,0,0,0,0,0,0];
					let acum = 0;
					values.forEach((pay) => {
						receiveds[pay.month-1] = receiveds[pay.month-1] + pay.dollar_amount;
						acum += pay.dollar_amount;
					});
					*/

					let item_color = colors[p];
					labels.push(item.name);
					data_donut.push(item.amount);
					donough_colors.push(item_color);
					
					let obj = {
				        label               : item.name,
				        backgroundColor     : item_color,
				        borderColor         : 'rgba(60,141,188,0.8)',
				        pointRadius          : false,
				        pointColor          : '#3b8bba',
				        pointStrokeColor    : 'rgba(60,141,188,1)',
				        pointHighlightFill  : '#fff',
				        pointHighlightStroke: 'rgba(60,141,188,1)',
				        data                : item
				      };
					datos.push(obj);
					p++;
				});

			var donutData        = {
			      labels: labels,
			      datasets: [
			        {
			          data: data_donut,
			          backgroundColor : donough_colors,
			        }
			      ]
			    }
			    var donutOptions     = {
			      maintainAspectRatio : false,
			      responsive : true,
			    }
			    //Create pie or douhnut chart
			    // You can switch between pie and douhnut using the method below.
			    var donutChart = new Chart(donutChartCanvas, {
			      type: 'doughnut',
			      data: donutData,
			      options: donutOptions      
			    })
			
			    /*
		    var areaChartData = {
		      labels  : months,
		      datasets: datos
		    }
		    */
	    
	  };
			
		

		let loadGraphics = () => {
			let start_at = $('#start_at').val();
			let end_at = $('#end_at').val();
			$.ajax({
		        url : URL, type : 'post', data: { _token : TOKEN, start_at: start_at, end_at: end_at  }, dataType : 'json',
		        success : function(data) {
		        	data = data.data ? data.data : null;
		        	refreshTable(data);
		          	graphics(data);	
		        },
		        error : function(xhr, status) {
		            console.log('error: ', xhr, status);
		            switch(xhr.status) {
		                case 401:
		                    swal("Oops!", "Su sesión ha expirado.", "info");
		                    setTimeout(function(){   location.reload();   }, 2000);
		                    break;
		                case 404:
		                    swal("Oops!", "Recurso no encontrado.", "error");
		                    break;
		                default:
		                    swal("Oops!", "¡El registro no pudo ser procesado!... Intente nuevamente.", "error");
		            }
		        },
		        complete:function(response){}
	    	});
		};

		loadGraphics();

			


	</script>
	
@endsection
