/* Theme Name:iDea - Clean & Powerful Bootstrap Theme
 * Author:HtmlCoder
 * Author URI:http://www.htmlcoder.me
 * Author e-mail:htmlcoder.me@gmail.com
 * Version: 1.3
 * Created:October 2014
 * License URI:http://support.wrapbootstrap.com/
 * File Description: Place here your custom scripts
 */

 $(function () {

 	// Прокрутка и открытие формы
 	$("[data-scroll]").click(function (e) {

 		var elementClick = $(this).attr("data-scroll");
 		var destination = $("#" + elementClick).offset().top;
 		$('html, body').animate({ scrollTop: destination }, 1000);

 		setTimeout(function() {
 			$("#" + elementClick).click();
 			setTimeout(function() {
 				$("#" + elementClick).siblings(".dropdown-menu").find("input").first().focus();
 			}, 100);
 		}, 100);
 	});


 });