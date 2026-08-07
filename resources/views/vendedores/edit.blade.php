@extends('layouts.app')

@section('titulo', config('app.name', 'Laravel')  . ' - Vendedor')

@section('titulo_header', 'Modificar Vendedor')
@section('subtitulo_header', 'Vendedor')

@php
	$metasMensualesArray = $metaTableDisponible
		? $metasMensuales->map(function ($meta) {
			return [
				'periodo_key' => $meta->periodo_key,
				'meta_ventas_usd' => $meta->meta_ventas_usd,
				'meta_pedidos_aprobados' => $meta->meta_pedidos_aprobados,
				'meta_pedidos_pagados' => $meta->meta_pedidos_pagados,
				'meta_logro_pedidos_pct' => $meta->meta_logro_pedidos_pct,
			];
		})->values()->all()
		: [];
	$metasMensualesJson = json_encode($metasMensualesArray, JSON_UNESCAPED_UNICODE);
@endphp

@section('content')

	<div class="container-fluid">
 		
		@include('layouts.partials.info')
 		@include('layouts.partials.errors')

	{!! Form::open(['route' =>  ['vendedores.update', $vendedor->id], 'method' => 'PUT', 'id' => 'form']) !!}
		<input type="hidden" name="vendedor_id" id="vendedor_id" value="{{$vendedor->id}}">
		<input type="hidden" name="user_id" id="user_id" value="{{ optional($vendedor->user)->id }}">
		<div class="card card-primary card-outline">
			
			<div class="card-body">
				@include('vendedores.form')
			</div>

			@include('vendedores.footer')			
		</div>
	{!! Form::close() !!}

		@if($metaTableDisponible)
			<div class="card card-info card-outline mt-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<div class="font-weight-bold">
						<i class="fas fa-bullseye mr-1"></i> Metas mensuales
					</div>
					<span class="badge badge-light">Periodo actual: {{ $metaPeriodoActual }}</span>
				</div>
				<div class="card-body">
					<div class="row mb-3">
						<div class="col-lg-4 mb-3 mb-lg-0">
							<div class="alert alert-info h-100 mb-0">
								<div class="font-weight-bold mb-2">Configuracion por mes</div>
								<div>Esta meta se guarda en la misma tabla que usa el analisis de vendedores.</div>
								<div class="mt-2">
										<strong>Estado:</strong> <span class="js-meta-estado">{{ $metaMensual ? 'Meta registrada para este mes' : 'Sin meta registrada para este mes' }}</span>
								</div>
							</div>
						</div>
						<div class="col-lg-8">
							<form method="POST" action="{{ route('vendedores.meta.mensual.guardar') }}">
								@csrf
								<input type="hidden" name="vendedor_id" value="{{ $vendedor->id }}">
								<div class="form-row">
									<div class="form-group col-md-3">
										<label for="meta_periodo_key">Mes</label>
										<input type="month" class="form-control" id="meta_periodo_key" name="periodo_key" value="{{ old('periodo_key', optional($metaMensual)->periodo_key ?? $metaPeriodoActual) }}" required>
									</div>
									<div class="form-group col-md-3">
										<label for="meta_ventas_usd">Meta ventas USD</label>
										<input type="number" step="0.01" min="0" class="form-control" id="meta_ventas_usd" name="meta_ventas_usd" value="{{ old('meta_ventas_usd', optional($metaMensual)->meta_ventas_usd) }}" placeholder="Opcional">
									</div>
									<div class="form-group col-md-3">
										<label for="meta_pedidos_aprobados">Pedidos aprobados</label>
										<input type="number" min="0" class="form-control" id="meta_pedidos_aprobados" name="meta_pedidos_aprobados" value="{{ old('meta_pedidos_aprobados', optional($metaMensual)->meta_pedidos_aprobados) }}" placeholder="Opcional">
									</div>
									<div class="form-group col-md-3">
										<label for="meta_pedidos_pagados">Pedidos pagados</label>
										<input type="number" min="0" class="form-control" id="meta_pedidos_pagados" name="meta_pedidos_pagados" value="{{ old('meta_pedidos_pagados', optional($metaMensual)->meta_pedidos_pagados) }}" placeholder="Opcional">
									</div>
									<div class="form-group col-md-3">
										<label for="meta_logro_pedidos_pct">Cobertura objetivo (%)</label>
										<input type="number" step="0.01" min="0" max="100" class="form-control" id="meta_logro_pedidos_pct" name="meta_logro_pedidos_pct" value="{{ old('meta_logro_pedidos_pct', optional($metaMensual)->meta_logro_pedidos_pct) }}" placeholder="Opcional">
									</div>
								</div>
								<div class="d-flex justify-content-end align-items-center flex-wrap" style="gap:.5rem;">
									<small class="text-muted mr-auto">Deja vacios los campos que no apliquen, pero guarda al menos una meta.</small>
									<button type="submit" class="btn btn-primary">
										<i class="fas fa-save mr-1"></i> Guardar meta mensual
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		@else
			<div class="alert alert-warning mt-4 mb-0">
				La tabla <strong>metas_vendedores_periodo</strong> aun no existe en la base de datos de company, por eso no se puede configurar la meta mensual desde vendedores.
			</div>
		@endif

	</div>
	<div id="meta-mensuales-data" class="d-none" data-items='{{ $metasMensualesJson }}'></div>

@endsection


@section('scripts')
	<script>
		$(document).ready(function(){
			set_select2();

			const metasMensualesNode = document.getElementById('meta-mensuales-data');
			const metasMensuales = metasMensualesNode ? JSON.parse(metasMensualesNode.dataset.items || '[]') : [];

			const metaPeriodoInput = document.getElementById('meta_periodo_key');
			const metaVentasInput = document.getElementById('meta_ventas_usd');
			const metaAprobadosInput = document.getElementById('meta_pedidos_aprobados');
			const metaPagadosInput = document.getElementById('meta_pedidos_pagados');
			const metaCoberturaInput = document.getElementById('meta_logro_pedidos_pct');
			const metaEstadoText = document.querySelector('.js-meta-estado');

			const aplicarMetaMensual = (periodoKey) => {
				if (!periodoKey) {
					return;
				}

				const meta = metasMensuales.find((item) => item.periodo_key === periodoKey.toUpperCase());
				if (meta) {
					metaVentasInput.value = meta.meta_ventas_usd ?? '';
					metaAprobadosInput.value = meta.meta_pedidos_aprobados ?? '';
					metaPagadosInput.value = meta.meta_pedidos_pagados ?? '';
					metaCoberturaInput.value = meta.meta_logro_pedidos_pct ?? '';
					if (metaEstadoText) {
						metaEstadoText.textContent = 'Meta registrada para este mes';
					}
					return;
				}

				metaVentasInput.value = '';
				metaAprobadosInput.value = '';
				metaPagadosInput.value = '';
				metaCoberturaInput.value = '';
				if (metaEstadoText) {
					metaEstadoText.textContent = 'Sin meta registrada para este mes';
				}
			};

			if (metaPeriodoInput) {
				metaPeriodoInput.addEventListener('change', function () {
					aplicarMetaMensual(this.value);
				});
				aplicarMetaMensual(metaPeriodoInput.value);
			}
			
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
