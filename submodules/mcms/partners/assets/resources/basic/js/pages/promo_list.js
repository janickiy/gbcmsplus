$(function() {
  // Для работы списка ссылок и источников
  $(document).on('click', '.settings, .code', function(e) {
    e.preventDefault();
    if ($(this).hasClass('ajax-request-in-progress')) return ;
    var $this = $(this),
      action = $this.data('action'),
      source = $this.data('source'),
      active = $this.hasClass('active');

    $('.collapse_tr').find('.collapse-content').slideUp(active ? 300 : 0, function() {
      $('.collapse_tr').remove();
      $('.settings, .code').removeClass('active');
    });

    if (!active) {
      $(this).addClass('ajax-request-in-progress');
      $.ajax({
        url: action,
        type: 'post',
        dataType: 'json',
        data: {
          'source': source
        },
        success: function(result) {
          if (!result.success) {
            notifyInit(null, result.error, false);
            return;
          }

          $this.addClass('active');

          var $template = $("<tr class='collapse_tr'><td colspan='7'></td></tr>");
          $template.find('td').html(result.data.form);

          var container = $this.parents('tr').after($template).next('.collapse_tr').find('.collapse-content');

          if ($this.hasClass('settings')) {
            fillScale ();
            $('[data-toggle="tooltip"]').tooltip({container:'body'});
            $('.selectpicker').selectpicker();
            FilterInit();
          } else {
            new Clipboard('.copy-button');
          }

          container.slideToggle(400, function() {
            /* Скроллим, если открытый бокс не влазит на экран */

            var boxPos = $(this).offset().top + $(this).height() - $(window).height();
            var bodyPos = document.body.scrollTop;

            if (boxPos > bodyPos) {
              $("html, body").animate({scrollTop: boxPos + "px"}, {duration: 200});
            }
          });
        }
      }).always(function() {
        $(this).removeClass('ajax-request-in-progress');
      }.bind(this));
    }
  });

  // Обновление настроек
  $(document).on('click', 'tr.collapse_tr ul.radio_s li', function(e) {
    var checkbox = $(this).find('input');
    var data = {source: checkbox.data('source')};
    data[checkbox.attr('name')] = checkbox.val();

    $.ajax({
      url: checkbox.data('url'),
      type: 'post',
      data: data
    });
  });

  var operatorListState = -1;
  setInterval(function() {
    if (operatorListState === 0) {
      var postbackFormate = $('.postback-formate');
      if (postbackFormate.length === 0) {
        return;
      }

      var operators = [];
      postbackFormate.find('input[name="operators"]:checked').each(function() {
        operators.push($(this).val());
      });

      var data = {
        source: postbackFormate.data('source'),
        operators: operators,
      };

      $.ajax({
        url: postbackFormate.data('url'),
        type: 'post',
        data: data
      });
    }

    operatorListState = operatorListState > -1 ? (operatorListState - 1) : -1;
  }, 100);

  $(document).on('change', '.postback-formate input[type=checkbox]', function() {
    operatorListState = 1;
  });
});
