/* Modal */
(function(){function d(){b.style.height="0";b.style.opacity="0";f("[data-yoobe-modal].show").className-=NaN}function h(){b.style.height="100%";b.style.opacity="1"}var f=function(a){return document.querySelector(a)},e=document.querySelectorAll("[data-show-modal]"),c=document.querySelectorAll("[data-yoobe-modal]"),b=f("body").appendChild(document.createElement("div"));b.setAttribute("id","mbg");b.style.cssText="position: fixed;top: 0;left: 0;right:0;opacity:0;height:0;z-index: 99999;-webkit-transition: opacity .6s ease;transition: opacity .6s ease;";
	b.addEventListener?b.addEventListener("click",d):b.attachEvent("onclick",d);for(var a=c.length;a--;){var k=c[a].offsetWidth,l=c[a].offsetHeight,g=c[a].appendChild(document.createElement("em"));g.setAttribute("class","close");c[a].style.marginLeft=-Math.round(k/2)+"px";c[a].style.marginTop=-Math.round(l/2)+"px";g.addEventListener?g.addEventListener("click",d):g.attachEvent("onclick",d)}for(a=e.length;a--;)e[a].addEventListener?e[a].addEventListener("click",function(){f("[data-yoobe-modal="+this.getAttribute("data-show-modal")+
		"]").className+=" show";h()}):(b.style.background="#000",e[a].attachEvent("onclick",function(a){return function(){f("[data-yoobe-modal="+a.getAttribute("data-show-modal")+"]").className+=" show";h()}}(e[a])));document.onkeydown=function(a){a=a||window.event;27==a.keyCode&&d()}})();

		/* Placeholder */
		(function(p,f,d){function q(b){var a={},c=/^jQuery\d+$/;d.each(b.attributes,function(b,d){d.specified&&!c.test(d.name)&&(a[d.name]=d.value)});return a}function g(b,a){var c=d(this);if(this.value==c.attr("placeholder")&&c.hasClass("placeholder"))if(c.data("placeholder-password")){c=c.hide().next().show().attr("id",c.removeAttr("id").data("placeholder-id"));if(!0===b)return c[0].value=a;c.focus()}else this.value="",c.removeClass("placeholder"),this==f.activeElement&&this.select()}function k(){var b,
			a=d(this),c=this.id;if(""==this.value){if("password"==this.type){if(!a.data("placeholder-textinput")){try{b=a.clone().attr({type:"text"})}catch(e){b=d("<input>").attr(d.extend(q(this),{type:"text"}))}b.removeAttr("name").data({"placeholder-password":a,"placeholder-id":c}).bind("focus.placeholder",g);a.data({"placeholder-textinput":b,"placeholder-id":c}).before(b)}a=a.removeAttr("id").hide().prev().attr("id",c).show()}a.addClass("placeholder");a[0].value=a.attr("placeholder")}else a.removeClass("placeholder")}
			var h="placeholder"in f.createElement("input"),l="placeholder"in f.createElement("textarea"),e=d.fn,m=d.valHooks,n=d.propHooks;h&&l?(e=e.placeholder=function(){return this},e.input=e.textarea=!0):(e=e.placeholder=function(){this.filter((h?"textarea":":input")+"[placeholder]").not(".placeholder").bind({"focus.placeholder":g,"blur.placeholder":k}).data("placeholder-enabled",!0).trigger("blur.placeholder");return this},e.input=h,e.textarea=l,e={get:function(b){var a=d(b),c=a.data("placeholder-password");
				return c?c[0].value:a.data("placeholder-enabled")&&a.hasClass("placeholder")?"":b.value},set:function(b,a){var c=d(b),e=c.data("placeholder-password");if(e)return e[0].value=a;if(!c.data("placeholder-enabled"))return b.value=a;""==a?(b.value=a,b!=f.activeElement&&k.call(b)):c.hasClass("placeholder")?g.call(b,!0,a)||(b.value=a):b.value=a;return c}},h||(m.input=e,n.value=e),l||(m.textarea=e,n.value=e),d(function(){d(f).delegate("form","submit.placeholder",function(){var b=d(".placeholder",this).each(g);
					setTimeout(function(){b.each(k)},10)})}),d(p).bind("beforeunload.placeholder",function(){d(".placeholder").each(function(){this.value=""})}))})(this,document,jQuery);

					/* News */	
					$("header > section > div.news ").each(function(){var b=$(this),a=b.children("ul"),d=a.children("li");if(3<=d.length){for(var c=0;100>c;c++)d.slice(2*c,2*(c+1)).wrapAll("<div />");a.children("div").eq(0).addClass("active");b.append('<span class="prev" style="display:none;">\u0412\u0432\u0435\u0440\u0445</span><span class="next">\u0415\u0449\u0435 \u043d\u043e\u0432\u043e\u0441\u0442\u0438</span>');var e=b.children(".next"),f=b.children(".prev");e.click(function(){a.children("div").eq(-2).is(".active")&&
						$(this).fadeOut();a.children("div").eq(0).is(".active")&&f.fadeIn();a.children("div.active").next("div").addClass("active").prev("div").css({top:"-120px"}).removeClass("active");return!1});f.click(function(){a.children("div").eq(1).is(".active")&&$(this).fadeOut();a.children("div").eq(-2).not(".active")&&e.fadeIn();a.children("div.active").prev("div").addClass("active").next("div").css({top:"60px"}).removeClass("active");return!1})}});

					/* Start */
					$(document).ready(function(){

						$("input, textarea").placeholder();
						$("body").on("click","#showlost",function(){$("#title").text("\u0412\u043e\u0441\u0441\u0442\u0430\u043d\u043e\u0432\u043b\u0435\u043d\u0438\u0435 \u043f\u0430\u0440\u043e\u043b\u044f");$("#showsign").fadeIn();$("#margin").stop().animate({"margin-left":"-370px"},"slow")});
						$("body").on("click","#showsign",function(){$("#title").text("\u0412\u0445\u043e\u0434 \u0432 \u043a\u0430\u0431\u0438\u043d\u0435\u0442");$(this).fadeOut();$("#margin").stop().animate({"margin-left":"0"},"slow")});
						$('body').on('click', '.regado > span', function()  {$('#rega').addClass("show");$('div[data-yoobe-modal=sign]').removeClass("show");}); 
						$('body').on('click', 'div[data-yoobe-modal] span.ok', function()  {$('div[data-yoobe-modal]').removeClass("show");$('#mbg').css({ 'height': 0 + "px", "opacity": "0"});}); 
						$('body').on('click', 'footer > div > section span.regatop', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('body,html').animate({scrollTop:0},200);$('#rega').addClass("show");});
						$('body').on("change","div[data-yoobe-modal] form input",function(){$(this).is(":valid")&&$(this).parent().addClass("valid");$(this).is(":invalid")&&$(this).parent().addClass("invalid");});
						$("div[data-yoobe-modal] form input").focus(function(){$(this).parent().addClass("focus")}).blur(function(){$(this).parent().removeClass("focus")});
						$("body").on("change","#partners-repassword",function(){var a=$("#partners-password"),b=$("#partners-repassword"),c=a.val(),d=b.val();c!=d||6>c.length?(a.parent().removeClass("valid").addClass("has-error"),b.parent().removeClass("valid").addClass("has-error")):(a.parent().addClass("valid").removeClass("has-error"),b.parent().addClass("valid").removeClass("has-error"))});
						$('.alert-danger,.alert-success,.alert-info,.alert-warning').append("<em></em>");
						$("body").click(function(e) { if($(e.target).closest(".alert-danger,.alert-success,.alert-info,.alert-warning").length==0) $(".alert-danger,.alert-success,.alert-info,.alert-warning").css("display","none");});
						$('body').on('click', '.alert-danger > em,.alert-success > em,.alert-info > em,.alert-warning > em', function()  {$(this).parent().fadeOut();}); 
						$('body').on('click', '.resend', function()  {$('#rega').removeClass("show");$('div[data-yoobe-modal=resend]').addClass("show");}); 

						/** Custom scripts */
						$('body').on('click', '.register-modal-button', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('#rega').addClass("show");})
							.on('click', '.login-modal-button', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('[data-yoobe-modal="sign"]').addClass("show");})
							.on('click', '.request-password-modal-button', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('#sign').addClass("show");})
							.on('click', '.js-open-fail', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('#fail-modal').addClass("show");})
							.on('click', '.js-open-success', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('#success-modal').addClass("show");})
							.on('click', '#reset-modal-button', function()  {$('#mbg').css({ 'height': 100 + "%", "opacity": "1"});$('#reset-password-modal').addClass("show");})
							.on('click', '[data-show-modal="countries"]', function()  {$('body,html').animate({scrollTop:0},200);});



						$(".switch-button-old, .switch-button-new").click(function() {
							if (!$(this).hasClass("switch-button-current")) {
								$(".switch-button-current").removeClass("switch-button-current");
								$(this).addClass("switch-button-current");

								var $thisForm = $("#" + $(this).attr('data-login'));

								$thisForm.show();
								$thisForm.siblings("form").hide();
							}
						});
					});

function beforeValidate(form)
{
	$(form).children("button").addClass("load");
	return true;
}

function afterValidate(form, data, hasError)
{
	$(form).children("button").removeClass("load");
	if(hasError) {
		return false;
	} else {
		return true;
	}
}

function submit(form)
{
	if($(form).find('.has-error').length) {
		return false;
	}
	
	$(form).children("button").addClass("load");
	$.ajax({
		url: form.attr('action'),
		type: 'post',
		data: form.serialize(),
		success: function(response, textStatus, jqXHR) {
			$(form).children("button").removeClass("load");
			if(response.success) {
				if(response.success.type == 'html') {
					$(form).parent().html(response.success.html);
					$("#resend").find('#partners-username').val(response.success.username);
				} else if (response.success == 'info') {
					$.each(response.data, function(key, value) {
						$(form).find('.field-' + key).addClass('has-success').find('.help-block').text(value);

					});
				}
			} else {
				$.each(response.errors, function(key, value) {
					if ($.isArray(value) && value.length) {
						$(form).find('.field-' + key).addClass('has-error').find('.help-block').text(value[0]);
					}
				});
			}
		},
		error: function(data, textStatus, errorThrown) {
			$(form).children("button").removeClass("load");
		}
	});
	return false;
}