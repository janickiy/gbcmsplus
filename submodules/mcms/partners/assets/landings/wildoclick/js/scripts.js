$(document).ready(function() {
  var $_body = $('body');

  // Табы
  var $_tabs_container = $('.tabs-container');

  if ($_tabs_container.length) {
    var $_tabs_headers = $_tabs_container.find('li');
    $_tabs_headers.each(function (index) {
      $(this).data('tab_id', index).attr('data-tab_id', index);
    });

    var $_tabs = $_tabs_container.find('.tab');
    var tabs_height = 0;
    $_tabs.each(function (index) {
      $(this).data('tab_id', index).attr('data-tab_id', index);
      var this_height = parseInt($(this).height());
      if (this_height > tabs_height) {
        tabs_height = this_height;
      }
    });

    $_tabs_container.height(tabs_height);

    setTimeout(function () {
      var tabs_height = 0;
      $_tabs.each(function (index) {
        $(this).data('tab_id', index).attr('data-tab_id', index);
        var this_height = parseInt($(this).height());
        if (this_height > tabs_height) {
          tabs_height = this_height;
        }
      });

      $_tabs_container.height(tabs_height);
    }, 1000);

    tabsInit($_tabs_headers, $_tabs, 0);

    $_tabs_headers.find('a').click(function (e) {
      e.preventDefault();
      tabsInit($_tabs_headers, $_tabs, $(this).parent().data('tab_id'));
    })
  }

  function tabsInit($_tabs_headers, $_tabs, index) {
    $_tabs_headers.not('[data-tab_id=' + index +']').removeClass('active');
    $_tabs_headers.siblings('[data-tab_id=' + index +']').addClass('active');

    $_tabs.not('[data-tab_id=' + index +']').fadeOut(300);
    setTimeout(function () {
      $_tabs.siblings('[data-tab_id=' + index +']').fadeIn(300);
    }, 300);
  }

  // Слайдер
  var $_slider = $('#response-slider');

  function sliderInit() {
    $_slider.bxSlider({
      mode: 'fade',
      auto: true,
      pause: 10000,
      pager: false
    });
  }


  function togleSlides() {
    var windiw_width = $(window).width();
    var slider = $('#response-slider');
    var slides = slider.find('.slide');
    var i;
    var content;


    if (windiw_width >= 1009 && !slider.hasClass('double')) {
      for (i = 0; i < slides.length; i += 2) {
        content = $(slides[i + 1]).find('.slide-wrapper').addClass('right-align');
        $(slides[i]).append(content);
        $(slides[i + 1]).remove();
      }
      slider.addClass('double');
    } else if (windiw_width < 1009 && slider.hasClass('double')) {
      for (i = 0; i < slides.length; i++) {

        content = $(slides[i]).find('.right-align').removeClass('right-align').get();
        $(slides[i]).after($(document.createElement('li')));
        $(slides[i]).next().addClass('slide').append(content);
      }
      slider.removeClass('double');
    }
  }

  togleSlides();

  sliderInit();

  $(window).resize(function () {
    togleSlides();
    $_slider.reloadSlider();
  });

  // Страны
  $('#countries-slider').bxSlider({
    auto: true,
    pause: 5000,
    pager: false,
    slideWidth: 100,
    maxSlides: 6,
    minSlides: 2,
    slideMargin: 20,
    moveSlides: 1
  });

  // Параллакс
  var $_parallax_top = $('#parallax-top'),
      $_parallax_middle = $('#parallax-middle');
  // var $_parallax_bottom = $('#parallax-bottom');

  $_parallax_top.css('top', $_parallax_top.data('start-offset') + 'px');
  $_parallax_middle.css('top', $_parallax_middle.data('start-offset') + 'px');
  // $_parallax_bottom.css('top', $_parallax_bottom.data('start-offset') + 'px');

  $(window).scroll(function () {
    $_parallax_top.css('top', $(window).scrollTop() * 0.4 + $_parallax_top.data('start-offset'));
    $_parallax_middle.css('top', $(window).scrollTop() * 0.4 + $_parallax_middle.data('start-offset'));
    // $_parallax_bottom.css('top', $(window).scrollTop() * 0.4 + $_parallax_bottom.data('start-offset'));
  });

  // Плавная загрузка бэкграунда
  $('.lazy-bg').each(function (i, el) {
    var img_url = $(el).data('background');

    $('<img/>').attr('src', img_url).load(function() {
      $(this).remove();
      $(el).css({
        backgroundImage: 'url("' + img_url + '")',
        opacity: '1'
      });
    });
  });

  // Пауза вращения планет
  $('.planet-active.inner').hover(
      function () {
        $('.inner').addClass('paused');
      },
      function () {
        $('.inner').removeClass('paused');
      }
  );
  $('.planet-active.middle').hover(
      function () {
        $('.middle').addClass('paused');
      },
      function () {
        $('.middle').removeClass('paused');
      }
  );
  $('.planet-active.outer').hover(
      function () {
        $('.outer').addClass('paused');
      },
      function () {
        $('.outer').removeClass('paused');
      }
  );

  // Высота строки с телефонами
  // var $_iphones = $('.how-it-works .iphones');
  //
  // var row_height = 0;
  // $_iphones.find('.col-3').each(function () {
  //   var this_height = parseInt($(this).height());
  //   if (this_height > row_height) {
  //     row_height = this_height;
  //   }
  // });
  //
  // $_iphones.height(row_height);

  // Появление при прокрутке
  setTimeout(function () {
    $('.inview-animate').on('inview', function (e, isInView) {
      if (isInView) {
        $(this).addClass('inview');
      }
    });

    $('.how-it-works .stages').on('inview', function (e, isInView) {
      var $_parent = $(this);
      if (isInView) {
        $(this).find('.col-3').each(function (i) {
          $(this).delay(200 * i).queue(function () {
            $(this).find('img').addClass('inview');
            $(this).find('p').addClass('inview');
          });
        });
        setTimeout(function () {
          $_parent.addClass('inview');
        }, 1000)
      }
    });

    $('.how-it-works .iphones').on('inview', function (e, isInView) {
      // var $_parent = $(this);
      if (isInView) {
        $($(this).find('.col-3').get().reverse()).each(function (i) {
          $(this).delay(200 * i).queue(function () {
            $(this).addClass('inview');
          });
        });
      }
    });

  }, 500);

  // Стилизация select
  $('select').styler();

  // Попапы
  $('.btn-registration').click(function (e) {
    e.preventDefault();
    $_body.addClass('fixed');
    $('#reg-popup-wrapper').fadeIn(300);
  });

  $('.btn-login').click(function (e) {
    e.preventDefault();
    $_body.addClass('fixed');
    $('#login-popup-wrapper').fadeIn(300);
  });

  $('.btn-recovery').click(function (e) {
    e.preventDefault();
    $('.btn-popup-close').trigger('click');
    $_body.addClass('fixed');
    $('#recovery-popup-wrapper').fadeIn(300);
  });

  $('.btn-reset').click(function (e) {
    e.preventDefault();
    $_body.addClass('fixed');
    $('#reset-popup-wrapper').fadeIn(300);
  });

  $('#success-modal-button').click(function (e) {
    e.preventDefault();
    $('.btn-popup-close').trigger('click');
    $_body.addClass('fixed');
    $('#success-modal-wrapper').fadeIn(300);
  });
  $('#fail-modal-button').click(function (e) {
    e.preventDefault();
    $('.btn-popup-close').trigger('click');
    $_body.addClass('fixed');
    $('#fail-modal-wrapper').fadeIn(300);
  });

  $('.btn-popup-close, .btn-message-close').click(function (e) {
    e.preventDefault();
    $(this).parents('.popup-wrapper').fadeOut(300, function () {
      $_body.removeClass('fixed');
    });
  });

   $('.popup-wrapper').mousedown(function (e) {
     if ($(this).is(e.target) && $(this).has(e.target).length == 0) {
       $(this).fadeOut(300, function () {
         $_body.removeClass('fixed');
       });
     }
   });
});