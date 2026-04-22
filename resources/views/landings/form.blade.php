<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('company_id', 'Empresa', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="company_id" name="company_id">
				@foreach($companies as $item)
					<option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
            </select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('theme_id', 'Tema', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="theme_id" name="theme_id">
				@foreach($themes as $item)
					<option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
            </select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('status', 'Estatus', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="status" name="status">
				<option value="A">Activo</option>                
				<option value="">Inactivo</option>                
            </select>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('name', 'Nombre de Landing', ['class' => 'control-label']) }}
			{!! Form::text('name', @$landing->name, ['class' => 'form-control ', 'placeholder' => 'Nombre del Tema' ,'required', 'id' => 'name']) !!}
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('company_name', 'Nombre de Empresa', ['class' => 'control-label']) }}
			{!! Form::text('company_name', @$landing->company_name, ['class' => 'form-control ', 'placeholder' => 'Nombre a mostrar en landing', 'require', 'id' => 'company_name']) !!}
		</div>
	</div>
</div>



<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('schedule', 'Horario', ['class' => 'control-label']) }}
			{!! Form::text('schedule', @$landing->schedule, ['class' => 'form-control ', 'placeholder' => 'Horario', 'id' => 'schedule']) !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('email', 'Email', ['class' => 'control-label']) }}
			{!! Form::text('email', @$landing->email, ['class' => 'form-control ', 'placeholder' => 'Email', 'id' => 'email']) !!}
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			{{ Form::label('address', 'Dirección', ['class' => 'control-label']) }}
			{!! Form::text('address', @$landing->address, ['class' => 'form-control ', 'placeholder' => 'Dirección', 'id' => 'address']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('phone', 'Teléfono', ['class' => 'control-label']) }}
			{!! Form::text('phone', @$landing->phone, ['class' => 'form-control ', 'placeholder' => 'Teléfono', 'id' => 'phone']) !!}
		</div>
	</div>
	<div class="col-md-3">
		<div class="form-group">
			{{ Form::label('whatsapp', 'Whatsapp', ['class' => 'control-label']) }}
			{!! Form::text('whatsapp', @$landing->whatsapp, ['class' => 'form-control ', 'placeholder' => 'Whatsapp', 'id' => 'whatsapp']) !!}
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			{{ Form::label('float_whatsapp', 'Botón Ws', ['class' => 'control-label']) }}
			<select class="form-control select2" data-placeholder="Seleccione" style="width: 100%;" id="float_whatsapp" name="float_whatsapp">
				<option value="S">SI</option>                
				<option value="">NO</option>                
            </select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('float_whatsapp_text', 'Mensaje WS', ['class' => 'control-label']) }}
			{!! Form::text('float_whatsapp_text', @$landing->float_whatsapp_text, ['class' => 'form-control ', 'placeholder' => 'Mensaje predefinido', 'id' => 'float_whatsapp_text']) !!}
		</div>
	</div>
</div>


<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('instagram', 'Instagram', ['class' => 'control-label']) }}
			{!! Form::text('instagram', @$landing->instagram, ['class' => 'form-control ', 'placeholder' => 'Instagram', 'id' => 'instagram']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('facebook', 'Facebook', ['class' => 'control-label']) }}
			{!! Form::text('facebook', @$landing->facebook, ['class' => 'form-control ', 'placeholder' => 'Facebook', 'id' => 'facebook']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('twitter', 'Twitter', ['class' => 'control-label']) }}
			{!! Form::text('twitter', @$landing->twitter, ['class' => 'form-control ', 'placeholder' => 'Twitter', 'id' => 'twitter']) !!}
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('title', 'Título Landing', ['class' => 'control-label']) }}
			{!! Form::text('title', @$landing->title, ['class' => 'form-control ', 'placeholder' => 'Título Landing', 'id' => 'title']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('slogan', 'Slogan', ['class' => 'control-label']) }}
			{!! Form::text('slogan', @$landing->slogan, ['class' => 'form-control ', 'placeholder' => 'Slogan', 'id' => 'slogan']) !!}
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('video', 'Video', ['class' => 'control-label']) }}
			{!! Form::text('video', @$landing->video, ['class' => 'form-control ', 'placeholder' => 'Video', 'id' => 'video']) !!}
		</div>
	</div>
</div>









<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('image', 'Logo Web', ['class' => 'control-label']) }}
			<div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
                {{ Form::file('main_logo', ['class' => 'btn btn-outline-primary btn-block ']) }}
            </div>
		</div>
	</div>

	<div class="col-md-2">
	    <img class="img-responsive" src="{{ getUrlImage(@$landing, 'main_logo') }}" width="200px">
	</div>
	<div class="col-md-4">
		<div class="form-group">
			{{ Form::label('image_slider', 'Logo Footer', ['class' => 'control-label']) }}
			<div class="col-md-12 col-sm-12 col-xs-12 hint--top" aria-label="Click para cambiar la imagen">
                {{ Form::file('footer_logo', ['class' => 'btn btn-outline-primary btn-block ']) }}
            </div>
		</div>
	</div>

	<div class="col-md-2">
	    <img class="img-responsive" src="{{ getUrlImage(@$landing, 'footer_logo') }}" width="200px">
	</div>
</div>