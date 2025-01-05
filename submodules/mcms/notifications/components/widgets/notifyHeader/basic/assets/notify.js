$(function () {
  function Notify(notifyHeader) {

    this.$notifyHeader = $(notifyHeader);
    this.$readAllButton = this.$notifyHeader.find('#read-all-button');
    this.$openLink = this.$notifyHeader.children('.notify-header_top');
    this.$notifyHeaderTop = this.$openLink;
    this.$panel = this.$notifyHeader.children('.header-notify__collapse');
    this.$clearButton = this.$panel.find('.clear_notify');
    this.opened = false;
    this.windowWidth = $(window).width();
    this.settingsUrl = this.$notifyHeader.data('settingsUrl');
    this.readAllUrl = this.$notifyHeader.data('readAllUrl');
    this.clearUrl = this.$clearButton.data('url');
    this.initEvents()
  }

  Notify.prototype = {

    initEvents: function () {

      this.$readAllButton.on('click', function() {
        $.getJSON(this.readAllUrl);
      }.bind(this));

      this.$openLink.click(function (e) {
        e.preventDefault();
        this.togglePanel();
        this.$openLink.toggleClass('active');
      }.bind(this));

      this.$panel.find('#notify-settings').click(function (e) {
        location = this.settingsUrl;
      }.bind(this));

      this.$panel.find('#notify-close').click(function () {
        this.hidePanel();
        this.opened = false;
        this.$openLink.removeClass('active');
      }.bind(this));

      $(window).resize(function () {
        this.windowWidth = $(window).width();
        this.setSizePanel();
      }.bind(this));

      this.$clearButton.click(function () {
        $.getJSON(this.clearUrl);

        this.$panel.find('.item').fadeOut(300, function () {
          this.$panel.addClass('empty');
          this.clearPanel();
        }.bind(this));

      }.bind(this));
    },

    togglePanel: function () {
      this.opened ? this.hidePanel() : this.showPanel();
      this.opened = !this.opened;
    },

    clearPanel: function () {
      $('.footer').css('paddingRight', '35px');
      this.setStatusView();
      this.clearCountMessage();
    },

    showPanel: function () {
      $('body')
        .addClass('open-notify')
        .children('div.global, nav, section')
        .css('paddingRight', document.body.clientWidth - this.windowWidth)
      ;
      $('.footer').css('paddingRight', document.body.clientWidth - this.windowWidth + 35);
      this.setSizePanel();
      $('.header-notify__list').scrollator({zIndex: '99'});

      this.$panel.stop().slideDown(300, function () {
        this.$panel.addClass('opened').find('.header-notify__list').scrollTop(0);
      }.bind(this));

      $('body').on('click.notify', function(e) {
        if ($(e.target).closest(this.$notifyHeader).length) return;
        this.hidePanel();
        this.opened = false;
        this.$openLink.removeClass('active');
      }.bind(this));
    },

    hidePanel: function () {
      this.$panel.removeClass('opened');
      this.$panel.stop().slideUp(300, function () {
        $('.header-notify__list').scrollator('destroy');
        $('body').removeClass('open-notify').children('div.global, nav, section').css('paddingRight', 0);
      }.bind(this));
      $('body').off('click.notify');
    },

    setStatusView: function () {
      this.$panel.find('li').removeClass('danger news new');
    },

    clearCountMessage: function () {
      this.$openLink.find('.count_notify').hide();
    },

    setSizePanel: function () {
      this.$panel.width($(window).width() - $('.notify-header').offset().left).height($(window).height() - $('.navbar').height());
    }
  };
  new Notify('.notify-header');
});
