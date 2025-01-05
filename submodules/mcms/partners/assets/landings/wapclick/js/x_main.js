
// Detect if the browser is IE or not.
// If it is not IE, we assume that the browser is NS.
var IE = document.all?true:false

// If NS -- that is, !IE -- then set up for mouse capture
if (!IE) document.captureEvents(Event.MOUSEMOVE)

// Set-up to use getMouseXY function onMouseMove
document.getElementsByClassName('header')[0].onmousemove = getMouseXY;

// Temporary variables to hold mouse x-y pos.s
var tempX = 0;
var tempY = 0;

var objectArray = new Array();





var objectArray = {
	blg : [
		[".bee_money_1", 580, -60, 0.05, 'margin-left', 'top', 'px'],
		[".bee_money_2", 380, 15, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_3", 540, 125, 0.007, 'margin-left', 'top', 'px'],
		[".bee_money_4", -550, 30, 0.03, 'margin-left', 'top', 'px'],
		[".bee_money_5", -790, 10, 0.009, 'margin-left', 'top', 'px'],
		[".bee_money_6", -690, 210, 0.005, 'margin-left', 'top', 'px'],
		[".header-bg", 50, 330, 0.002, 'background-position-x', '', '%'],
	],
	lg : [
		[".bee_money_1", 400, -40, 0.05, 'margin-left', 'top', 'px'],
		[".bee_money_2", 220, 45, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_3", 440, 125, 0.007, 'margin-left', 'top', 'px'],
		[".bee_money_4", -440, 30, 0.03, 'margin-left', 'top', 'px'],
		[".bee_money_5", -640, 10, 0.009, 'margin-left', 'top', 'px'],
		[".bee_money_6", -590, 210, 0.004, 'margin-left', 'top', 'px'],
		[".header-bg", 50, 330, 0.002, 'background-position-x', '', '%'],
	],
	md : [
		[".bee_money_1", 400, -40, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_2", 220, 45, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_3", 440, 125, 0.002, 'margin-left', 'top', 'px'],
		[".bee_money_4", -440, 30, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_5", -540, 10, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_6", -490, 210, 0.002, 'margin-left', 'top', 'px'],
		[".header-bg", 50, 330, 0.002, 'background-position-x', '', '%'],
	],
	sm : [
		[".bee_money_1", 300, -40, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_2", 220, 45, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_3", 440, 125, 0.002, 'margin-left', 'top', 'px'],
		[".bee_money_4", -380, 30, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_5", -440, 10, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_6", -330, 270, 0.002, 'margin-left', 'top', 'px'],
		[".header-bg", 50, 330, 0.002, 'background-position-x', '', '%'],
	],
	xs : [
		[".bee_money_1", 300, -40, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_2", 220, 45, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_3", 440, 125, 0.002, 'margin-left', 'top', 'px'],
		[".bee_money_4", -220, 130, 0.01, 'margin-left', 'top', 'px'],
		[".bee_money_5", -440, 10, 0.005, 'margin-left', 'top', 'px'],
		[".bee_money_6", -330, 270, 0.002, 'margin-left', 'top', 'px'],
		[".header-bg", 50, 330, 0.002, 'background-position-x', '', '%'],
	]
};

var setBrakepoint  = function() {
	var ww = $(window).width();
	return ww > 1600 ? 'blg' : ww > 1170 ? 'lg' : ww > 1000 ? 'md' : ww > 767 ? 'sm' : 'xs';
}
var brakepoint = setBrakepoint();



// Main function to retrieve mouse x-y pos.s

function getMouseXY(e)
{
	if (IE)
	{
		// grab the x-y pos.s if browser is IE
		tempX = event.clientX + document.body.scrollLeft
		tempY = event.clientY + document.body.scrollTop
	}
	else
	{
		// grab the x-y pos.s if browser is NS
		tempX = e.pageX
		tempY = e.pageY
	}
	// catch possible negative values in NS4
	if (tempX < 0){tempX = 0}
	if (tempY < 0){tempY = 0}

	moveDiv(tempX, tempY);

	return true
}
var wind_w = $(window).width();
var wind_h = $(window).height();
function moveDiv(tempX, tempY)
{
	var obj = objectArray[brakepoint];


	for (var i=0;i<obj.length;i++)
	{
		test = {};
		var yourDivPositionX = obj[i][3] * (0.5 * wind_w - tempX) + obj[i][1];
		var yourDivPositionY = obj[i][3] * (0.9 * wind_h - tempY) + obj[i][2];

		test[obj[i][4]] = yourDivPositionX + obj[i][6];

		if(obj[i][5] !== '') {
			test[obj[i][5]] = yourDivPositionY + obj[i][6];
		}

		$(obj[i][0]).css(test);
	}
}

function positionDivs()
{
	var obj = objectArray[brakepoint];

	for (var i=0;i<obj.length;i++)
	{
		test = {};
		test[obj[i][4]] = obj[i][1] + obj[i][6];

		$(obj[i][0]).css(test);

	}
}


positionDivs();

$(window).on('resize', function() {
	brakepoint = setBrakepoint();
	positionDivs();
});




/* Показать еще */
var in_box = $('.load_news');
//Demo
var demo = in_box.children('.row');
var indx = 1;
$('.show_more a').on('click', function(e) {
	e.preventDefault();
	if($(this).hasClass('disabled')) return false;
	$(this).addClass('loading');

	setTimeout(function() {
		demo.eq(indx).fadeIn(300);
		indx++;
		if(indx >= demo.length) {
			$(this).addClass('disabled');
		}
		$(this).removeClass('loading');
	}.bind(this), 100);

});


/* Выдвигаем панельку под медведем */
/* Показываем цитату медведя */
$(window).on('load', function() {
	$('.header_bottom').animate({'bottom' : '0'}, 400);

	setTimeout(function() {
		$('.quote').addClass('animated fadeInUp')
	}, 1000);

	//Закрузка картинок
	$('.lazy').each(function() {
		$(this).attr('src', $(this).data('src'));
	});
});



//Селекты
$('select').fancySelect();


//Фикс показа ошибки
$('form').on('afterValidateAttribute', function (e) {
	$(e.target).find('.has-error .help-block').show();
});

$('.help-block').hover(function() {
	$(this).hide();
});

$('.change-modal').on('click', function(e) {
	e.preventDefault();
	$('.modal').modal('hide');
	var modal = $(this).data('modal');
	setTimeout(function() {
		$('#'+modal).modal('show');
	}, 600);
});


//Окошко
$('.toggle_popunder').on('click', function() {
	var pop = $('.popunder');
	var $this = $(this);
	pop.toggleClass('opened');

	if(pop.hasClass('opened')) {
		$(document).on('click.popunder', function(e) {
			if(!$(e.target).hasClass('toggle_popunder')) {
				$('.popunder .close').trigger('click');
			}
		});
	} else {
		$(document).off('click.popunder');
	}
});

$('.popunder .close').on('click', function(e) {
	e.preventDefault();
	$(this).parent().removeClass('opened');
	$(document).off('click.popunder');
});

$('.scroll_up a').on('click', function(e) {
	e.preventDefault();
	$('html, body').animate({scrollTop: 0},500);
});


//Анимация при скролле
if($(window).width() > 767) {
	$('.scroll').viewportChecker({
		offset: 160
	});
}

var nav = $('.nav');
$(window).on('scroll resize', function() {
	if($(this).scrollTop() > 200 && $(this).width() > 1000) {
		nav.addClass('fixed');
	} else {
		nav.removeClass('fixed');
	}
});

$('.scroll_to_box').on('click', function(e) {
	e.preventDefault();
	var target = $(this).attr('href');
	var add = nav.hasClass('fixed') ? 64 : 0;
	$('html, body').animate({scrollTop: $(target).offset().top - add },500);
});

$('.toggle').on('click', function () {
	$(this).toggleClass('on');

	if($(this).hasClass('on')) {
		$('.new_panel').hide();
		$('.old_panel').show();
	} else {
		$('.old_panel').hide();
		$('.new_panel').show();
	}
});
$('[data-toggle="tooltip"]').tooltip({
	html: true
});

//Ротатор
var myCanvas = document.getElementById("cycle"),
	context = myCanvas.getContext("2d"),
	timeLimit = 10000,
	timeStart = (new Date).getTime(),
	canvasSize = 16,
	lineWidth = 2,
	timer,
	drawX = drawY = radius = canvasSize / 2;
radius -= lineWidth / 2;
myCanvas.width = canvasSize;
myCanvas.height = canvasSize;

function go() {
	context.beginPath();
	context.lineWidth = lineWidth;
	context.lineCap = "round";
	context.strokeStyle = "rgb(205,205,205)";
	var a = ((new Date).getTime() - timeStart) / timeLimit;
	context.clearRect(0, 0, canvasSize, canvasSize);

	context.arc(drawX, drawY, radius, -Math.PI / 2 + 2 * Math.PI * a, -Math.PI / 2, !1);
	context.stroke();
	if(1 < a) {
		clearTimeout(timer);
	} else {
		timer = window.setTimeout(go, 50)
	}
}

function Wiki() {
	var items;
	this.init = function() {
		items = $('.wiki_rotate-item');
		var activeIndex = 0;
		go();
		setInterval(function() {
			activeIndex = activeIndex+1 == items.length ? 0 : activeIndex+1;
			items.hide().eq(activeIndex).fadeIn(300);
			timeStart = (new Date).getTime();
			go();
		}, 10000);
	}
};

new Wiki().init();
