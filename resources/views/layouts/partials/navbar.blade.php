<nav class="main-header navbar navbar-expand navbar-primary navbar-dark ">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('inicio') }}" class="nav-link" data-toggle="tooltip" data-placement="top"
                title="Ir al Inicio"> <i class="fa fa-home"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="https://www.instagram.com/softdesignvzla/?hl=es" target="_blank" class="nav-link"
                data-toggle="tooltip" data-placement="top" title="Cuenta Instagram Oficial">
                <i class="fab fa-instagram"></i>
            </a>
        </li>
    </ul>

    <span class="span-header-upt w-100">
        <ul class="d-none">
            <li class="nav-item d-sm-inline-block" style="list-style: none">
                <a href="{{ route('inicio') }}" class="nav-link">
                    <img class="responsive logo-nube" src="{{ asset('imgs/logos/logo_nube.png') }}" alt="SDCloud">
                    <span class="header-sd">SD<span class="header-sdcloud">Cloud</span></span>
                </a>
            </li>
        </ul>

        <div class="text-center m-auto">
            <span class="nav-item d-sm-inline-block bcv-rate text-white font-weight-bold mr-2" id="navbar-tasa-bcv">
                @php
                    $todayTasa = \App\Models\Tasa::where('fecha', now()->format('Y-m-d'))->first();
                @endphp
                @if($todayTasa)
                    BCV: {{ number_format($todayTasa->valor, 2, ',', '.') }}
                @endif
            </span>
            @auth
                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager') || Auth::user()->hasRole('admin_pedidos') || Auth::user()->hasRole('admin_gastos'))
                    <button onclick="promptUpdateTasa()" class="btn btn-xs btn-outline-light" title="Actualizar Tasa">
                        <i class="fas fa-edit"></i>
                    </button>
                @endif
            @endauth
        </div>
    </span>



    @livewire('offline')

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!--
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-comments"></i>
                <span class="badge badge-danger navbar-badge">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <a href="#" class="dropdown-item">
        
                    <div class="media">
                        <img src="{{ asset('theme/dist/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                Brad Diesel
                                <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">Call me whenever you can...</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
        
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
        
                    <div class="media">
                        <img src="{{ asset('theme/dist/img/user8-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                John Pierce
                                <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">I got your message bro</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
        
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
        
                    <div class="media">
                        <img src="{{ asset('theme/dist/img/user3-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
                        <div class="media-body">
                            <h3 class="dropdown-item-title">
                                Nora Silvester
                                <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                            </h3>
                            <p class="text-sm">The subject goes here</p>
                            <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                        </div>
                    </div>
        
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
            </div>
        </li>
        -->

        <!-- LIVEWIRE -->

        {{--
        @livewire('counter-notifications')
        --}}
        <span class="cart-icon"></span>
        @if (isset(auth()->user()->dashboard) && auth()->user()->dashboard == 'pedidos')
            @livewire('navbarshoppingcart')
        @endif

        <li class="nav-item dropdown">
            <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="nav-link dropdown-toggle">

                @auth
                    <img width="20" class="img-circle" src="{{ getUserPhoto() }}" class="img-circle elevation-2"
                        alt="Foto">
                @endauth

            </a>
            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow"
                style="{{ session()->has('impersonator') || (Auth::check() && Auth::user()->canImpersonateSellers()) ? 'min-width: 280px;' : '' }}">
                @if (session()->has('impersonator'))
                    <li class="dropdown-header text-center text-warning">
                        Impersonando a {{ Auth::user()->email }}
                    </li>
                    <li class="px-3 py-2">
                        <form action="{{ route('users.impersonate.stop') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm btn-block">
                                <i class="fas fa-undo"></i> Volver a mi cuenta
                            </button>
                        </form>
                    </li>
                    <li class="dropdown-divider"></li>
                @elseif (Auth::check() && Auth::user()->canImpersonateSellers())
                    <li class="dropdown-header text-center"><strong>Impersonar vendedor</strong></li>
                    <li class="px-3 py-2">
                        <form action="{{ route('users.impersonate') }}" method="POST">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="email" name="email" class="form-control" placeholder="Correo del vendedor"
                                    value="{{ old('email') }}" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" title="Impersonar">
                                        <i class="fas fa-sign-in-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @error('email')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </form>
                    </li>
                    <li class="dropdown-divider"></li>
                @endif

                <li>
                    <a href="{{ getUserUrl() }}" class="dropdown-item">
                        <i class="fas fa-user"></i> Mi Perfil
                    </a>
                </li>

                <li class="dropdown-divider"></li>

                <li class="nav-item">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
    </ul>
</nav>
