<li class="nav-item dropdown">
<a class="nav-link" data-toggle="dropdown" href="#">
    <i class="far fa-bell"></i>
    @if ($count_notifications) 
	    <span class="badge badge-primary navbar-badge">
	    	{{ $count_notifications }}
	    </span>
	@endif
</a>
<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" wire:poll.300s>
    <span class="dropdown-item dropdown-header">{{ $count_notifications }} Notificaciones</span>
    
    @foreach($notifications as $notification)
	    <div class="dropdown-divider"></div>
	    <a href="{{$notification->url}}" class="dropdown-item">
	        <i class="{{$notification->icon}} mr-2"></i> {{$notification->description}}
	        <span class="float-right text-muted text-sm">{{getHumanDate($notification->created_at)}}</span>
	    </a>
	@endforeach
    
    <div class="dropdown-divider"></div>
    <a href="#" class="dropdown-item dropdown-footer">Ver Todas</a>
	
</div>
</li>
