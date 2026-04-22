<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('tipo', 'Tipo', ['class' => 'control-label']) }}
			<select class="form-control select" data-placeholder="Seleccione" style="width: 100%;" id="tipo" name="tipo">
				<option value="producto_nuevo">Prod. Nuevo</option>				
				<option value="producto_promocion">Prod. en Promoción</option>				
				<option value="personalizado">Email Personalizado</option>				
				<option value="whatsapp">Whatsapp</option>				
				{{--
				<option value="producto_futuro">Prod. por llegar</option> 
				--}}
      </select>
		</div>
	</div>

	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('codigo', 'Producto', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="codigo" name="codigo">
				@foreach(@$products as $item)
			        @if($item->CODIGO == @$marketing->codigo)
			            <option selected="selected" value="{{$item->CODIGO}}">{{$item->DESCR}}</option>
			        @else
			            <option value="{{$item->CODIGO}}">{{$item->DESCR}}</option>
			        @endif
			    @endforeach
            </select>
		</div>
	</div> 
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('contacto', 'Info. Contacto', ['class' => 'control-label']) }}
			{!! Form::text('contacto', @$marketing->contacto, ['class' => 'form-control ', 'placeholder' => 'Información para el contacto', 'id' => 'contacto']) !!}
		</div>
	</div>
	
</div>

<div class="row">
	<div class="col-md-12">
		<div class="form-group">
			{{ Form::label('mensaje', 'Mensaje', ['class' => 'control-label']) }}
			{!! Form::textarea('mensaje', @$marketing->mensaje, ['class' => 'form-control ', 'placeholder' => 'Mensaje', 'id' => 'mensaje']) !!}
		</div>
	</div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {{ Form::label('image', 'Imagen', ['class' => 'control-label']) }}
            <div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
                {{ Form::file('image', ['class' => 'btn btn-outline-primary btn-block ']) }}
            </div>
        </div>
    </div>

    <div class="col-md-2">
        <img src="{{ asset('storage/products/' . @$marketing->image)}}" width="200" alt="Logo">
    </div>
</div>

@if (@$marketing->id)
	<div class="row mt-3">
		<div class="col-md-12">
			<div class="alert alert-primary" role="alert">
  				<i class="fa fa-envelope"></i> Clientes de la campaña
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-10">
			<div class="form-group">
				{{ Form::label('rif', 'Buscar Cliente', ['class' => 'control-label']) }}
				<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="rif" name="rif">
					<option value=""></option> 
					@foreach(@$clients as $item)
				        <option value="{{$item->RIF}}">{{$item->NOMBRE}} ({{$item->RIF}})</option>
				    @endforeach
	            </select>
			</div>
		</div>
		<div class="col-md-2">
				{{ Form::label('', '.', ['class' => 'control-label']) }}
				<div class="form-group">
					<button id="btn_all_clients" class="btn btn-primary btn-block"><i class="fa fa-arrow-down"></i> Agregar Todos</button>
				</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				{{ Form::label('razon_social', 'Razón Social', ['class' => 'control-label']) }}
				{!! Form::text('razon_social', '', ['class' => 'form-control ', 'placeholder' => 'Razón Social', 'id' => 'razon_social']) !!}
			</div>
		</div>	
		<div class="col-md-4">
			<div class="form-group">
				@php($label = (@$marketing->tipo=='whatsapp')?'Whatsapp':'Email')
				{{ Form::label('email', $label, ['class' => 'control-label']) }}
				{!! Form::text('email', '', ['class' => 'form-control ', 'placeholder' => $label, 'id' => 'email']) !!}
			</div>
		</div>	
		<div class="col-md-2">
			<div class="form-group">
				{{ Form::label('', '.', ['class' => 'control-label']) }}
				<div class="form-group">
					<button id="btn_save" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Agregar</button>
				</div>
			</div>
		</div>	

	</div>

	<div class="row">
		<div class="col-12">
			<div class="card bg">
              <div class="card-header bg-info border-0">
                <h3 class="card-title">Clientes</h3>
                <div class="card-tools">                  
                  <a href="#" class="btn btn-tool btn-sm">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                  <tbody id="detalle">
                  	@if (@$marketing->marketing_detalle && sizeof(@$marketing->marketing_detalle))
                  		@foreach ($marketing->marketing_detalle as $item)
                  			<tr>
                  				<td>{{ $item->nombre }}</td>
                  				<td>{{ $item->email }}</td>
                  				<td> <a href="" class="btn btn-outline-danger" onclick="event.preventDefault(); removeItem({{$item->id}})"><i class="fa fa-trash"></i></a> </td>
                  			</tr>
                  		@endforeach
                  	@else
		                  <tr>
		                    <td colspan="3">
		                      No hay información para mostrar...
		                    </td>
		                  </tr>
		                @endif
                  </tbody>
                </table>
              </div>
            </div>
		</div>
	</div>
@endif

