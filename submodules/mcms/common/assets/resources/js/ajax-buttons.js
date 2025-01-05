if (!window.ajaxButtonsBinded) {
  window.ajaxButtonsBinded = true;

  /**
   * Инструкция
   * - добавьте аттрибут data-ajaxable="1" ссылке
   * Готово. Теперь при нажатии на ссылку по указанному адресу будет отправлен post-запрос.
   * Указанный экшен для возвращения результата должен использовать класс \mcms\common\web\AjaxResponse.
   *
   * Дополнительный параметры
   * - confirm-text string Текст конфирма
   * - ajaxable-success callback Колбэк вызываемый при успехе. Например: function(data) { alert(data.data.text); }
   * - ajaxable-reload int Обновленить pjax при успехе. Возможные значения: 1, 0. По умолчанию 1
   * - ajaxable-reload-container string Селектор pjax-контейнера. По умолчанию берется ближайший родительский pjax-контейнер
   * - ajaxable-reload-url URL для получения содержимого pjax. По умолчанию текущий URL
   */
  $(document).on('click', '[data-ajaxable=1]', function (event) {
    event.preventDefault();
    var button = this;
    var $button = $(button);
    if ($button.attr('disabled')) return;

    var confirmText = $button.data('confirm-text');
    var successCallback = $button.data('ajaxable-success');
    var pjax = $button.data('ajaxable-reload') !== 0;
    var pjaxContainer = $button.data('ajaxable-reload-container');
    if (!pjaxContainer) pjaxContainer = $button.closest('[data-pjax-container]');
    var pjaxUrl = $button.data('ajaxable-reload-url');

    var pjaxParams = {container: pjaxContainer, timeout: 3000};
    if (pjaxUrl) pjaxParams.url = pjaxUrl;

    var ok = function () {
      $.post(button.href)
        .done(function (data) {

          if (!data['{{successParam}}']) {
            var failText = data['{{errorParam}}'] ? data['{{errorParam}}'] : '{{failText}}';
            $.smallBox({
              "color": "rgb(196, 106, 105)",
              "timeout" : 4000,
              "title": failText,
              "sound": false,
              "iconSmall": "miniPic fa fa-warning shake animated"
            });
            return;
          }

          $.smallBox({
            "color": "rgb(115, 158, 115)",
            "timeout" : 4000,
            "title": "{{successText}}",
            "sound": false,
            "iconSmall": "miniPic fa fa-check-circle bounce animated"
          });

          if (pjax) {
            if (pjaxContainer && $(pjaxContainer).length !== 0) {
              $.pjax.reload(pjaxParams);
            } else {
              location.reload();
            }
          }

          if (successCallback) eval('(' + successCallback + ')(data)');
        })
        .fail(function () {
          $.smallBox({
            "color": "rgb(196, 106, 105)",
            "timeout" : 4000,
            "title": '{{failText}}',
            "sound": false,
            "iconSmall": "miniPic fa fa-warning shake animated"
          });
        });
    }

    if (confirmText && yii.confirm(confirmText, ok)) return true;
    if (!confirmText) return ok();

  });
}