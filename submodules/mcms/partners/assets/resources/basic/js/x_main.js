//меню в сайдбаре
$(".sidebar-nav").each(function(){
	var a=$(this),
		b=$('<div class="shadow"></div>'),
		c=$(".toggle_nav");
	a.prepend(b);

	b.click(function(){
		a.removeClass("full");
		c.toggleClass("active")
	});
	c.click(function(e){
		e.preventDefault();
		a.toggleClass("full");
		c.toggleClass("active")
	});

	$(window).scroll(function(){
		if($(window).width() > 1300) {
			counter = 162 - $(window).scrollTop();
			a.css({
				'padding-top': counter>0?counter:0
			});
		}

	})
});


var links = $('.footer-links');
$('.mobile__contacts span').click(function() {
	$(this).addClass('active').siblings().removeClass('active');
	links.removeClass('vis').eq($(this).data('target')-1).addClass('vis');
});


//Закрашиваем красиво шкалу Прибыль/Безопасность
function fillScale () {
	var allScales = $('.scale__col');
	allScales.children().removeClass();

	var i=1;
	inter = setInterval( function() {

		allScales.each(function(indx, elem) {
			var $this = $(elem),
			color = $this.data('color'),
			count = $this.data('count'),
			scales = $this.children();
			if(count>=i) {
				scales.removeClass('orange-'+(i-1));
			}
			if(i<=count) {

				scales.slice(0,i).addClass(color==1?'orange-'+i:'brown');

			}
		});

		if(i==5) {
			clearInterval(inter);
		}
		i++;
	} , 150)

}


  //Выбираем формат рекламы
  $('body').on('click', '.radio_s li', function() {
  	$(this).siblings('li').removeClass('active');
  	$(this).addClass('active').find('input[type=radio]').prop('checked', true);
  });

  //Простовляем подсказки
  $('[data-toggle="tooltip"]').tooltip({container:'body'});



  //Выделяем содержимое блока по клику в его области
  $('body').on('click', '.selected__text:not(.clipboard)', function() {
  	var e = this;
  	if(window.getSelection){
  		var s=window.getSelection();
  		if(s.setBaseAndExtent){
  			s.setBaseAndExtent(e, 0, e.nextSibling, e.children.length);
  		} else {
  			var r=document.createRange();
  			r.selectNodeContents(e);
  			s.removeAllRanges();
  			s.addRange(r);
  		}
  	} else if(document.getSelection){
  		var s=document.getSelection();
  		var r=document.createRange();
  		r.selectNodeContents(e);
  		s.removeAllRanges();
  		s.addRange(r);
  	} else if(document.selection){
  		var r=document.body.createTextRange();
  		r.moveToElementText(e);
  		r.select();
  	}
  });

  //Для progress и аякс сообщений
  var headerHeight = $('nav').height();
  function progressPosition() {
  	if($(this).scrollTop() > headerHeight) {
  		$('body').addClass('fixed');
  	} else {
  		$('body').removeClass('fixed');
  	}
  };
  progressPosition();

  $(window).scroll(function() {
  	progressPosition();
  });


function setDpDate(id, flag) {
	var value = $('#'+id).val();

	if(!flag) {
		var t_id = id.replace("m_", "");
		var date_arr = value.split('-');
		$('#'+t_id).kvDatepicker("setDate", date_arr[2]+'.'+date_arr[1]+'.'+date_arr[0]);
	} else {
		var date_arr = value.split('.');
		$('#m_'+id).val(date_arr[2]+'-'+date_arr[1]+'-'+date_arr[0]);
	}
}

var $headerNavbarWrapper = $('.header-navbar-wrapper'),
  $headerNavbarLogoWrapper = $('.navbar-header'),
  $headerNavbarUserInfoWrapper = $('.navbar-collapse .navbar-nav'),
  $headerNavbarProfileUser = $('.user-dropdown .dropdown_profile-user');

//Прячем имя юзера если блоки в шапке не помещаются
//Не получается решить на css из-за разной ширины блока с инфой в зависимости от ситуации
function toggleProfileUser() {
	if ($headerNavbarWrapper.outerWidth() - $headerNavbarLogoWrapper.outerWidth() <= $headerNavbarUserInfoWrapper.outerWidth() && !$headerNavbarProfileUser.is(':hidden')) {
		$headerNavbarProfileUser.addClass('hidden');
	} else {
		$headerNavbarProfileUser.removeClass('hidden');
	}
}
toggleProfileUser = debounce(toggleProfileUser);

$(document).ready(function () {
	toggleProfileUser();
});

$(window).on('resize', function () {
	toggleProfileUser();
});

// Вызывает переданную функцию не чаще чем в ms милисекунд
function debounce(f, ms) {
  var timer = null;
  ms = (typeof ms === 'undefined') ? 350 : ms;

  return function () {
    var that = this,
      args = arguments;

    var onComplete = function onComplete() {
      f.apply(that, args);
      timer = null;
    };

    if (timer) {
      clearTimeout(timer);
    }

    timer = setTimeout(onComplete, ms);
  };
}