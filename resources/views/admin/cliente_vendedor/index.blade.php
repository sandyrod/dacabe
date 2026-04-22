@extends('layouts.app')

@section('titulo', 'Asociación Clientes - Vendedores')
@section('titulo_header', 'Gestión de Asociaciones')
@section('subtitulo_header', 'Asocie clientes existentes a vendedores específicos')

@section('styles')
<style>
    .table-hover tbody tr:hover { background-color: rgba(67, 97, 238, 0.04); }
    .badge-asociado { background: #28a745; color: white; }
    .cliente-info { font-size: 0.9em; color: #6c757d; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">

        {{-- ── Panel de asociaciones existentes ── --}}
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-navy text-white d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="fas fa-users mr-2"></i>Asociaciones Existentes
                        <span class="badge badge-light ml-2">{{ $asociaciones->count() }}</span>
                        @if(request()->hasAny(['vendedor', 'cliente']))
                            <small class="text-light">(filtrado)</small>
                        @endif
                    </h5>
                    <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalAgregar">
                        <i class="fas fa-plus mr-1"></i>Nueva Asociación
                    </button>
                </div>

                {{-- Filtros --}}
                <div class="card-body pb-0">
                    <form method="GET" action="{{ route('cliente-vendedor.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted mb-1">Filtrar por Vendedor</label>
                                <select name="vendedor" class="form-control form-control-sm select2">
                                    <option value="">— Todos los vendedores —</option>
                                    @foreach($vendedoresFiltro as $vendedor)
                                        <option value="{{ $vendedor->email }}" @selected(request('vendedor') == $vendedor->email)>
                                            {{ $vendedor->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted mb-1">Filtrar por Cliente</label>
                                <select name="cliente" class="form-control form-control-sm select2">
                                    <option value="">— Todos los clientes —</option>
                                    @foreach($clientesFiltro as $cliente)
                                        <option value="{{ $cliente->RIF }}" @selected(request('cliente') == $cliente->RIF)>
                                            {{ $cliente->NOMBRE }} ({{ $cliente->RIF }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-filter mr-1"></i>Filtrar
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('cliente-vendedor.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-times mr-1"></i>Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body pt-0">
                    @if($asociaciones->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay asociaciones configuradas</h5>
                            <p class="text-muted">Cree asociaciones para limitar el acceso de vendedores a sus clientes.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Cliente</th>
                                        <th>RIF</th>
                                        <th>Vendedor</th>
                                        <th>Email Vendedor</th>
                                        <th>Fecha Asociación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asociaciones as $asoc)
                                    <tr>
                                        <td>
                                            <strong>{{ $asoc->cliente->NOMBRE ?? 'N/A' }}</strong>
                                            <div class="cliente-info">{{ $asoc->cliente->DIRECCION ?? '' }}</div>
                                        </td>
                                        <td><code>{{ $asoc->rif }}</code></td>
                                        <td>{{ $asoc->vendedor && $asoc->vendedor->user ? trim($asoc->vendedor->user->name . ' ' . $asoc->vendedor->user->last_name) : 'N/A' }}</td>
                                        <td>{{ $asoc->email_vendedor }}</td>
                                        <td>{{ $asoc->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('cliente-vendedor.destroy', $asoc->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('¿Eliminar esta asociación?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modal para agregar asociación ── --}}
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-gradient-navy text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus mr-2"></i>Nueva Asociación Cliente-Vendedor
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('cliente-vendedor.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rif">Cliente <span class="text-danger">*</span></label>
                                    <select name="rif" id="rif" class="form-control select2" required>
                                        <option value="">— Seleccione cliente —</option>
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->RIF }}">{{ $cliente->NOMBRE }} ({{ $cliente->RIF }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email_vendedor">Vendedor <span class="text-danger">*</span></label>
                                    <select name="email_vendedor" id="email_vendedor" class="form-control select2" required>
                                        <option value="">— Seleccione vendedor —</option>
                                        @foreach($vendedores as $vendedor)
                                            <option value="{{ $vendedor->email }}">{{ $vendedor->display_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Crear Asociación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
    <script src="{{ asset('theme/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar select2 para filtros
            $('select[name="vendedor"], select[name="cliente"]').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione una opción',
                allowClear: true,
                width: '100%'
            });

            // Inicializar select2 en modal
            $('#rif, #email_vendedor').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione una opción',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalAgregar')
            });
        });
    </script>
@endsection