@extends('landing.layouts.app_products2')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css')
@endsection

@section('content')
        

        <section class="blog section1">
            <div class="container">
                <div class="row pt-4">
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
                        @if(@$product->EUNIDAD && $product->EUNIDAD > 0 && $product->EUNIDAD >= $product->SMIN && $product->BASE1 >= 0)
                            <div class="col-lg-4 col-md-6 col-12 pt-5">
                                <!-- Single Blog -->
                                <div class="single-news">
                                    <div class="news-head">
                                        <img src="{{asset('storage/products/').'/'.@$product->FOTO}}" alt="{{@$product->DESCR}}">
                                    </div>
                                    <div class="news-body">
                                        <div class="news-content">
                                            @php($dacabe_percent = obtenerDescuentoGlobal())
                                            @php($porc = $dacabe_percent>0?$dacabe_percent:35)

                                            <!-- Cedano VALIDAR PRODUCTOS NACIONALES O NO PARA PRECIO2 -->
                                            @php($precio2 = $product->BASE1+(($product->BASE1*$porc/100)))
                                            @php($precio2 = $product->BASE2)
                                            <div class="date">$ {{round($precio2, 2)}}</div>
                                            <h2><a href="#">{{$product->DESCR}}</a></h2>
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
