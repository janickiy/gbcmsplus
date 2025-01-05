//переключатель шагов
function Steps(option) {
  this.$option = option;
  this.$activeStep = 1;
  this.scroll = false;
  this.$obj = $('.steps_wrap');
  this.$items = this.$obj.find('.steps>div');
  this.$countSteps = this.$items.length;
  this.$buttons = this.$obj.find('.steps__buttons');
  this.$header = this.$obj.find('.steps_progress');
  this.$buttons = this.$obj.find('.steps__buttons');
  this.$buttonNext = this.$buttons.find('#next__step');
  this.$buttonPrev = this.$buttons.find('#prev__step');
  var self = this;

  this.$buttonPrev.click(function() {
    if (self.$buttonPrev.hasClass('disabled')) {
      event.stopImmediatePropagation();
      return false;
    }
    self.prevStep();
  });

  this.$buttonNext.click(function(event) {
    if (self.$buttonNext.hasClass('disabled')) {
      event.stopImmediatePropagation();
      return false;
    }
    self.disableButtons();
  });
}

Steps.prototype = {
  init: function() {
    if (location.hash !== '') {
      this.scroll = true;
      switch (location.hash) {
        case '#step_2':
          this.$activeStep = 2;
          break;
        case '#step_3':
          this.$activeStep = 3;
          break;
        default:
          this.$activeStep = 1;
      }
      this.setHash();
    }

    this.showStep();
    return this;
  },
  nextStep: function() {
    if (this.$option[this.$activeStep - 1].beforeAction() && this.$activeStep < this.$countSteps) {
      this.$buttonNext.off('click.step' + this.$activeStep);
      this.scroll = true;
      this.hideStep();
      this.$activeStep++;
      this.showStep();
    }

    this.enableButtons();
  },
  prevStep: function() {
    this.$buttonNext.off('click.step' + this.$activeStep);
    this.disableButtons();

    this.scroll = true;
    this.hideStep();
    this.$activeStep--;
    this.showStep();

    this.enableButtons();
  },
  hideStep: function() {
    this.$items.filter('.step__' + this.$activeStep).hide();
  },
  showStep: function() {
    this.showButtonPrev();
    this.setButtonText();
    this.$items.filter('.step__' + this.$activeStep).fadeIn(300);
    this.changeHeader();

    var self = this;
    if (this.$items.filter('.step__' + this.$activeStep).hasClass('step-empty')) {
      $.ajax({
        url: this.$obj.data('step' + this.$activeStep + '-action') + window.location.search,
        type: "POST",
        dataType: "html",
      }).done(function(res) {
        self.$obj.find('.step__' + self.$activeStep).removeClass('step-empty').html(res);
      });
    }

    this.$buttonNext.on('click.step' + this.$activeStep, function () {
      $('#linkStep' + self.$activeStep + 'Form').submit();
    });

    this.$option[this.$activeStep - 1].afterAction(this.$activeStep);

    if (this.scroll) {
      $('html, body').stop().animate({scrollTop: this.$obj.find('.change__step').offset().top}, 500);
    }

  },
  setHash: function() {
    //history.pushState("", document.title, window.location.pathname);
  },
  changeHeader: function() {
    this.$header.removeClass('active');
    this.$header.removeClass('travel').slice(0, this.$activeStep).addClass('travel');
    this.$header.filter('[data-step=' + this.$activeStep + ']').addClass('active');
  },
  getNextIndex: function() {
    return this.$activeStep + 1;
  },
  getPrevIndex: function() {
    return this.$activeStep - 1;
  },
  setButtonText: function() {
    this.$buttonNext.text(this.$option[this.$activeStep - 1].ButtonText);
  },
  showButtonPrev: function() {
    if (this.$activeStep > 1) {
      this.$buttonPrev.removeClass('hidden');
    } else {
      this.$buttonPrev.addClass('hidden');
    }
  },
  disableButtons: function() {
    this.$buttonNext.addClass('disabled loading').prop('disabled', true);
    this.$buttonPrev.addClass('disabled').prop('disabled', true);
  },
  enableButtons: function() {
    this.$buttonNext.removeClass('disabled loading').prop('disabled', false);
    this.$buttonPrev.removeClass('disabled').prop('disabled', false);
  }
};
