@extends('layouts.app')

@section('titulo', 'Logística - Nueva Caja')
@section('titulo_header', 'Armar Caja')
@section('subtitulo_header', 'Crear Paquete')

@section('content')
<section class="content">
    <div class="container-fluid">
        @include('admin.logistica.partials.form', [
            'action' => route('admin.logistica.store'),
            'method' => 'POST',
            'submitLabel' => 'Crear caja',
            'pedidosIniciales' => [],
        ])
    </div>
</section>
@endsection
