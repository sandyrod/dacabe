@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - marketing')

@section('titulo_header', strtoupper(substr($marketing->tipo, 9, 1)) . substr($marketing->tipo, 10))
@section('subtitulo_header', 'marketing')


@section('content')
	<div class="container-fluid">
 		
 		@include('layouts.partials.errors')

		<input type="hidden" name="marketing_id" id="marketing_id" value="{{$marketing->id}}">
		
						<div class="card card-primary">
				            <div class="card-header border-transparent">
				                <h3 class="card-title"><i class="fa fa-shopping-cart"></i> {{ @$marketing->descripcion }} <small class="ml-3"><i class="fa fa-tags"></i> {{@$marketing->codigo}}</small></h3>

				                <div class="card-tools">
				                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
				                    <i class="fas fa-minus"></i>
				                  </button>
				                  <button type="button" class="btn btn-tool" data-card-widget="remove">
				                    <i class="fas fa-times"></i>
				                  </button>
				                </div>
				            </div>
			              <div class="card-body p-0">
			                <div class="table-responsive">
			                  <table class="table m-0">
			                    <thead>
			                    <tr>
			                      <th>Razón Social</th>
			                      <th>Email</th>
			                      <th>Status</th>
			                    </tr>
			                    </thead>
			                    <tbody>
			                    	@foreach(@$marketing->marketing_detalle as $item)
					                    <tr>
					                      <td>{{$item->nombre}}</td>
					                      <td>{{$item->email}}</td>
					                      <td>
					                      	@php($style = $item->estatus=='Procesado' ? 'success' : 'warning')
					                      	<span class="badge badge-{{$style}}">{{$item->estatus}}</span>
					                      </td>		                      
					                    </tr>
					                @endforeach
			                    </tbody>
			                  </table>
			                </div>
			              </div>              
			            </div>
					
	
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

	

@endsection


@section('scripts')
	<script>
		
		$(document).ready(function() {

		    

			$('.btn').on('click', function() {
			    var $this = $(this);
			 	$this.button('loading');
			    setTimeout(function() {
			       $this.button('reset');
			   }, 8000);
			});



		}); 
	</script>
	
@endsection
