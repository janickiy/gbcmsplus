function FilterCheckboxes(obj) {
  this.obj = obj;
  this.header = this.obj.children('span').children('i');
  this.filter = this.obj.siblings('.filter-body');
  this.selectedCheckboxWrap = this.filter.children('.filter-body_selected');
  this.selectedCheckbox = this.selectedCheckboxWrap.find('.checkbox:not(.cb_g)');
  this.deSelectedCheckboxWrap = this.filter.children('.filter-body_deselected');
  this.deSelectedCheckbox = this.deSelectedCheckboxWrap.find('.checkbox:not(.cb_g)');

  this._destruct = function () {
    delete this.obj;
    delete this.filter;
    delete this.selectedCheckboxWrap;
    delete this.deSelectedCheckboxWrap;
  }

}

FilterCheckboxes.prototype = {
  checkAction: function (group) {
    var prop = group.prop('checked');
    var group_id = group.attr('id');

    if (prop) {
      this.selectGroup(group_id);
    } else {
      this.deSelectGroup(group_id);
    }

  },

  checkGroupCountSelected: function () {
    var groups = this.filter.find('.cb_group');
    groups.each(function (indx, el) {
      var count_checked = $(el).find('.vs input:checked').length;
      var count = $(el).find('.vs input').length;
      var prnt = $(el).find('.cb_group-name input');

      if (count_checked !== count) {
        prnt.prop('checked', false);
      } else {
        prnt.prop('checked', true);
      }
    });

  },

  selectGroup: function (group_id) {
    var group = $('#' + group_id).parents('.cb_group');
    var wrap = group.parents('.filter-body_selected').length > 0 ? this.selectedCheckbox : this.deSelectedCheckbox;

    wrap.filter(function (indx, el) {
      if ($(el).hasClass(group_id) && !$(el).hasClass('hd')) {
        return true;
      }
    }.bind(this)).find('input').prop('checked', true);
    this.appendToSelected(group);
    this.updateHeader();
  },

  deSelectGroup: function (group_id) {
    var group = $('#' + group_id).parents('.cb_group');
    var wrap = group.parents('.filter-body_selected').length > 0 ? this.selectedCheckbox : this.deSelectedCheckbox;

    wrap.filter(function (indx, el) {
      if ($(el).hasClass(group_id)) {
        return true;
      }
    }.bind(this)).find('input').prop('checked', false);
    this.appendToDeSelected(group);
    this.updateHeader();
  },

  updateHeader: function () {
    var counter = this.selectedCheckbox.children('input:checked').length
    this.header.html(counter > 0 ? '(' + counter + ')' : '');
  },

  appendToSelected: function (group) {
    if (group.parents('.filter-body_selected').length == 0) {
      this.selectedCheckboxWrap.append(group).show();
      this.selectedCheckbox = this.selectedCheckboxWrap.find('.checkbox:not(.cb_g)');
      this.deSelectedCheckbox = this.deSelectedCheckboxWrap.find('.checkbox:not(.cb_g)');
    }
    this.selectedCheckboxWrap.find('.hidden_text').hide();
    if (this.deSelectedCheckbox.length == 0) {
      this.deSelectedCheckboxWrap.hide().find('.hidden_text').show().next('.form-group').hide();
      this.selectedCheckboxWrap.addClass('cl_bord');
    }
  },

  appendToDeSelected: function (group) {
    if (group.parents('.filter-body_deselected').length == 0) {
      this.deSelectedCheckboxWrap.append(group).show();
      this.deSelectedCheckbox = this.deSelectedCheckboxWrap.find('.checkbox:not(.cb_g)');
      this.selectedCheckbox = this.selectedCheckboxWrap.find('.checkbox:not(.cb_g)');
    }
    this.selectedCheckboxWrap.removeClass('cl_bord');
    this.deSelectedCheckboxWrap.find('.hidden_text').hide();
    if (this.selectedCheckbox.length == 0) {
      this.selectedCheckboxWrap.hide();
    }
  },

  singleSelect: function (el) {
    var parent_group = el.parents('.cb_group');

    if (parent_group.length !== 0) {
      var parent_group_cb = parent_group.find('.cb_group-list input');
      var count_group = parent_group_cb.length;
      var count_group_checked = parent_group_cb.filter(':checked').length;

      var cb_selectAll = parent_group.find('.cb_group-name input');

      if (count_group == count_group_checked) {
        cb_selectAll.prop('checked', true);
      } else {
        cb_selectAll.prop('checked', false);
      }

      if (count_group_checked > 0) {
        this.appendToSelected(parent_group);
      } else {
        this.appendToDeSelected(parent_group);
      }
    } else {
      if (el.prop('checked')) {
        this.appendToSelected(el.parent('.checkbox'));
      } else {
        this.appendToDeSelected(el.parent('.checkbox'));
      }

    }

    this.updateHeader();
  }
};
var active_filter;
$('body').on('click', '.filter-header', function () {
  var parent = $(this).parent();
  $('.filter').not(parent).removeClass('opened');

  if (parent.hasClass('opened')) {
    parent.removeClass('opened');
    active_filter._destruct();
    $(document).off('click.filter');
  } else {
    parent.addClass('opened');

    $(document).on('click.filter', function (event) {
      if ($(event.target).closest(parent).length == 0) {
        if (parent.hasClass('opened')) parent.trigger('eventClose');
        parent.removeClass('opened');
      }
    });

    active_filter = new FilterCheckboxes($(this));
  }

});

