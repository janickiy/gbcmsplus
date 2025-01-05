$(function ()
{
  //Фильтры
  $('.collapse_filters').click(function ()
  {
    var $this = $(this);
    if ($this.hasClass('opened')) {
      $('.statistics_collapsed').slideUp(300, function ()
      {
        $this.removeClass('opened');
      });
    } else {
      $this.addClass('opened');
      $('.statistics_collapsed').slideDown(300);
    }
  });

  var $form = $('#linksListFilter'),
      pjaxSelector = '#linksFormPjax';

  $form.on('submit', function(e) {
    $.pjax.submit(e, pjaxSelector);
  });
});