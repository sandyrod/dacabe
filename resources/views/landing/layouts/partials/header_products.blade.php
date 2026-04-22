<!-- Preloader -->
        <div class="preloader">
            <div class="loader">
                <div class="loader-outter"></div>
                <div class="loader-inner"></div>
            </div>
        </div>
        <!-- End Preloader -->

        <!-- Header Area -->
    <header class="header" >
      <!-- Topbar -->
      <div class="topbar">
        <div class="container">
          <div class="row">
            <div class="col-lg-6 col-md-5 col-12">
              <!-- Contact -->
              <ul class="top-link">
                <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                <li><a href="#"><i class="fa fa-youtube"></i></a></li>
                <li><a href="#"><i class="fa fa-facebook"></i></a></li>
              </ul>
              <!-- End Contact -->
            </div>
            <div class="col-lg-6 col-md-7 col-12">
              <ul class="top-contact">
                <li><i class="fa fa-phone"></i>0412 6202649</li>
                <li><i class="fa fa-envelope"></i><a href="mailto:inversionesdacabeonline@gmail.com">inversionesdacabeonline@gmail.com</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <!-- End Topbar -->
      <!-- Header Inner -->
      <div class="header-inner">
        <div class="container">
          <div class="inner">
            <div class="row text-center">
              <div class="col-lg-3 col-md-3 col-12">
                <!-- Start Logo -->
                <div class="logo">
                  <a href="{{url('dacabe')}}"><img src="{{asset('landing/img/dacabe.png')}}" alt="#"></a>
                </div>
                <!-- End Logo -->
                <!-- Mobile Nav -->
                <div class="mobile-nav"></div>
                <!-- End Mobile Nav -->
              </div>
              <div class="col-lg-1 col-md-2 col-12">
              </div>
              <div class="col-lg-4 col-md-5 col-12">
                <!-- Main Menu -->
                <div class="main-menu">
                  @if (@$categories && sizeof($categories))
                    <nav class="navigation">
                      <ul class="nav menu">
                        <li><a href="#">Categorias <i class="icofont-rounded-down"></i></a>
                          <ul class="dropdown">
                            <li><a href="{{url('catalogo/'.$seller->id)}}">VER TODOS</a></li>
                            @foreach(@$categories as $category)
                              <li><a href="{{url('catalogo/'.$seller->id.'/'.$category->CGRUPO)}}">{{$category->DGRUPO}}</a></li>
                            @endforeach
                            
                          </ul>
                        </li>
                        <li><a href="#contact">Vendedor </a>
                        </li>
                      </ul>
                    </nav>
                  @endif
                </div>
                <!--/ End Main Menu -->
              </div>
              <div class="col-lg-2 col-12">
                <div class="get-quote">
                  <a href="https://wa.me/5804126202649" target="_blank" class="btn"><i class="fa fa-whatsapp fa-2x"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--/ End Header Inner -->
    </header>
    <!-- End Header Area -->