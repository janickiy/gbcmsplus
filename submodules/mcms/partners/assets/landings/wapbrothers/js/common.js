$(function() {

	//SVG Fallback
	if(!Modernizr.svg) {
		$("img[src*='svg']").attr("src", function() {
			return $(this).attr("src").replace(".svg", ".png");
		});
	};


	//Chrome Smooth Scroll
	try {
		$.browserSelector();
		if($("html").hasClass("chrome")) {
			$.smoothScroll();
		}
	} catch(err) {

	};

	$("img, a").on("dragstart", function(event) { event.preventDefault(); });
	
});

$(window).load(function() {

	$(".loader_inner").fadeOut();
	$(".loader").delay(400).fadeOut("slow");

});

// Sliders offfers
	$(".slider-revievs").owlCarousel({
		items: 1,
		nav: false,
		navText: "",
		loop: true,
		autoplay: false,
		autoplayHoverPause: true,
		fluidSpeed: 1000,
		autoplaySpeed: 600,
		navSpeed: 600,
		dotsSpeed: 600,
		dragEndSpeed: 600,
		autoHeight: true
	});

	var countries = $(".logo-countryes img").length;
	var owl_countryes = $(".logo-countryes");
	owl_countryes.owlCarousel({
		nav: false,
		rewindNav: false,
		navText: "",
		loop: false,
		autoplay: false,
		autoplayHoverPause: true,
		fluidSpeed: 1000,
		autoplaySpeed: 600,
		navSpeed: 600,
		dotsSpeed: 600,
		dragEndSpeed: 600,
		responsiveClass:true,
	    responsive:{
	        0:{
	            items:3
	        },
	        480:{
	            items:6
	        },
	        770:{
	            items:6
	        },
	        1000:{
	            items: countries < 9 ? countries : 9
	        }
	    }
	});
	owl_countryes.on('changed.owl.carousel', function(event) {
		if(event.item.count % event.page.size !== 0 && event.item.index + event.page.size >= event.item.count) {
			$(event.currentTarget).find('.owl-dot').removeClass('active').last().addClass('active');
		}
	});

// Popap form
	$('.popup-with-move-anim').magnificPopup({
		type: 'inline',

		fixedContentPos: false,
		fixedBgPos: true,

		overflowY: 'auto',

		closeBtnInside: true,
		preloader: false,

		midClick: true,
		removalDelay: 300,
		mainClass: 'my-mfp-slide-bottom'
	});

		// Menu Scroll
	$("nav.menu").on("click", "a", function (event) {
		//отменяем стандартную обработку нажатия по ссылке
		event.preventDefault();

		//забираем идентификатор бока с атрибута href
		var id = $(this).attr('href'),

			//узнаем высоту от начала страницы до блока на который ссылается якорь
			top = $(id).offset().top;

		//анимируем переход на расстояние - top за 1000 мс
		$('body,html').animate({
			scrollTop: top - 87
		}, 500);
	});
	// Menu Scroll
	$(".back-top").on("click", "a", function (event) {
		//отменяем стандартную обработку нажатия по ссылке
		event.preventDefault();

		//забираем идентификатор бока с атрибута href
		var id = $(this).attr('href'),

			//узнаем высоту от начала страницы до блока на который ссылается якорь
			top = $(id).offset().top;

		//анимируем переход на расстояние - top за 1000 мс
		$('body,html').animate({
			scrollTop: top - 87
		}, 500);
	});

	// Hover CSS
	$(".btn").addClass("hvr-push");


	// Load Animate
	// $(function(){$(".phone").addClass("wow bounceInDown").attr("data-wow-duration","0.8s").attr("data-wow-delay","0.5s");});
	
	$(function(){$(".an-it-1").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0s");});
	$(function(){$(".an-it-2").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0.4s");});
	$(function(){$(".an-it-3").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0.8s");});
	$(function(){$(".an-it-4").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "1.2s");});
	$(function(){$(".an-it-5").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "1.5s");});
	$(function(){$(".an-it-6").addClass("wow flipInX").attr("data-wow-duration","0.8s").attr("data-wow-delay", "1.9s");});

	$(function(){$(".an2-it-1").addClass("wow zoomIn").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0s");});
	$(function(){$(".an2-it-2").addClass("wow zoomIn").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0.5s");});
	$(function(){$(".an2-it-3").addClass("wow zoomIn").attr("data-wow-duration","0.8s").attr("data-wow-delay", "1s");});
	$(function(){$(".an2-it-4").addClass("wow zoomIn").attr("data-wow-duration","0.8s").attr("data-wow-delay", "1.5s");});

	$(function(){$(".arbitragniki").addClass("wow fadeInLeft").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0.4s");});
	$(function(){$(".webmasters").addClass("wow fadeInRight").attr("data-wow-duration","0.8s").attr("data-wow-delay", "0.4s");});

	$(function(){$(".block-registratoin h2").addClass("wow bounceIn").attr("data-wow-duration","1.5s").attr("data-wow-delay", "0s");});
		// $(function(){$(".forma-wrap").addClass("wow bounceInUp").attr("data-wow-duration","1.5s").attr("data-wow-delay", "0.5s");});


jQuery(document).ready(function($) {
	$('.button-dropdown').on('click', function(event) {
		$('.menu-dropdown').toggle();
	});
});

$(document).click(function(e) {
    if ($('.lang-dropdown').has(e.target).length === 0) {
        $(".menu-dropdown").hide();
    }
});

var wow = new WOW({
	boxClass: 'wow',
	animateClass: 'animated',
	offset: 150,
	mobile: false
});
wow.init();

