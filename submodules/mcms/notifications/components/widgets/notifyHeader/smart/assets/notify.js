$(function () {

  //нотификации
  function Notify(obj) {
    var $this = this;
    $this.obj = $(obj);
    $this.badge = $($this.obj.data('unread-count-selector'));

    $this.obj.find('#notify-mark-all-as-read').on('click', function (e) {
      $.getJSON($(this).attr('data-read-all-url'))
        .done(function (result) {
          (result.success == true) && $this.badge.text(0);
          $this.obj.find('#notify-refresh').trigger('click');
        }.bind($this))
      ;
      e.preventDefault();
    });

    $this.obj.find('#notify-refresh').on('click', function (e) {
      var pjaxContainer = null;
      if (pjaxContainer = $this.obj.data('pjax-notify-list-container')) {
        $.pjax.reload({container: pjaxContainer, "timeout": false});
      }
      e.preventDefault();
    }.bind($this));
  }
  new Notify('#notification-list');
});


