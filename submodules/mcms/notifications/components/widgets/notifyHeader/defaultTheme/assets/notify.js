$(function () {

  //нотификации
  function Notify(obj) {
    var $this = this;
    $this.obj = $(obj);
    $this.$openLink = $this.obj.children('a');
    $this.panel = $this.obj.children('.header-notify__collapse');
    $this.opened = false;
    $this.windowWidth = $(window).width();

    $this.$openLink.click(function (e) {
      e.preventDefault();
      $this.togglePanel();
      $(this).toggleClass('active');
    });

    $this.panel.find('#notify-close').click(function () {
      $
        .getJSON($(this).attr('data-read-all-url'))
        .done(function (result) {
          this.hidePanel();
          this.opened = false;
          this.$openLink.removeClass('active');
          if (result.success == true) {
            $('.header-notifications-list .item > li').addClass('is_viewed');
          }
        }.bind($this))
      ;
    });

    $this.panel.find('#notify-clear').click(function () {
      $this.hidePanel();
      $this.opened = false;
      $this.$openLink.removeClass('active');

      var link = $(this).data('url');
      $.ajax({
        url: link,
        type: 'get'
      }).done(function (result) {
        if (result.success == true) {
          $('.header-notifications-list').remove();
        }
      });

    });

    $(window).resize(function () {
      $this.windowWidth = $(this).width();
      $this.setSizePanel();
    });

  }

  Notify.prototype = {
    togglePanel: function () {
      this.opened ? this.hidePanel() : this.showPanel();
      this.opened = !this.opened;
    },
    showPanel: function () {
      if (this.windowWidth >= document.body.clientWidth) {
        $('body').addClass('open-notify').children().css('paddingRight', document.body.clientWidth - this.windowWidth);
      }

      this.setSizePanel();
      this.panel.slideDown(300, function () {
        this.panel.find('.header-notify__list').scrollTop(0);
      }.bind(this));
    },
    hidePanel: function () {

      this.panel.slideUp(300, function () {
        $('body').removeClass('open-notify').children().css('paddingRight', 0);
      });

    },
    setSizePanel: function () {

      this.panel.height($(window).height() - $('.navbar').height());

      if (!this.panel.hasClass('right')) {
        var notifyOffset = $('.header-notify').offset().left
          , width = this.windowWidth >= document.body.clientWidth
            ? $(window).width() - notifyOffset
            : document.body.clientWidth - notifyOffset
          ;

        this.panel.width(width)
      }
    }
  };

  new Notify('.header-notify');
});


