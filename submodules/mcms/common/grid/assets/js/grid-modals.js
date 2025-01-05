/**
 * @deprecated Скрипт устарел, используйте \yii\bootstrap\Modal для модалок
 * TRICKY Не подключайте этот скрипт к сайту, иначе при использовании виджета \kartik\editable\Editable
 * могут возникнуть проблемы, например после отправки формы вместо обновления модалки будет подгружаться целая страница
 */
var modal = [
  '<div id="gridModal" class="modal fade">',
    '<div class="modal-dialog">',
      '<div class="modal-content"></div>',
    '</div>',
  '</div>'
].join('');

$('body').append(modal);

var $modalContent = $('div#gridModal .modal-content');
var $modalDialog = $('div#gridModal .modal-dialog');

$(document).on('gridmodal:fetch', function() {
  $.ajax({
    url: $modalDialog.data('url'),
    type: "GET",
    success: function(response){
      $modalContent.html(response);
    },
    error: function(jqXHR){
      $modalContent.html(jqXHR.responseText);
    }
  });
});

$(document).on("click", "[data-remote-url]", function (e) {
  e.preventDefault();
  var modalWidth = $(this).data('modal-width')
    , modalMaxWidth = $(this).data('modal-max-width')
    , remoteUrl = $(this).data('remote-url')
    ;

  if (modalWidth !== undefined) $modalDialog.css('width', modalWidth);
  if (modalMaxWidth !== undefined) $modalDialog.css('max-width', modalMaxWidth);

  $modalDialog.data('url', remoteUrl);

  $(document).trigger('gridmodal:fetch');
});

$('#gridModal').on('hidden.bs.modal', function (e) {
  $modalContent.html('');
});