@extends('layouts.app')

@section('titulo', 'Lista de Precios')
@section('titulo_header', 'Lista de Precios')

@section('styles')
<style>
    :root { --price-ink:#17324d; --price-accent:#087e8b; --price-warm:#ffb703; --price-bg:#f4f7f8; }
    .content-wrapper { background:var(--price-bg); }
    .price-hero { background:linear-gradient(120deg,#123047 0%,#087e8b 72%,#0aa3a3 100%); color:#fff; padding:22px 24px; border-radius:8px; box-shadow:0 12px 28px rgba(18,48,71,.18); }
    .price-hero h2 { font-family:Georgia,serif; font-size:1.75rem; margin:0; letter-spacing:0; }
    .price-hero .rate { background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.3); padding:8px 12px; border-radius:6px; white-space:nowrap; }
    .filter-shell { background:#fff; border-top:4px solid var(--price-warm); border-radius:6px; box-shadow:0 5px 18px rgba(23,50,77,.08); }
    .filter-label { color:#41566a; font-size:.75rem; font-weight:700; text-transform:uppercase; }
    .summary-strip { display:flex; gap:10px; overflow-x:auto; padding:2px 0 8px; }
    .summary-item { min-width:130px; background:#fff; border-left:3px solid var(--price-accent); padding:9px 12px; box-shadow:0 2px 8px rgba(23,50,77,.07); }
    .summary-item strong { display:block; color:var(--price-ink); font-size:1rem; }
    .product-table thead th { background:var(--price-ink); color:#fff; border:0; font-size:.76rem; text-transform:uppercase; vertical-align:middle; }
    .product-table td { vertical-align:middle; }
    .product-thumb { width:58px; height:58px; object-fit:cover; border-radius:6px; background:#eef2f3; }
    .code-pill { color:#087e8b; font-family:Consolas,monospace; font-weight:700; }
    .price-value { color:var(--price-ink); font-weight:800; }
    .mobile-products { display:none; }
    .mobile-product { background:#fff; border-radius:7px; box-shadow:0 4px 14px rgba(23,50,77,.09); overflow:hidden; }
    .mobile-product-body { display:grid; grid-template-columns:76px 1fr; gap:12px; padding:12px; }
    .mobile-product .product-thumb { width:76px; height:82px; }
    .mobile-prices { display:grid; grid-template-columns:1fr 1fr; background:#eef6f6; border-top:1px solid #d9e8e8; }
    .mobile-prices > div { padding:10px 12px; }
    .mobile-prices > div + div { border-left:1px solid #d1e0e0; }
    .empty-state { background:#fff; border:1px dashed #b8c5cc; border-radius:8px; padding:48px 20px; text-align:center; color:#667987; }
    @media (max-width:767.98px) {
        .price-hero { padding:18px; }
        .price-hero h2 { font-size:1.45rem; }
        .hero-row { align-items:flex-start !important; }
        .desktop-products { display:none; }
        .mobile-products { display:grid; gap:12px; }
        .filter-actions .btn { flex:1; }
        .content { padding-left:8px !important; padding-right:8px !important; }
    }
</style>
@endsection

@section('content')
<section class="content pb-4">
    <div class="container-fluid">
        <div class="price-hero mb-3">
            <div class="d-flex justify-content-between hero-row">
                <div>
                    <div class="text-uppercase small mb-1">Catálogo comercial</div>
                    <h2>Precios listos para compartir</h2>
                    <div class="mt-2 text-white-50">Filtra el inventario y genera un PDF para tus clientes.</div>
                </div>
                <div class="rate text-right ml-3">
                    <small class="d-block">Moneda</small>
                    <strong>Divisa</strong>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('vendedor.lista-precios.index') }}" class="filter-shell p-3 mb-3" id="priceFilters">
            <div class="row">
                <div class="col-12 col-lg-4 form-group">
                    <label class="filter-label" for="buscar">Producto</label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                        <input type="search" class="form-control" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="Código o descripción">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-4 form-group">
                    <label class="filter-label" for="cdepos">Depósito</label>
                    <select class="form-control" id="cdepos" name="cdepos">
                        @foreach($depositos as $deposito)
                            <option value="{{ $deposito->CDEPOS }}" {{ $depositoSeleccionado == $deposito->CDEPOS ? 'selected' : '' }}>{{ $deposito->DDEPOS }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-lg-4 form-group">
                    <label class="filter-label" for="cgrupo">Grupos</label>
                    <select class="form-control select2" id="cgrupo" name="cgrupo[]" multiple data-placeholder="Todos los grupos">
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->CGRUPO }}" {{ in_array($grupo->CGRUPO, (array) request('cgrupo', [])) ? 'selected' : '' }}>{{ $grupo->DGRUPO }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-lg-2 form-group">
                    <label class="filter-label" for="precio_desde">Precio desde</label>
                    <input type="number" min="0" step="0.01" class="form-control" id="precio_desde" name="precio_desde" value="{{ request('precio_desde') }}" placeholder="$ 0,00">
                </div>
                <div class="col-6 col-lg-2 form-group">
                    <label class="filter-label" for="precio_hasta">Precio hasta</label>
                    <input type="number" min="0" step="0.01" class="form-control" id="precio_hasta" name="precio_hasta" value="{{ request('precio_hasta') }}" placeholder="Sin límite">
                </div>
                <div class="col-6 col-lg-3 form-group">
                    <label class="filter-label" for="stock">Disponibilidad</label>
                    <select class="form-control" id="stock" name="stock">
                        <option value="">Cualquier stock</option>
                        <option value="con_stock" {{ request('stock') === 'con_stock' ? 'selected' : '' }}>Disponible</option>
                        <option value="bajo" {{ request('stock') === 'bajo' ? 'selected' : '' }}>Últimas unidades (1-10)</option>
                        <option value="sin_stock" {{ request('stock') === 'sin_stock' ? 'selected' : '' }}>Agotado</option>
                    </select>
                </div>
                <div class="col-6 col-lg-3 form-group">
                    <label class="filter-label" for="orden">Ordenar por</label>
                    <select class="form-control" id="orden" name="orden">
                        <option value="descripcion" {{ request('orden') === 'descripcion' ? 'selected' : '' }}>Descripción A-Z</option>
                        <option value="codigo" {{ request('orden') === 'codigo' ? 'selected' : '' }}>Código</option>
                        <option value="precio_asc" {{ request('orden') === 'precio_asc' ? 'selected' : '' }}>Menor precio</option>
                        <option value="precio_desc" {{ request('orden') === 'precio_desc' ? 'selected' : '' }}>Mayor precio</option>
                        <option value="stock_desc" {{ request('orden') === 'stock_desc' ? 'selected' : '' }}>Mayor stock</option>
                    </select>
                </div>
                <div class="col-12 col-lg-2 form-group d-flex align-items-end filter-actions">
                    <button class="btn btn-info mr-2" type="submit"><i class="fas fa-sliders-h mr-1"></i> Aplicar</button>
                    <a class="btn btn-light" href="{{ route('vendedor.lista-precios.index') }}" title="Limpiar filtros"><i class="fas fa-undo"></i></a>
                </div>
            </div>
        </form>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div class="summary-strip flex-grow-1 mr-md-3">
                <div class="summary-item"><small>Resultados</small><strong>{{ number_format($productos->total()) }}</strong></div>
                <div class="summary-item"><small>Depósito</small><strong>{{ optional($depositos->firstWhere('CDEPOS', $depositoSeleccionado))->DDEPOS }}</strong></div>
                @if($recargo > 0)<div class="summary-item"><small>Recargo aplicado</small><strong>{{ number_format($recargo, 2) }}%</strong></div>@endif
            </div>
            <a href="{{ route('vendedor.lista-precios.pdf', request()->query()) }}" class="btn btn-danger btn-lg mt-2 mt-md-0" target="_blank">
                <i class="fas fa-file-pdf mr-2"></i>Generar PDF
            </a>
        </div>

        @if($productos->count())
            <div class="desktop-products table-responsive bg-white shadow-sm">
                <table class="table table-hover product-table mb-0">
                    <thead><tr><th>Foto</th><th>Código / Producto</th><th>Grupo</th><th class="text-right">REF1</th><th class="text-right">REF2</th><th class="text-center">Stock</th></tr></thead>
                    <tbody>
                        @foreach($productos as $producto)
                            <tr>
                                <td><img class="product-thumb" src="{{ $producto->FOTO ? asset('storage/products/' . $producto->FOTO) : asset('storage/products/nofoto.jpg') }}" alt="{{ $producto->DESCR }}"></td>
                                <td><span class="code-pill">{{ $producto->CODIGO }}</span><br><strong>{{ $producto->DESCR }}</strong></td>
                                <td>{{ $producto->DGRUPO ?: 'Sin grupo' }}</td>
                                <td class="text-right price-value">$ {{ number_format($producto->precio_ref1, 2, ',', '.') }}</td>
                                <td class="text-right price-value">$ {{ number_format($producto->precio_ref2, 2, ',', '.') }}</td>
                                <td class="text-center"><span class="badge badge-{{ $producto->stock > 10 ? 'success' : ($producto->stock > 0 ? 'warning' : 'secondary') }} p-2">{{ number_format($producto->stock, 0, ',', '.') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mobile-products">
                @foreach($productos as $producto)
                    <article class="mobile-product">
                        <div class="mobile-product-body">
                            <img class="product-thumb" src="{{ $producto->FOTO ? asset('storage/products/' . $producto->FOTO) : asset('storage/products/nofoto.jpg') }}" alt="{{ $producto->DESCR }}">
                            <div>
                                <div class="d-flex justify-content-between"><span class="code-pill">{{ $producto->CODIGO }}</span><span class="badge badge-{{ $producto->stock > 10 ? 'success' : ($producto->stock > 0 ? 'warning' : 'secondary') }}">Stock {{ number_format($producto->stock, 0) }}</span></div>
                                <strong class="d-block mt-1">{{ $producto->DESCR }}</strong>
                                <small class="text-muted">{{ $producto->DGRUPO ?: 'Sin grupo' }}</small>
                            </div>
                        </div>
                        <div class="mobile-prices">
                            <div><small class="text-muted d-block">REF1</small><strong>$ {{ number_format($producto->precio_ref1, 2, ',', '.') }}</strong></div>
                            <div><small class="text-muted d-block">REF2</small><strong>$ {{ number_format($producto->precio_ref2, 2, ',', '.') }}</strong></div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-3 d-flex justify-content-center">{{ $productos->links() }}</div>
        @else
            <div class="empty-state"><i class="fas fa-box-open fa-3x mb-3"></i><h5>No encontramos productos</h5><p class="mb-0">Prueba ampliando el precio, el grupo o la disponibilidad.</p></div>
        @endif
    </div>
</section>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#cgrupo').select2({
            theme: 'bootstrap4',
            width: '100%',
            closeOnSelect: false,
            placeholder: 'Todos los grupos'
        });
        $('#cdepos, #stock, #orden').on('change', function () {
            $('#priceFilters').submit();
        });
    });
</script>
@endsection