<div class="card">
    <div class="card-header border-0">
    	<h3 class="card-title">Migración SUGAU</h3>
        <div class="card-tools">
        	
        	<div wire:loading>
           		<a href="#" class="btn btn-tool btn-sm text-primary hint--top" aria-label="Procesando...">
			        <i class='nav-icon fas fa-circle-notch fa-2x fa-spin fa-fw'></i>
			    </a>
			</div>

        	<input wire:model="anno" class="form form-input hint--top" aria-label="Año" placeholder="Año" type="text" />
        	<input wire:model="period" class="form form-input hint--top" aria-label="Período" placeholder="Período" type="text" />
        	<input wire:model="payroll" class="form form-input hint--top" aria-label="Nómina" placeholder="Nómina" type="text" />

            <a href="#" wire:click="migrateDataVouchers" class="btn btn-tool btn-sm hint--left" aria-label="Realizar migración">
                <i class="fas fa-download"></i>
            </a>
            <a href="#" class="btn btn-tool btn-sm">
                <i class="fas fa-bars"></i>
            </a>
        </div>
    </div>
    
	<div class="card-body table-responsive p-0">
        <table class="table table-striped table-valign-middle">
        	@if (empty($migrations))
            	<thead>
	        	    <tr>
	                    <th>No hay datos para mostrar</th>
	                </tr>
	            </thead>
        	@else
            	<thead>
	                <tr>
	                    <th>Fecha</th>
	                    <th>Usuario</th>
	                    <th>Año</th>
	                    <th>Período</th>
	                    <th>Nómina</th>
	                    <th>Estatus</th>
	                </tr>
            	</thead>
				@foreach($migrations as $migration)
					<tr>
			            <td>{{getHumanDate($migration->created_at)}}</td>
			        	<td>
			            	<img src="{{asset('imgs/users/' . $migration->user->photo)}}" alt="usuario" class="img-circle img-size-32 mr-2">
			                {{ $migration->user->name }} {{ $migration->user->last_name }}
			            </td>
			            <td>
			            	{{ $migration->year }}
			            </td>
			            <td>
			            	{{ $migration->period }}
			            </td>
			            <td>
			            	{{ $migration->payroll }}
			            </td>
			            <td>
			            	@if ($migration->status=='error')
			            		<span class="badge bg-{{$migration->status}}">Fallido</span>
			            	@else
			            		<span class="badge bg-{{$migration->status}}">Ok</span>
			            	@endif
			            </td>
			        </tr>
				@endforeach
			@endif
    	</table>
  	</div>
</div>
