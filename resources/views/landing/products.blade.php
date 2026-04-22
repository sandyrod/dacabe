@extends('landing.layouts.app_products')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css')
@endsection

@section('content')
        

        <section class="blog section1">
            <div class="container">
                <div class="row pt-4">
                    <div class="col-12 mb-4">
                        <form method="GET" action="" class="card card-body shadow-sm p-3 mb-0" style="background: #fff8f0; border-radius: 1rem;">
                            <div class="row align-items-end">
                                <div class="col-md-8 mb-2">
                                    <label for="search_descr" class="font-weight-bold mb-1">Buscar por nombre</label>
                                    <input type="text" class="form-control" id="search_descr" name="search_descr" placeholder="Ej: Botas..." value="{{ request('search_descr') }}">
                                </div>
                                <div class="col-md-4 mb-2 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-warning mr-2 flex-fill"><i class="fa fa-search"></i> Buscar</button>
                                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary flex-fill ml-2"><i class="fa fa-times"></i> Reiniciar</a>
                                </div>
                            </div>
                        </form>
                        <div class="row mb-2 mt-2">
                            <div class="col-12">
                                <div class="alert alert-info py-2 px-3 mb-0" style="border-radius: 0.5rem;">
                                    <strong>Resultados:</strong> Se encontraron <b>{{ $products->count() }}</b> productos
                                    @if(request('search_descr'))
                                        para la búsqueda <b>"{{ request('search_descr') }}"</b>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="section-title">
                            @php($category_show = @$category ? $category : '')
                            <h2>Catálogo de Productos</h2>
                            <h3 style="color: #ff9f2f;">{{$category_show}}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    @foreach(@$products as $product)
                        @if($product->EUNIDAD && $product->EUNIDAD > 0 && $product->EUNIDAD >= $product->SMIN && $product->BASE1 >= 0)
                            <div class="col-lg-4 col-md-6 col-12 pt-5">
                                <!-- Single Blog -->
                                <div class="single-news">
                                    <div class="news-head">
                                        <img src="{{asset('storage/products/').'/'.$product->FOTO}}" alt="{{$product->DESCR}}">
                                    </div>
                                    <div class="news-body">
                                        <div class="news-content">
                                            @php($dacabe_percent = obtenerDescuentoGlobal())
                                            @php($porc = $dacabe_percent>0?$dacabe_percent:35)
                                            @php($recargo = $seller->recargo>0?$seller->recargo:0)
                                            
                                            <!-- Cedano VALIDAR PRODUCTOS NACIONALES O NO PARA PRECIO2 -->
                                            @php($precio2 = $product->BASE1+(($product->BASE1*$porc/100)))
                                            @php($precio2 = $product->BASE2)
                                            @php($precio2 += $precio2*($recargo/100))
                                            <div class="date">$ {{round($precio2, 2)}}</div>
                                            <h2><a href="blog-single.html">{{$product->DESCR}}</a></h2>
                                            <p class="text">{{$product->DGRUPO}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                    @endforeach
                    
                    </div>
                </div>
            </div>
        </section>
        <!-- End Blog Area -->
        
      
        <!-- Start Newsletter Area -->
        <section class="newsletter mt-5" id="contact">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title mt-5">
                            <h2 class="text-white">Inversiones DACABE</h2>
                            <b class="text-white"><i class="fa fa-address-card"></i> {{@$user->name}} {{@$user->last_name}}</b>
                            @if (@$seller->email)
                                <p class="text-white">
                                    <i class="fa fa-envelope"></i>
                                    {{$seller->email}}
                                </p>
                            @endif
                            @if (@$seller->phone)
                                <p class="text-white">
                                    <i class="fa fa-phone"></i>
                                    {{$seller->phone}}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
        </section>
        <!-- /End Newsletter Area -->

    

@endsection


@section('js')
    <script>
        
        

    </script>
@endsection

@section('script')

@endsection
