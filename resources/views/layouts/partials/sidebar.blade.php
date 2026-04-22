<aside class="main-sidebar sidebar-dark-primary elevation-4">

    <a href="index3.html" class="brand-link">
        <img src="{{ asset('imgs/favicon.jpg') }}" alt="Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">SoftDesign</span>
    </a>

    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                @auth
                    <img src="{{ getUserPhoto() }}" class="img-circle elevation-2" alt="Foto">
                @endauth
            </div>
            <div class="info">
                <a href="#" class="d-block">
                    {{ getUserFullNameAbrev() }}
                </a>
            </div>
        </div>

        @auth
            <div class="mt-3">
                @livewire('search')
            </div>
        @endauth

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('inicio') }}" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Inicio</p>
                    </a>
                </li>


                @auth
                    <li class="nav-item">
                        <a href="{{ getUserUrl() }}" class="nav-link">
                            <i class="nav-icon fas fa-address-card"></i>
                            <p>Mi Perfil</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('comisiones.recibidas') }}" class="nav-link">
                            <i class="nav-icon fas fa-hand-holding-usd"></i>
                            <p>Mis Comisiones</p>
                        </a>
                    </li>

                    @ability('admin,owner', 'landing-settings')
                        <li class="nav-item">
                            <a href="{{ url('landing-settings') }}" class="nav-link">
                                <i class="nav-icon fas fa-link"></i>
                                <p>Mi Landing</p>
                            </a>
                        </li>
                    @endability

                    @if (auth()->user()->isAdministrator() || auth()->user()->isAdministrativerUser())
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-window-maximize"></i>
                                <p>
                                    Administración
                                    <i class="right fas fa-angle-left"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @ability('admin,owner', 'module')
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pagos</p>
                                        </a>
                                    </li>
                                @endability
                                @ability('admin,owner', 'module')
                                    <li class="nav-item">
                                        <a href="#" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Vendedores</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('comisiones.estado_cuenta') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Estado de Cuenta</p>
                                        </a>
                                    </li>
                                @endability
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-shopping-cart"></i>
                                <p>
                                    Inventario
                                    <i class="right fas fa-angle-left"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tasa Dólar</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Productos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pedidos</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Reportes gerenciales --}}
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>
                                    Reportes
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.reportes.inventario.dashboard') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard Inventario</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reportes.inventario') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Inventario General</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reportes.inventario.deposito') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Inventario por Depósito</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.producto_bultos.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Conf. Bultos</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('cliente-vendedor.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Asociar Clientes-Vendedores</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-laptop-medical"></i>
                                <p>
                                    Farmax
                                    <i class="right fas fa-angle-left"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('ftp') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Conf. Farmacias</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('drugstores') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Droguerias</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->isAdministrator())
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-laptop"></i>
                                <p>
                                    Landings
                                    <i class="right fas fa-angle-left"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('landings') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Gestión</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('themes') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Temas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cloud"></i>
                                <p>
                                    SDCloud 2020
                                    <i class="fas fa-angle-left right"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @ability('admin,owner', 'serial')
                                    <li class="nav-item">
                                        <a href="{{ url('serials') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Seriales</p>
                                        </a>
                                    </li>
                                @endability

                                @ability('admin,owner', 'serial')
                                    <li class="nav-item">
                                        <a href="{{ url('master-key') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Maestra</p>
                                        </a>
                                    </li>
                                @endability

                                @ability('admin,owner', 'module')
                                    <li class="nav-item">
                                        <a href="{{ url('modules') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Módulos</p>
                                        </a>
                                    </li>
                                @endability

                                @ability('admin,owner', 'command')
                                    <li class="nav-item">
                                        <a href="{{ url('commands') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Comandos</p>
                                        </a>
                                    </li>
                                @endability
                            </ul>

                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-lock"></i>
                                <p>
                                    Seguridad
                                    <i class="fas fa-angle-left right"></i>
                                    <span class="badge badge-info right">6</span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @ability('admin,owner', 'user')
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Usuarios</p>
                                        </a>
                                    </li>
                                @endability
                                @ability('admin,owner', 'company')
                                    <li class="nav-item">
                                        <a href="{{ route('companies.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Empresas</p>
                                        </a>
                                    </li>
                                @endability
                                @ability('admin', 'role')
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Roles</p>
                                        </a>
                                    </li>
                                @endability
                                @ability('admin', 'permission')
                                    <li class="nav-item">
                                        <a href="{{ route('permissions.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Opciones</p>
                                        </a>
                                    </li>
                                @endability
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-passport"></i>
                                <p>
                                    Web
                                    <i class="right fas fa-angle-left"></i>
                                    <span class="badge badge-info right"></span>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('categories') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Noticias</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif
                @endauth


            </ul>
        </nav>

    </div>

</aside>
