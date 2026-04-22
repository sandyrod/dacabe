jQuery(document).ready(function($) {
	"use strict"
	// main-Slider
	if ($('#slider1').length > 0) {
		$('#slider1').sliderPro({
			forceSize: 'fullWindow',
			height: 600,
			arrows: true,
			buttons: false,
			autoplay: false,
			autoScaleLayers: false,
			waitForLayers: false,
			fade: true,
			fadeArrows: true
		});
	}
	  // Topbar-Toggle

	  $(".topbar-info").click(function(){
      $(".topbar-toggle").css('margin-right' , '0');
      // $(this).hide();
	 });
	 
	 $(".topbar-close").click(function(){
		$(".topbar-toggle").css('margin-right' , '-320px');
		$(".topbar-info").show();
	});


	// header-contact-form-slider
	if ($('.header-form-wrap').length > 0){
		$('.header-form-wrap').vegas({
		    slides: [
		        { src: "img/slider/slider1.jpg" },
		        { src: "img/slider/slider2.jpg" },
		        { src: "img/slider/slider3.jpg" }
		    ],
		      transition: 'fade',
		});
	}

	// imgLiquid
	$('.imgLiquid').imgLiquid({
		verticalAlign: 'bottom'
	});
	if ($(window).width() > 500) {
		$('.skillbar').barGraph({
			unit: 10,
			total: 100
		});
	}
	else {
		$('.skillbar').barGraph({
			unit: 25,
			total: 100
		});
	}

	// Sectionize-Control
	if ($('select').length > 0) {
		$('select').selectize({
			create: true,
			sortField: {
				field: 'text',
				direction: 'asc'
			},
			dropdownParent: 'body'
		});
	}

	// counterUp
	if ($('.counter').length > 0) {
		$('.counter').counterUp({
			delay: 10,
			time: 1500
		});
	}

	// Scroll-to-Top
	if ($('.back-to-top').length > 0) {
		$('.back-to-top').click(function() {
			$('html, body').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	}
	
	// smooth page Scroll
   $('.cssmenu a[href^="#"], a.top[href^="#"], a.smooth[href^="#"]').on("click", function(event) {
     event.preventDefault();
     if( this.hash != '' ){
     	$('html,body').animate({
	     scrollTop: $(this.hash).offset().top - 50},
	     1000); 
     }

     
     
   });

	// magnificPopup
	$('.casestudies-single-gallery, .case-studies, .casestudies-area').magnificPopup({
		delegate: '.zoom-view',
		type: 'image',
		mainClass: 'mfp-with-zoom mfp-img-mobile',
		image: {
			verticalFit: true,
			titleSrc: function(item) {
				return item.el.attr('title') + ' &middot; <a class="image-source-link" href="' + item.el.attr('data-source') + '" target="_blank">image source</a>';
			}
		},
		gallery: {
			enabled: true
		},
		zoom: {
			enabled: true,
			easing: 'ease-in-out',
			duration: 300,
			opener: function(element) {
				return element.closest('.casestudies-gallery-item, .case-studies-img-holder, .single-item, .casestudies-single-post').find('img');
			}
		}
	});

	// casestudies-single-post
	$('.caseStudies-post-play').magnificPopup({
		disableOn: 700,
		type: 'iframe',
		mainClass: 'mfp-fade',
		removalDelay: 160,
		preloader: false,
		fixedContentPos: false
	});

	// casestudies-single-gallery
	$('.widget-recent-posts').magnificPopup({
		delegate: 'a',
		type: 'image',
		mainClass: 'mfp-with-zoom mfp-img-mobile',
		image: {
			verticalFit: true,
		},
		gallery: {
			enabled: true
		},
		zoom: {
			enabled: true,
			easing: 'ease-in-out',
			duration: 300,
			opener: function(element) {
				return element.find('img');
			}
		}
	});

	// our-letest-blog
	if ($('.blog-post-list').length > 0) {
		$('.blog-post-list').owlCarousel({
			nav: true,
			navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
			loop: true,
			margin: 45,
			dots: false,
			responsive: {
				300: {
					items: 1,
					nav: false,
					dots: true,
				},
				768: {
					items: 2,
					nav: false,
					dots: true,
				},
				1000: {
					items: 3,
					nav: true
				}
			}
		});
	}

	$('.tabs a').click(function (e) {
		  e.preventDefault()
		  $(this).tab('show')
		})

	// our-letest-blog
	if ($('.resource-slide').length > 0) {
		$('.resource-slide').owlCarousel({
			items: 1,
			loop: true,
			dots: false,
			nav: true,
			navText: ['<i class="pe-7s-left-arrow"></i>', '<i class="pe-7s-right-arrow"></i>'],
			loop: true
		});
	}

	// testimonial-carousel-1
	if ($('.testimonial-carousel').length > 0) {
		$('.testimonial-carousel').each(function() {
			var autoplay = $(this).data('autoplay');
			$(this).owlCarousel({
				animateOut: 'fadeOutUp',
				animateIn: 'fadeInUp',
				items: 1,
				nav: true,
				navText: ['<i class="fa fa-chevron-up"></i>', '<i class="fa fa-chevron-down"></i>'],
				loop: true,
				dots: true,
				autoplay: autoplay,
				autoplayHoverPause: true
			});
		});
	}

	// testimonial-carousel-2
	if ($('.testimonial-carousel-2').length > 0) {
		$('.testimonial-carousel-2').each(function() {
			var autoplay = $(this).data('autoplay');
			$(this).owlCarousel({
				animateOut: 'fadeOutUp',
				animateIn: 'fadeInUp',
				items: 1,
				loop: true,
				dots: true,
				autoplay: autoplay,
				autoplayHoverPause: true
			});
		});
	}

	// about-our-team
	if ($('.about-our-team').length > 0) {
		$('.about-our-team').owlCarousel({
			loop: true,
			nav: false,
			control: false,
			margin: 30,
			dots: false,
			responsive: {
				300: {
					items: 1,
					dots: true,
				},
				640: {
					items: 2,
					dots: true,
				},
				1000: {
					items: 4
				}
			}
		});
	}

	// Brand-Area
	if ($('.brand-list').length > 0) {
		$('.brand-list').owlCarousel({
			loop: true,
			dots: false,
			responsive: {
				300: {
					items: 1,
					dots: true,
				},
				480: {
					items: 2,
					dots: true,
				},
				768: {
					items: 3,
				},
				800: {
					items: 3,
				},
				1000: {
					items: 5,
				}
			}
		});
	}

	// client-say-style-3
	if ($('.client-list-style-3').length > 0) {
		$('.client-list-style-3').owlCarousel({
			navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
			loop: true,
			dots: false,
			margin: 40,
			responsive: {
				300: {
					items: 1,
					nav: false,
					dots: true,
				},
				768: {
					items: 2,
					nav: false,
					dots: true,
				},
				1000: {
					items: 3,
					nav: true
				}
			}
		});
	}


	// working-formulas
	if ($('.working-formulas').length > 0) {
		var $this = $('.working-formulas');
		$this.height($this.innerWidth());
		$('.formula').each(function() {
			var pos = $(this).position();
			if ($(this).hasClass('formula-position-top')) {
				var top = pos.top - $(this).height() / 2;
				$(this).css({
					top: top
				});
			}
			if ($(this).hasClass('formula-position-left') || $(this).hasClass('formula-position-right')) {
				var top = -$(this).height() / 2;
				$(this).css({
					marginTop: top
				});
			}
			if ($(this).hasClass('formula-position-bottom')) {
				var top = pos.top - $(this).height() / 2;
				$(this).css({
					bottom: top
				});
			}
			$(this).css({
				'position': 'absolute'
			});
		})
	}

	// case-studies-isotop
	var $container = $('.caseStudies-container');
	$container.isotope({
		filter: '*',
		animationOptions: {
			duration: 750,
			easing: 'linear',
			queue: false,
			masonry: {
				columnWidth: 50,
				gutter: 100
			}
		}
	});	
	$('.caseStudies-filter li').click(function() {
		$('.caseStudies-filter .current').removeClass('current');
		$(this).addClass('current');
		var selector = $(this).attr('data-filter');
		$container.isotope({
			filter: selector,
			animationOptions: {
				duration: 750,
				easing: 'linear',
				queue: false
			}
		});
		return false;
	});

	// google-map
	if ($('#google-map').length > 0) {
		// This example adds a marker to indicate the position of Bondi Beach in Sydney,
		var pos = {
			lat: 40.735657,
			lng: -74.172367
		};
		var map = new google.maps.Map(document.getElementById('google-map'), {
			zoom: 12,
			center: pos,
			scrollwheel: false
		});
		var image = 'img/map-marker.png';
		var beachMarker = new google.maps.Marker({
			position: pos,
			map: map,
			icon: image
		});
		map.set('styles', [
			{
				featureType: 'Greyscale',
				stylers: [
					{
						saturation: -90
			},
					{
						gamma: 1.5
			}]
		}]);
	}

	// Animation
	$(window).scroll(function() {
		$('.animate').each(function() {
			var imagePos = $(this).offset().top;
			var topOfWindow = $(window).scrollTop();
			if (imagePos < topOfWindow + 400) {
				$(this).addClass($(this).data('animation')).addClass('animated');
			}
		});
		var shHeight = $('.topbar').height()+$('.sticky-header').height();
		var topOfWindow = $(window).scrollTop();
		if (shHeight < topOfWindow) {
			$('.sticky-header').addClass('sticky-navbar nav-style-dark fadeInDown animated');
			$('header').css({
				paddingTop: $('.sticky-header').height()
			});
		}
		else {
			$('.sticky-header').removeClass('sticky-navbar nav-style-dark fadeInDown animated');
			$('header').css({
				paddingTop: 0
			});
		}
	});

	// Animation WOW JS 
	 var e = new WOW({
	      boxClass: "wow",
	      animateClass: "animated",
	      offset: 100,
	      mobile: false,
	      live: !0,
	      callback: function(e) {}
	  });
	  e.init();





});
// End Ready Function