var StateDropdown = function (params) {
  var state,
    api = {
      change: function ($cb_group, opened) {
        return state.change($cb_group, opened);
      }
    },
    _state = function (params) {
      this.state = {};
      this.params = {
        filterSelector: '.filter',
        cookieName: 'statisticsFilterState',
        cookieExpire: {expires: 1},
        page: window.location.pathname.replace(/[^a-z;]/g, '')
      };

      $.extend(this.params, params);

      if (typeof Cookies === "undefined") {
        return;
      }

      return this.check();
    };

  _state.prototype.check = function () {
    var state = Cookies.get(this.params.cookieName);
    state = state ? JSON.parse(state) : {};
    if (state && state.hasOwnProperty(this.params.page)) {
      this.set(state[this.params.page]).update();
    } else {
      this.state = state;
      this.set(this.get());
    }
  };
  _state.prototype.get = function () {
    var state = {};
    $('#settings').find(this.params.filterSelector).each(function (index) {
      state[index] = {};
      $(this).find('.cb_group').each(function () {
        var $this = $(this);
        var name = $this.attr('data-l1');
        state[index][name] = {};
        state[index][name]['opened'] = parseInt($this.find('.cb_group-list').attr('data-opened'));
      });
    });

    return state;
  };
  _state.prototype.set = function (state) {
    this.state[this.params.page] = state;
    Cookies.set(this.params.cookieName, this.state);
    return this;
  };
  _state.prototype.update = function () {
    var state = this.state[this.params.page]
      , self = this;
    $('#settings').find(this.params.filterSelector).each(function (index) {
      $(this).find('.cb_group').each(function () {
        if (!state[index] || !state[index][$(this).attr('data-l1')]) {
          return self.set(self.get());
        }
        var opened = state[index][$(this).attr('data-l1')]['opened'] || 0
          , $cb_group_list = $(this).find('.cb_group-list');
        if (opened == 1) {
          $(this).find('.icon-down2').addClass('opened');
          $cb_group_list.css('display', 'block').attr('data-opened', 1);
        } else {
          $(this).find('.icon-down2').addClass('closed');
          $cb_group_list.css('display', '').attr('data-opened', 0);
        }
      });
    });
  };
  _state.prototype.change = function ($cb_group, opened) {
    var state = this.state[this.params.page];
    var index = $cb_group.parents(this.params.filterSelector).parent().index(),
      filter = state[index],
      l1 = $cb_group.attr('data-l1');
    for (var group in filter) {
      if (filter.hasOwnProperty(group)) {
        filter[group]['opened'] = group === l1 ? opened : 0;
      }
    }
    state[index] = filter;

    this.set(state);
  };

  state = new _state(params);

  return api;
};
var State = new StateDropdown();

$('body').on('change.filter', '.cb_g input', function () {
  $('.cb_group-list').not($(this).parents('.cb_group').find('.cb_group-list')).slideUp(300);
  active_filter.checkAction($(this));
});

$('body').on('click', '.cb_group-name > span', function () {
  var $this = $(this),
    $next = $this.parent().next();
  $this.parents('.filter').find('.cb_group-list').not($next).slideUp(300).attr('data-opened', 0);
  var opened = (parseInt($next.attr('data-opened')) == 1) ? 0 : 1;
  if (opened === 1) {
    $(this).next().removeClass('closed').addClass('opened');
  } else {
    $(this).next().removeClass('opened').addClass('closed');
  }
  $next.slideToggle(300).attr('data-opened', opened);
  State.change($this.parents('.cb_group'), opened);
});

$('body').on('change', '.filter .checkbox:not(.cb_g) input', function () {
  active_filter.singleSelect($(this));
});


$(".filter .checkbox:not(.cb_g) input").each(function () {
  var label = $(this).siblings('label');

  if (label.text().length > 11) {
    // TODO Запилить кастомные тултипы, если надо
    label.wrapInner("<span title='" + label.text() + "'></span>");
  }

});

$('body').on('input', '.filter-body_search input', function () {
  var search_text = $(this).val();

  all_checkbox = active_filter.filter.find('.checkbox:not(.cb_g) label');
  $('.cb_group-list').hide(0);
  active_filter.filter.find('.cb_group').eq(0).find('.cb_group-list').show(0);

  $(this).siblings('.reset_search').show();
  all_checkbox.each(function () {
    $(this).parent().hide().removeClass('vs').addClass('hd');
    if ($(this).text().toUpperCase().indexOf(search_text.toUpperCase()) != -1) {
      $(this).parent().show().addClass('vs').removeClass('hd');
    }

    if ($(this).parents('.cb_group').find('.vs').length == 0) {
      $(this).parents('.cb_group').hide();
    } else {
      $(this).parents('.cb_group').show();
    }

    if (search_text.length < 1) {
      $(this).parent().siblings('.hidden_text').hide();
      $(this).parent().show().addClass('vs');
      $('.reset_search').hide();
    }
  });
  active_filter.checkGroupCountSelected();
});
$('body').on('click', '.reset_search', function () {
  $(this).siblings('input').val('').trigger('input');
});