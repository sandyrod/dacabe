@extends('layouts.app')

@section('titulo', 'Logística - Editar Caja')
@section('titulo_header', 'Editar Caja')
@section('subtitulo_header', 'Actualizar Paquete')

@section('content')
<section class="content">
    <div class="container-fluid">
        @include('admin.logistica.partials.form', [
            'action' => route('admin.logistica.update', $caja->id),
            'method' => 'PUT',
            'submitLabel' => 'Guardar cambios',
            'pedidosIniciales' => $pedidosDisponibles,
        ])
    </div>
</section>
@endsection
