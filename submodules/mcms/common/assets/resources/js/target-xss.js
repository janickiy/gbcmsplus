/**
 * Защита от XSS через target="_blank"
 * @see https://habrahabr.ru/post/282880/
 */
$(document).on('click', 'a[target=_blank]', function (e) {
  var $this = $(this);
  var rel = $this.attr('rel');
  rel = typeof rel == 'undefined' ? '' : rel;

  if (rel.search(/(nofollow|noopener)/m) == -1) {
    $this.attr('rel', (rel == '' ? '' : rel + ' ') + 'nofollow noopener');
  }
});