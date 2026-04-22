@extends('landing.layouts.app_products')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css')
@endsection

@section('content')
    <div class="container">
        <div class="row pt-2">
            @foreach(@$sellers as $seller)
             <div class="col-md-4 mt-5">
                <div class="card profile-card-3">
                   <div class="background-block">
                    <div class="pull-left">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate('https://inversionesdacabe.com/catalogo/'.$seller->id) !!}
              </div>
                    <span class="badge badge-warning pull-right mt-2 mr-2 ">{{$seller->codigo}}</span>
                   </div>
                   <div class="profile-thumb-block">
                      <img src="{{getUserPathPhoto($seller->user->photo)}}" alt="{{$seller->user->name}} {{$seller->user->last_name}}" class="profile" />
                   </div>
                   <div class="card-content">
                      <h4>{{$seller->user->name}} {{$seller->user->last_name}}</h4>
                      <small><i class="fa fa-map-marker"></i> {{$seller->zona}}</small>
                      
                         <div class="icon-block">
                         <a href="{{url('catalogo/'.$seller->id)}}" class="btn btn-primary btn-block text-white"> Ver Catálogo <i class="fa fa-arrow-right"></i></a>
                         </div>
                   </div>
                </div>
             </div>
            @endforeach
        </div>
    </div>
          
        
        <!-- Start Blog Area -->
        <section class="blog section d-none" id="blog">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>Keep up with Our Most Recent Medical News.</h2>
                            <img src="{{asset('landing/img/section-img.png')}}" alt="#">
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="{{asset('landing/img/blog1')}}.jpg" alt="#">
                            </div>
                            <div class="news-body">
                                <div class="news-content">
                                    <div class="date">22 Aug, 2020</div>
                                    <h2><a href="blog-single.html">We have annnocuced our new product.</a></h2>
                                    <p class="text">Lorem ipsum dolor a sit ameti, consectetur adipisicing elit, sed do eiusmod tempor incididunt sed do incididunt sed.</p>
                                </div>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="{{asset('landing/img/blog2.jpg')}}" alt="#">
                            </div>
                            <div class="news-body">
                                <div class="news-content">
                                    <div class="date">15 Jul, 2020</div>
                                    <h2><a href="blog-single.html">Top five way for solving teeth problems.</a></h2>
                                    <p class="text">Lorem ipsum dolor a sit ameti, consectetur adipisicing elit, sed do eiusmod tempor incididunt sed do incididunt sed.</p>
                                </div>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Single Blog -->
                        <div class="single-news">
                            <div class="news-head">
                                <img src="img/blog3.jpg" alt="#">
                            </div>
                            <div class="news-body">
                                <div class="news-content">
                                    <div class="date">05 Jan, 2020</div>
                                    <h2><a href="blog-single.html">We provide highly business soliutions.</a></h2>
                                    <p class="text">Lorem ipsum dolor a sit ameti, consectetur adipisicing elit, sed do eiusmod tempor incididunt sed do incididunt sed.</p>
                                </div>
                            </div>
                        </div>
                        <!-- End Single Blog -->
                    </div>
                </div>
            </div>
        </section>
        <!-- End Blog Area -->
        
      
        <!-- Start Newsletter Area -->
        <section class="newsletter mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title mt-5">
                            <h2 class="text-white">Inversiones DACABE</h2>
                            
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
