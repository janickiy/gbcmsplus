var stars = $('.ticket-rate a');
 stars.on('mouseenter', function (e) {
 	stars.removeClass('hover');
 	$(this).addClass('hover').prevAll('a').addClass('hover');
 });
 stars.on('mouseleave', function (e) {
 	stars.removeClass('hover');
 });
