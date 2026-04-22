<div class="relative">
	    <div class="input-group input-group-sm">
	        <input 
	        	wire:model="query"
	            class="form-control" 
	        	type="search" 
	        	placeholder="Buscar..." 
	        	aria-label="Search">
	        <div class="input-group-append">
	        </div>
	    </div>

        <div wire:loading wire:target="query">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
			        <li class="nav-item text-center">
		                <a href="#" class="nav-link">
		                	<i class='nav-icon fas fa-circle-notch fa-2x fa-spin fa-fw'></i>
		                    <p>Actualizando...</p>
		                </a>
		            </li>
		        </ul>
		</div>
		

		@if (!empty($permissions))
			<nav class="mt-2">
	            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
			
				@foreach($permissions as $permission)
		            <li class="nav-item">
		                    <a href="{{ url($permission->url) }}" class="nav-link">
		                        <i class="nav-icon far fa-circle"></i>
		                        <p>{{ $permission->display_name }}</p>
		                    </a>
		                </li>
				@endforeach
				</ul>
			</nav>
		@endif
	
</div>
