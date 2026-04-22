@extends('landing.layouts.app')

@section('titulo', trans('site.head.title') )

@section('subtitulo', trans('site.head.subtitle'))

@section('css')
@endsection

@section('content')

   <!-- Slider Area -->
        <section class="slider">
            <div class="hero-slider">
                <!-- Start Single Slider -->
                <div class="single-slider" style="background-image:url('{{asset('landing/img/slider2.jpg')}}'">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="text">
                                    <h1> <span>Producto 1</span> </h1>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed nisl pellentesque, faucibus libero eu, gravida quam. </p>
                                    <div class="button pl-2">
                                        <a href="#" class="btn">Ver más <i class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Single Slider -->
                <!-- Start Single Slider -->
                <div class="single-slider" style="background-image:url('{{asset('landing/img/slider.jpg')}}'">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="text">
                                    <h1><span>Multi Palustra</span></h1>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed nisl pellentesque, faucibus libero eu, gravida quam. </p>
                                    <div class="button">
                                        <a href="#" class="btn">Ver más <i class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start End Slider -->
                <!-- Start Single Slider -->
                <div class="single-slider" style="background-image:url('{{asset('landing/img/slider3_1.jpg')}}'">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="text">
                                    <h1><span>Saco Maxical</span></h1>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sed nisl pellentesque, faucibus libero eu, gravida quam. </p>
                                    <div class="button">
                                        <a href="#" class="btn">Ver más <i class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Single Slider -->
            </div>
        </section>
        <!--/ End Slider Area -->
        
        <!-- Start Schedule Area -->
        <section class="schedule">
            <div class="container">
                <div class="schedule-inner">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-12 ">
                            <!-- single-schedule -->
                            <div class="single-schedule first">
                                <div class="inner">
                                    <div class="icon">
                                        <i class="fa fa-tags"></i>
                                    </div>
                                    <div class="single-content">
                                        <span><i class="fa fa-tags"></i> Variedad</span>
                                        <h4>Amplio Stock de productos</h4>
                                        <p>Lorem ipsum sit amet consectetur adipiscing elit. Vivamus et erat in lacus convallis sodales.</p>
                                        <a href="#">VER MÁS<i class="fa fa-long-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <!-- single-schedule -->
                            <div class="single-schedule middle">
                                <div class="inner">
                                    <div class="icon">
                                        <i class="icofont-prescription"></i>
                                    </div>
                                    <div class="single-content">
                                        <span><i class="fa fa-shopping-cart"></i> Realiza tu pedido</span>
                                        <h4>Con nuestros vendedores</h4>
                                        <p>Lorem ipsum sit amet consectetur adipiscing elit. Vivamus et erat in lacus convallis sodales.</p>
                                        <a href="#">VER MÁS<i class="fa fa-long-arrow-right"></i></a>                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-12 col-12">
                            <!-- single-schedule -->
                            <div class="single-schedule last">
                                <div class="inner">
                                    <div class="icon">
                                        <i class="icofont-ui-clock"></i>
                                    </div>
                                    <div class="single-content">
                                        <span><i class="icofont-ui-clock"></i> A tu tiempo</span>
                                        <h4>El mejor horario</h4>
                                        <p>Lorem ipsum sit amet consectetur adipiscing elit. Vivamus et erat in lacus convallis sodales.</p>
                                        <a href="#">VER MÁS<i class="fa fa-long-arrow-right"></i></a>                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/End Start schedule Area -->

        <!-- Start Feautes -->
        <section class="Feautes section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>Lorem ipsum dolor, sit amet, consectetur adipisicing elit</h2>
                            
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <!-- Start Single features -->
                        <div class="single-features">
                            <div class="signle-icon">
                                <i class="fa fa-phone"></i>
                            </div>
                            <h3>Contactanos</h3>
                            <p>Lorem ipsum sit, consectetur adipiscing elit. Maecenas mi quam vulputate.</p>
                        </div>
                        <!-- End Single features -->
                    </div>
                    <div class="col-lg-4 col-12">
                        <!-- Start Single features -->
                        <div class="single-features">
                            <div class="signle-icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <h3>Solicita productos</h3>
                            <p>Lorem ipsum sit, consectetur adipiscing elit. Maecenas mi quam vulputate.</p>
                        </div>
                        <!-- End Single features -->
                    </div>
                    <div class="col-lg-4 col-12">
                        <!-- Start Single features -->
                        <div class="single-features last">
                            <div class="signle-icon">
                                <i class="fa fa-check"></i>
                            </div>
                            <h3>La mejor atención</h3>
                            <p>Lorem ipsum sit, consectetur adipiscing elit. Maecenas mi quam vulputate.</p>
                        </div>
                        <!-- End Single features -->
                    </div>
                </div>
            </div>
        </section>
        <!--/ End Feautes -->
        
        <!-- Start Fun-facts -->
        <!-- Start portfolio -->
        <section class="portfolio section" >
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2><i class="fa fa-shopping-cart"></i> Más productos</h2>
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="owl-carousel portfolio-slider">
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf1.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf2.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf3.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf4.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf1.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf2.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf3.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                            <div class="single-pf">
                                <img src="{{asset('landing/img/pf4.jpg')}}" alt="#">
                                <a href="#" class="btn">Ver detalles <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--/ End portfolio -->
        
        <!-- Start service -->
        <section class="services section d-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>We Offer Different Services To Improve Your Health</h2>
                            <img src="{{asset('landing/img/section-img.png')}}" alt="#">
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-prescription"></i>
                            <h4><a href="service-details.html">General Treatment</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-tooth"></i>
                            <h4><a href="service-details.html">Teeth Whitening</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-heart-alt"></i>
                            <h4><a href="service-details.html">Heart Surgery</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-listening"></i>
                            <h4><a href="service-details.html">Ear Treatment</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-eye-alt"></i>
                            <h4><a href="service-details.html">Vision Problems</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <!-- Start Single Service -->
                        <div class="single-service">
                            <i class="icofont icofont-blood"></i>
                            <h4><a href="service-details.html">Blood Transfusion</a></h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec luctus dictum eros ut imperdiet. </p> 
                        </div>
                        <!-- End Single Service -->
                    </div>
                </div>
            </div>
        </section>
        <!--/ End service -->
        
        <!-- Pricing Table -->
        <section class="pricing-table section d-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>We Provide You The Best Treatment In Resonable Price</h2>
                            <img src="{{asset('landing/img/section-img.png')}}" alt="#">
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Single Table -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <div class="single-table">
                            <!-- Table Head -->
                            <div class="table-head">
                                <div class="icon">
                                    <i class="icofont icofont-ui-cut"></i>
                                </div>
                                <h4 class="title">Plastic Suggery</h4>
                                <div class="price">
                                    <p class="amount">$199<span>/ Per Visit</span></p>
                                </div>  
                            </div>
                            <!-- Table List -->
                            <ul class="table-list">
                                <li><i class="icofont icofont-ui-check"></i>Lorem ipsum dolor sit</li>
                                <li><i class="icofont icofont-ui-check"></i>Cubitur sollicitudin fentum</li>
                                <li class="cross"><i class="icofont icofont-ui-close"></i>Nullam interdum enim</li>
                                <li class="cross"><i class="icofont icofont-ui-close"></i>Donec ultricies metus</li>
                                <li class="cross"><i class="icofont icofont-ui-close"></i>Pellentesque eget nibh</li>
                            </ul>
                            <div class="table-bottom">
                                <a class="btn" href="#">Book Now</a>
                            </div>
                            <!-- Table Bottom -->
                        </div>
                    </div>
                    <!-- End Single Table-->
                    <!-- Single Table -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <div class="single-table">
                            <!-- Table Head -->
                            <div class="table-head">
                                <div class="icon">
                                    <i class="icofont icofont-tooth"></i>
                                </div>
                                <h4 class="title">Teeth Whitening</h4>
                                <div class="price">
                                    <p class="amount">$299<span>/ Per Visit</span></p>
                                </div>  
                            </div>
                            <!-- Table List -->
                            <ul class="table-list">
                                <li><i class="icofont icofont-ui-check"></i>Lorem ipsum dolor sit</li>
                                <li><i class="icofont icofont-ui-check"></i>Cubitur sollicitudin fentum</li>
                                <li><i class="icofont icofont-ui-check"></i>Nullam interdum enim</li>
                                <li class="cross"><i class="icofont icofont-ui-close"></i>Donec ultricies metus</li>
                                <li class="cross"><i class="icofont icofont-ui-close"></i>Pellentesque eget nibh</li>
                            </ul>
                            <div class="table-bottom">
                                <a class="btn" href="#">Book Now</a>
                            </div>
                            <!-- Table Bottom -->
                        </div>
                    </div>
                    <!-- End Single Table-->
                    <!-- Single Table -->
                    <div class="col-lg-4 col-md-12 col-12">
                        <div class="single-table">
                            <!-- Table Head -->
                            <div class="table-head">
                                <div class="icon">
                                    <i class="icofont-heart-beat"></i>
                                </div>
                                <h4 class="title">Heart Suggery</h4>
                                <div class="price">
                                    <p class="amount">$399<span>/ Per Visit</span></p>
                                </div>  
                            </div>
                            <!-- Table List -->
                            <ul class="table-list">
                                <li><i class="icofont icofont-ui-check"></i>Lorem ipsum dolor sit</li>
                                <li><i class="icofont icofont-ui-check"></i>Cubitur sollicitudin fentum</li>
                                <li><i class="icofont icofont-ui-check"></i>Nullam interdum enim</li>
                                <li><i class="icofont icofont-ui-check"></i>Donec ultricies metus</li>
                                <li><i class="icofont icofont-ui-check"></i>Pellentesque eget nibh</li>
                            </ul>
                            <div class="table-bottom">
                                <a class="btn" href="#">Book Now</a>
                            </div>
                            <!-- Table Bottom -->
                        </div>
                    </div>
                    <!-- End Single Table-->
                </div>  
            </div>  
        </section>  
        <!--/ End Pricing Table -->
        
        
        
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
        
        <!-- Start clients -->
        <div class="clients overlay d-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-12">
                        <div class="owl-carousel clients-slider">
                            <div class="single-clients">
                                <img src="img/client1.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client2.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client3.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client4.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client5.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client1.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client2.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client3.png" alt="#">
                            </div>
                            <div class="single-clients">
                                <img src="img/client4.png" alt="#">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/Ens clients -->
        
        <!-- Start Appointment -->
        <section class="appointment d-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-title">
                            <h2>We Are Always Ready to Help You. Book An Appointment</h2>
                            <img src="img/section-img.png" alt="#">
                            <p>Lorem ipsum dolor sit amet consectetur adipiscing elit praesent aliquet. pretiumts</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12 col-12">
                        <form class="form" action="#">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <input name="name" type="text" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <input name="email" type="email" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <input name="phone" type="text" placeholder="Phone">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <div class="nice-select form-control wide" tabindex="0"><span class="current">Department</span>
                                            <ul class="list">
                                                <li data-value="1" class="option selected ">Department</li>
                                                <li data-value="2" class="option">Cardiac Clinic</li>
                                                <li data-value="3" class="option">Neurology</li>
                                                <li data-value="4" class="option">Dentistry</li>
                                                <li data-value="5" class="option">Gastroenterology</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <div class="nice-select form-control wide" tabindex="0"><span class="current">Doctor</span>
                                            <ul class="list">
                                                <li data-value="1" class="option selected ">Doctor</li>
                                                <li data-value="2" class="option">Dr. Akther Hossain</li>
                                                <li data-value="3" class="option">Dr. Dery Alex</li>
                                                <li data-value="4" class="option">Dr. Jovis Karon</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-12">
                                    <div class="form-group">
                                        <input type="text" placeholder="Date" id="datepicker">
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12 col-12">
                                    <div class="form-group">
                                        <textarea name="message" placeholder="Write Your Message Here....."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-5 col-md-4 col-12">
                                    <div class="form-group">
                                        <div class="button">
                                            <button type="submit" class="btn">Book An Appointment</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7 col-md-8 col-12">
                                    <p>( We will be confirm by an Text Message )</p>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6 col-md-12 ">
                        <div class="appointment-image">
                            <img src="img/contact-img.png" alt="#">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Appointment -->
        
        <!-- Start Newsletter Area -->
        <section class="newsletter section d-none">
            <div class="container">
                <div class="row ">
                    <div class="col-lg-6  col-12">
                        <!-- Start Newsletter Form -->
                        <div class="subscribe-text ">
                            <h6>Sign up for newsletter</h6>
                            <p class="">Cu qui soleat partiendo urbanitas. Eum aperiri indoctum eu,<br> homero alterum.</p>
                        </div>
                        <!-- End Newsletter Form -->
                    </div>
                    <div class="col-lg-6  col-12">
                        <!-- Start Newsletter Form -->
                        <div class="subscribe-form ">
                            <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
                                <input name="EMAIL" placeholder="Your email address" class="common-input" onfocus="this.placeholder = ''"
                                    onblur="this.placeholder = 'Your email address'" required="" type="email">
                                <button class="btn">Subscribe</button>
                            </form>
                        </div>
                        <!-- End Newsletter Form -->
                    </div>
                </div>
            </div>
        </section>
        <!-- /End Newsletter Area -->

    

@endsection


@section('js')
    <script>
        let url = window.location.href;
        let menu = ['nosotros', 'servicios', 'calendario', 'contactanos'];
        

    </script>
@endsection

@section('script')

@endsection
