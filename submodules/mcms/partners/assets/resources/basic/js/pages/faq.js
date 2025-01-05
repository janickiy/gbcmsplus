$(function() {
    NProgress.start();

    $(window).load(function () {
        NProgress.done();
    });

    $('.faq_content a').on('click', function (e) {
        e.preventDefault();
        var target = $(this).attr('href');
        var $navbar = $('.navbar');
        var navbarHeight = $navbar.css('position') === 'fixed' ? $navbar.height() : 0;
        $('html, body').stop().animate({scrollTop: $(target).offset().top - 5 - navbarHeight}, 500);
    });
});