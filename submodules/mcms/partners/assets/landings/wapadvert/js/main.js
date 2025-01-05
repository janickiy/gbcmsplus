$(function () {
	getMonthName = function(date) {
	    var month = {
	    	ru: ['Января','Февраля','Марта','Апреля','Мая','Июня','Июля','Августа','Сентября','Октября','Ноября','Декабря'],
	    	en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
	    };
	    return month[lang][date.getMonth()];
	}
	getWeekDay = function (date) {
	  	var days = {
	  		ru: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
	  		en: ['Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat']
	  	};
	  	return days[lang][date.getDay()];
	}

	function getRandomInt(min, max) {
	  	return Math.floor(Math.random() * (max - min)) + min;
	}

	var today = new Date;
	var monthWrap = document.getElementsByClassName('r_month')[0];
	var dateWrap = document.getElementsByClassName('js-date-num')[0];
	var timeWrap = document.getElementsByClassName('r_time')[0];
	var timerTop = document.getElementsByClassName('timer_top')[0];
	var dayWrap = document.getElementsByClassName('js-date-day')[0];
	var balancWrap = document.getElementsByClassName('js-balance-num')[0];
	var balance = getRandomInt(600, 1000);
	var addBl = $('.balance__add');
	var balanceExp = $('.balance_exp');
	var lang = $('body').data('lang');

	function updateBalance() {
		
		if(balance >= 20) {
			today.setDate(today.getDate() +1);
			balance -= 20;
		} else {
			clearInterval(updInt);

			balancWrap.parentElement.parentElement.classList.add('red_pulse');
			addBalance (balance);
			return;
		}
		balanceExp.addClass('uk-animation-slide-top uk-animation-reverse');
		setTimeout(function() {
			balanceExp.removeClass('uk-animation-slide-top uk-animation-reverse');
		}, 1500);
		var randTime = getRandomInt(19,33);
		timeWrap.innerHTML = '17:'+randTime;
		timerTop.innerHTML = '17:'+randTime;
		monthWrap.innerHTML = getMonthName(today);
		dayWrap.innerHTML = getWeekDay(today);
		dateWrap.innerHTML = today.getDate();
		balancWrap.innerHTML = balance;
	}
	updateBalance();

	function addBalance (int) {
		var oldBalance = int;
		var newBalance = getRandomInt(600, 1000);
		balance = newBalance + oldBalance;
		var i = oldBalance;
		addBl.find('i').html(newBalance);
		setTimeout(function() {
			addBl.slideDown(300, function() {
				$(balancWrap).parent().parent().removeClass('red_pulse').addClass('green');

				var balanceAnimate = setInterval(function() {

					if(i < newBalance) {
						i++;
						balancWrap.innerHTML = i+oldBalance;
					} else {
						clearInterval(balanceAnimate);
						addBl.fadeOut();	
						updInt = setInterval(function(){
							updateBalance();
							$(balancWrap).parent().parent().removeClass('green');
						}, 3000);
					}
				}, 5);
			});
		}, 2000);
			
		
		
		
	}	
	

	var updInt = setInterval(function() {
					updateBalance();
				}, 3000);
})