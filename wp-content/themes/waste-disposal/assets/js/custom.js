jQuery(document).ready(function () {
  var waste_disposal_swiper_testimonials = new Swiper(".testimonial-swiper-slider.mySwiper", {
    slidesPerView: 3,
      spaceBetween: 50,
      speed: 1000,
      autoplay: {
        delay: 3000,
        disableOnPoppinsaction: false,
      },
      navigation: {
        nextEl: ".testimonial-swiper-button-next",
        prevEl: ".testimonial-swiper-button-prev",
      },
      breakpoints: {
        0: {
          slidesPerView: 1,
        },
        767: {
          slidesPerView: 2,
        },
        1023: {
          slidesPerView: 3,
        }
    },
  });
});

jQuery(document).ready(function ($) {
  var waste_disposal_owl = $(".slider-outer.owl-carousel");
  waste_disposal_owl.owlCarousel({
    loop: true,
    items: 1,
    margin: 20,
    autoplayTimeout: 3000,
    speed: 300,
    nav: true,
    dots: false,
    navText: ['<i class="fas fa-arrow-left"></i>','<i class="fas fa-arrow-right"></i>'],
    rtl: false,
    autoplay: true,
  
  });
});

jQuery(document).ready(function ($) {
	var waste_disposal_owl = $('.project-section .owl-carousel');
	waste_disposal_owl.owlCarousel({
	margin: 20,
	nav: true,
	dots: false,
	navText: ['<i class="fas fa-arrow-left"></i>','<i class="fas fa-arrow-right"></i>'],
  loop: true,
	autoplay: true,
	lazyLoad: true,
	responsive: {
    0: 
    { items: 1,
      margin: 0,
     },
    768: { items: 2 },
		1200: { items: 3 },
    1500: { items: 3 },
	}
	});
});