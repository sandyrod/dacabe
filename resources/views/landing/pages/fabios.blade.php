@extends('landing.pages.layouts.app')


@section('css')
@endsection

@section('content')

    <!-- ======= Top Bar ======= -->
  <div id="topbar" class="d-flex align-items-center fixed-top">
    <div class="container d-flex justify-content-center justify-content-md-between">

      <div class="contact-info d-flex align-items-center">
        <i class="bi bi-phone d-flex align-items-center"><span>+58 0412-1551314</span></i>
        <i class="bi bi-clock d-flex align-items-center ms-4"><span> Mar-dom: 11AM - 11PM</span></i>
      </div>

      <div class="languages d-none d-md-flex align-items-center">
        
      </div>
    </div>
  </div>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top d-flex align-items-cente">
    <div class="container-fluid container-xl d-flex align-items-center justify-content-lg-between">

      <h1 class="logo me-auto me-lg-0"><a href="{{url('fabios')}}"></a></h1>
      <!-- Uncomment below if you prefer to use an image logo -->
      <a href="{{url('fabios')}}" class="logo me-auto me-lg-0"><img src="{{asset('theme/restaurant/assets/img/logo.png')}}" alt="" class="img-fluid"></a>

      <nav id="navbar" class="navbar order-last order-lg-0">
        <ul>
          <li><a class="nav-link scrollto active" href="#hero">Inicio</a></li>
          <li><a class="nav-link scrollto" href="#about">Nosotros</a></li>
          <li class="dropdown"><a href="#"><span>Menú</span> <i class="bi bi-chevron-down"></i></a>
            <ul>
              <li><a href="#menu">Burgers y Hotdogs</a></li>
              <li><a href="#menu_postres">Postres y Helados</a></li>
              <li><a href="#menu_desayunos">Desayunos</a></li>
              <li><a href="#specials">Fabios Menú</a></li>
              <li><a href="#promociones">Promociones</a></li>
            </ul>
          </li>
          <li><a class="nav-link scrollto" href="#events">Eventos</a></li>
          <li><a class="nav-link scrollto" href="#gallery">Galeria</a></li>
          <li><a class="nav-link scrollto" href="#footer">Contacto</a></li>
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->
      <a target="_blank" href="https://api.whatsapp.com/send?phone=584121551314" class="book-a-table-btn scrollto d-none d-lg-flex"> Contactar</a>

    </div>
  </header><!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero" class="d-flex align-items-center">
    <div class="container position-relative text-center text-lg-start" data-aos="zoom-in" data-aos-delay="100">
      <div class="row">
        <div class="col-lg-8">
          <h1>Fabios <span>Dolce Caffe</span></h1>
          <h2>+ De 6 años endulzando tu corazón!</h2>

          <div class="btns">
            <a href="#menu" class="btn-menu animated fadeInUp scrollto">Ver Menú</a>
          </div>
        </div>
        <div class="col-lg-4 d-flex align-items-center justify-content-center position-relative" data-aos="zoom-in" data-aos-delay="200">
          <a href="https://www.youtube.com/watch?v=KUrzpNLUsT8" class="glightbox play-btn"></a>
        </div>

      </div>
    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    <section id="about" class="about">
      <div class="container" data-aos="fade-up">

        <div class="row">
          <div class="col-lg-6 order-1 order-lg-2" data-aos="zoom-in" data-aos-delay="100">
            <div class="about-img">
              <img src="{{ asset('theme/restaurant/assets/img/about.jpg') }}" alt="">
            </div>
          </div>
          <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 content">
            <h3>Voluptatem dignissimos provident quasi corporis voluptates sit assumenda.</h3>
            <p class="fst-italic">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
              magna aliqua.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
              <li><i class="bi bi-check-circle"></i> Duis aute irure dolor in reprehenderit in voluptate velit.</li>
              <li><i class="bi bi-check-circle"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate trideta storacalaperda mastiro dolore eu fugiat nulla pariatur.</li>
            </ul>
            <p>
              Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
              velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in
              culpa qui officia deserunt mollit anim id est laborum
            </p>
          </div>
        </div>

      </div>
    </section><!-- End About Section -->

    

    <!-- ======= Menu Section ======= -->
    <section id="menu" class="menu section-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          
          <p>BURGERS - HOTDOGS</p>
        </div>

        <div class="row" data-aos="fade-up" data-aos-delay="100">
          <div class="col-lg-12 d-flex justify-content-center">
            <ul id="menu-flters">
                @foreach($groups as $group)
                    @if ($loop->first)
                        <li data-filter="{{$group['filter']}}" class="filter-active">{{$group['name']}}</li>
                    @else
                        <li data-filter="{{$group['filter']}}">{{$group['name']}}</li>
                    @endif
                @endforeach
            </ul>
          </div>
        </div>

        <div class="row menu-container" data-aos="fade-up" data-aos-delay="200">
            @foreach($menu as $item)
              <div class="col-lg-4 menu-item {{$item['filter']}}">
                <img src="{{ asset('theme/restaurant/assets/img/menu/' . $item['img']) }}" class="menu-img" alt="{{$item['name']}}">
                <div class="menu-content">
                  <a href="#">{{$item['name']}}</a><span>${{$item['price']}}</span>
                </div>
                <div class="menu-ingredients">{{$item['description']}}</div>
              </div>
            @endforeach

        </div>

      </div>
    </section><!-- End Menu Section -->

    <!-- ======= Specials Section ======= -->
    <section id="specials" class="specials">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Especial</h2>
          <p>FABIOS MENÚ</p>
        </div>

        <div class="row" data-aos="fade-up" data-aos-delay="100">
          <div class="col-lg-3">
            <ul class="nav nav-tabs flex-column">
              <li class="nav-item">
                <a class="nav-link active show" data-bs-toggle="tab" href="#tab-1">Milanesa de Pollo a la Plancha</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-2">Milanesa de Pollo Crispy</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-3">Milanesa Premium</a>
              </li>              
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-4">Pídelas con...</a>
              </li>              
            </ul>
          </div>
          <div class="col-lg-9 mt-4 mt-lg-0">
            <div class="tab-content">
              <div class="tab-pane active show" id="tab-1">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>$6.50</h3>
                    <p class="fst-italic">150 gramos de milanesa de pollo, ensalada y papas fritas</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="{{ asset('theme/restaurant/assets/img/specials-1.png') }}" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-2">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>$7.00</h3>
                    <p class="fst-italic">150 gramos de milanesa de pollo, ensalada y papas fritas</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="{{ asset('theme/restaurant/assets/img/specials-2.png') }}" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-3">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>$8.50</h3>
                    <p class="fst-italic">papas fritas y ensalada Cesar</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="{{ asset('theme/restaurant/assets/img/specials-3.png') }}" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="tab-4">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3></h3>
                    <p class="fst-italic">SALSA A LA CAPRESA, AL CHAMPIÑON O CON SALSA DE LA CASA</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="{{ asset('theme/restaurant/assets/img/specials-4.png') }}" alt="" class="img-fluid">
                  </div>
                </div>
              </div>              
            </div>
          </div>
        </div>

      </div>
    </section><!-- End Specials Section -->

    <!-- ======= Events Section ======= -->
    <section id="events" class="events">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Eventos</h2>
          <p>Organiza tus eventos importantes en FABIOS</p>
        </div>

        <div class="events-slider swiper" data-aos="fade-up" data-aos-delay="100">
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="row event-item">
                <div class="col-lg-6">
                  <img src="{{ asset('theme/restaurant/assets/img/event-birthday.jpg') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 content">
                  <h3>Cumpleaños</h3>
                  <div class="price">
                    <p><span>$189</span></p>
                  </div>
                  <p class="fst-italic">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
                    magna aliqua.
                  </p>
                  <ul>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                    <li><i class="bi bi-check-circled"></i> Duis aute irure dolor in reprehenderit in voluptate velit.</li>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                  </ul>
                  <p>
                    Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
                    velit esse cillum dolore eu fugiat nulla pariatur
                  </p>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="row event-item">
                <div class="col-lg-6">
                  <img src="{{ asset('theme/restaurant/assets/img/event-private.jpg') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 content">
                  <h3>Reuniones</h3>
                  <div class="price">
                    <p><span>$290</span></p>
                  </div>
                  <p class="fst-italic">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
                    magna aliqua.
                  </p>
                  <ul>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                    <li><i class="bi bi-check-circled"></i> Duis aute irure dolor in reprehenderit in voluptate velit.</li>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                  </ul>
                  <p>
                    Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
                    velit esse cillum dolore eu fugiat nulla pariatur
                  </p>
                </div>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="row event-item">
                <div class="col-lg-6">
                  <img src="{{ asset('theme/restaurant/assets/img/event-custom.jpg') }}" class="img-fluid" alt="">
                </div>
                <div class="col-lg-6 pt-4 pt-lg-0 content">
                  <h3>Graduaciones</h3>
                  <div class="price">
                    <p><span>$99</span></p>
                  </div>
                  <p class="fst-italic">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
                    magna aliqua.
                  </p>
                  <ul>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                    <li><i class="bi bi-check-circled"></i> Duis aute irure dolor in reprehenderit in voluptate velit.</li>
                    <li><i class="bi bi-check-circled"></i> Ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                  </ul>
                  <p>
                    Ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate
                    velit esse cillum dolore eu fugiat nulla pariatur
                  </p>
                </div>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>
    </section><!-- End Events Section -->

    
    <!-- ======= Gallery Section ======= -->
    <section id="gallery" class="gallery">

      <div class="container" data-aos="fade-up">
        <div class="section-title">
          <h2>Fabios Dolce Caffe</h2>
          <p>Galeria</p>
        </div>
      </div>

      <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-0">

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-1.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-1.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-2.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-2.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-3.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-3.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-4.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-4.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-5.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-5.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-6.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-6.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-7.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-7.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="{{ asset('theme/restaurant/assets/img/gallery/gallery-8.jpg') }}" class="gallery-lightbox" data-gall="gallery-item">
                <img src="{{ asset('theme/restaurant/assets/img/gallery/gallery-8.jpg') }}" alt="" class="img-fluid">
              </a>
            </div>
          </div>

        </div>

      </div>
    </section><!-- End Gallery Section -->

    

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer">
    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-3 col-md-6">
            <div class="footer-info">
              <a href="{{url('fabios')}}" class="logo me-auto me-lg-0"><img width="200" src="{{asset('theme/restaurant/assets/img/logo.png')}}" alt="" class="img-fluid"></a>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Enlaces</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Inicio</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Nosotros</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Eventos</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Galeria</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Menú</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Burgers & Hotdogs</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Postres & Helados</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Desayunos</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Fabios Menú</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#">Promociones</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-newsletter">
            <h4>Contacto</h4>
            <p>
                5ta Av con Av. La Patria y Calle 16.SanFelipe. <br>
                Centro Comercial Valco<br><br>
                <strong>Teléfono:</strong> +58 0412-1551314<br>
                <strong>Email:</strong> contacto@fabios.com<br>
              </p>
              <div class="social-links mt-3">
                <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
              </div>

          </div>

        </div>
      </div>
    </div>

    <div class="container">
      <div class="copyright">
        <strong><span>Fabios Dolce Caffe - 2024</span></strong>.
      </div>
      
    </div>
  </footer><!-- End Footer -->

  <div id="preloader"></div>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  
    

@endsection


@section('js')
    <script>
        let url = window.location.href;        
    </script>
@endsection

@section('script')

@endsection
