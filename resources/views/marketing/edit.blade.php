@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - marketing')

@section('titulo_header', 'Modificar marketing')
@section('subtitulo_header', 'marketing')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

		<input type="hidden" name="marketing_id" id="marketing_id" value="{{$marketing->id}}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('marketing.form')
			</div>
	
			{{--
			@include('marketing.footer') 
			--}}
			<div class="card-footer">
				<div class="form-group">
					<div class="row">
						<div class="col-md-4 col-sm-12 col-xs-12">
						</div>
						<div class="mt-2 col-md-4 col-sm-6 col-xs-6">
							<a href="{{route($route)}}" class="btn btn-outline-danger btn-block">
								<i class="fas fa-chevron-left"></i>  Regresar
							</a>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>

@endsection


@section('scripts')
	<script>
		const TOKEN = '{{csrf_token()}}';
		let URL_DELETE_DETAIL = '{{ url('delete-marketing-detail') }}';
		let URL_DETAIL = '{{ url('update-marketing-detail') }}';

		let removeItem = (id) => {
			$('#detalle').html('<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
			$.ajax({
                url : URL_DELETE_DETAIL, type : 'post', data: { _token : TOKEN, id: id  }, dataType : 'json',
                success : function(response) {
                    console.log(response);
	    			if (response) {
			    		let html = '';
			    		response.data.marketing_detalle.forEach((item) => {
			    			html += '<tr><td>' + item.nombre + '</td><td>' + item.email + '</td>';
			    			html += '<td><a href="" onclick="event.preventDefault(); removeItem('+item.id+')"><i class="fa fa-trash"></i></a>' + '</td>';
			    			html += '</tr>';
			    		});
			    		$('#detalle').html(html);
			    		swal({title: response.title, text: response.text, type: response.type, html:true});	
			    	}
                },
                error : function(xhr, status) {
                    console.log('error: ', xhr, status);
                    $('#detalle').html('<tr><td colspan="3">No hay información para mostrar...</td></tr>');

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

		$(document).ready(function() {

			let clients = @json($clients)
			
		    $('#rif').val('').trigger('change');

			$('.select2').select2({
		        placeholder: "Seleccione",
		        allowClear: true
		    });
		    

			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});

			$('#rif').on('change', function() {
			    let selected_id = $('#rif').val();
			 	$('#razon_social').val('');
			 	$('#email').val('');
				let client = clients.filter((item) => item.RIF == selected_id);
				if (client && client.length) {
				 	$('#razon_social').val(client[0].NOMBRE);
				 	$('#email').val(client[0].EMAIL);					
				}
			});
			
			$('#btn_save').on('click', function() {
				let razon_social = $('#razon_social').val();
				let email = $('#email').val();
			    if (razon_social && email) {
			    	let data = {
			    		'nombre': razon_social,
			    		'email': email
			    	};
			    	let id = $('#marketing_id').val();
					sendRequest (URL_DETAIL, 'post', id, data);
			    }
			});
			
			$('#btn_all_clients').on('click', function() {
				let data = {
			    	'nombre': 'TODOS'
			    };
		    	let id = $('#marketing_id').val();
				sendRequest (URL_DETAIL, 'post', id, data);			    
			});
			

			let sendRequest = (url, type, id, data = null) => {
				$('#detalle').html('<div class="spinner-border" role="status"><span class="sr-only">Cargando...</span></div>');
				$.ajax({
	                url : url, type : type, data: { _token : TOKEN, id: id, data: data  }, dataType : 'json',
	                success : function(response) {
	                    console.log(response);
		    			if (response) {
				    		//refreshData(response);
				    		let html = '';
				    		response.data.marketing_detalle.forEach((item) => {
				    			html += '<tr><td>' + item.nombre + '</td><td>' + item.email + '</td>';
				    			html += '<td><a href="" class="btn btn-outline-danger" onclick="event.preventDefault(); removeItem('+item.id+')"><i class="fa fa-trash"></i></a>' + '</td>';
				    			html += '</tr>';
				    		});
				    		$('#detalle').html(html);
				    		$('#razon_social').val('');
				    		$('#email').val('');
				    		swal({title: response.title, text: response.text, type: response.type, html:true});	
				    	}
	                },
	                error : function(xhr, status) {
	                    console.log('error: ', xhr, status);
	                    $('#detalle').html('<tr><td colspan="3">No hay información para mostrar...</td></tr>');

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


		}); 
	</script>
	
@endsection
