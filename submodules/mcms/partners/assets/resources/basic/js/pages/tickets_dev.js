// Закрываем тикет
$(document).on('click', '.close_ticket > a, .close_ticket_mobile_inner > a', function (e) {
  var confirmText = $(this).data('confirm-text'),
      url = $(this).data('url'),
      pjaxContainer = $(this).data('pjax-update-after'),
      ok = function () {
        $.ajax(url, {
            success: function (data) {
              if (!data.success) return;

              $.pjax.reload("#" + pjaxContainer);
            }
          }
        );
      };

  yii.confirm(confirmText, ok);
});

$(document).on('click', '.ticket-header', function () {
  var $this = $(this);
  var id = $this.data('ticket-id');
  var $ticket = $this.closest('.ticket');

  if ($this.find('.panel-collapse').hasClass('in')) {
    $('.panel-collapse').collapse('hide');
    return true;
  }

  $.ajax({
    url: '/partners/support/messages/',
    data: {
      id: id
    }
  }).done(function (data) {
    $('.panel-collapse').collapse('hide');
    $ticket.find('.panel-body').html(data);
    $ticket.find('.panel-collapse').collapse('show');
    $('html, body').stop().animate({scrollTop: $this.offset().top}, 500);
  });

  var unreadTicket = $ticket.hasClass('has-new');
  if (!unreadTicket) return true;

  $.ajax('/partners/support/read/', {
    data: {
      id: id
    }
  }).done(function(res){
    if(!res.hasOwnProperty('data') || !res.data.hasOwnProperty('count')) return;
    if(res.data.count == 0){
      $('.support .badge').addClass('hidden');
    } else {
      $('.support .badge').removeClass('hidden').html(res.data.count);
    }
  });

});

// Удалить файл при написании сообщения
$(document).on('click', '.delete-file', function (e) {
  var url = $(this).data('url');
  var $attached = $(this).parents().eq(1);

  $attached
    .addClass('hide')
    .prev().show();

  $.ajax(url, {
      success: function (data) {
        if (!data.success) return;

      }
    }
  );

  return false;
});

//открытие тикета по ссылке
$(function() {
  if (location.hash !== '') {
    var ticketId = location.hash.replace(/\#/g, '');
    $('div[data-ticket-id="' + ticketId + '"]').trigger('click');
  }
});

$(document).on('change', 'input[type=file]', function (e) {
    var id = $(this).data('id');
    var reader = new FileReader();
    reader.onload = function (e) {
      $('#ticket-images, #ticket-images' + id).find("img").attr("src", e.target.result);
    };
    reader.readAsDataURL(this.files[0]);
});