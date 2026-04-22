@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel') . ' - Bancos Clientes')

@section('titulo_header', 'Crear Banco Client')
@section('subtitulo_header', 'Bancos Clientes')

@section('styles')

@endsection

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"> Formulario de Registro </h3>
                    </div>

                    <form action="{{ route('banks.store') }}" method="POST" autocomplete="off" class="form-horizontal">
                        @csrf
                        <div class="card-body">

                            <div class="form-group row {{ $errors->has('codigo') ? 'has-error' : '' }}">
                                <label for="codigo" class="col-sm-2 col-form-label">Código</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="codigo" name="codigo"
                                        placeholder="Código del banco" value="{{ old('codigo') }}">
                                    @if ($errors->has('codigo'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('codigo') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('nombre') ? 'has-error' : '' }}">
                                <label for="nombre" class="col-sm-2 col-form-label">Nombre</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        placeholder="Nombre del banco" value="{{ old('nombre') }}" required>
                                    @if ($errors->has('nombre'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('nombre') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary hint--top" aria-label="Guardar el registro">
                                <i class="fa fa-save"> </i> Guardar
                            </button>
                            <a href="{{ route($route) }}" class="btn btn-danger hint--top"
                                aria-label="Cancelar y volver a la lista">
                                <i class="fa fa-reply"> </i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

@endsection
