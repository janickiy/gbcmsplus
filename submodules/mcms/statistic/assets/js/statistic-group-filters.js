var statisticGroupFilters = function () {
  this.$originalGroupFilter = $('.statistic-group-filter');
  this.filtersCount = 1;
  this.filters = {};
  this.removedOptions = {};
  this.$addGroup = $('#add-group');
  this.sourceVal = 'webmasterSources';
  this.linkVal = 'arbitraryLinks';
};

statisticGroupFilters.prototype = {
  init: function () {
    var self = this;
    var name = this.$originalGroupFilter.attr('name');
    if (! /^.*\[\]$/.test(name)) {
      name = name + '[]';
      this.$originalGroupFilter.attr('name', name);
    }
    this.$clonedGroupFilter = this.$originalGroupFilter.clone().removeAttr('id');
    this.filters[this.filtersCount] = this.$originalGroupFilter;
    this.removedOptions[this.filtersCount] = {};
    this.$originalGroupFilter.data('filter-count', this.filtersCount);
    this.$originalGroupFilter.on('change', function () {
      self.removeDuplicateOptions($(this));
    });
    this.addEvents();
    return this;
  },
  
  addEvents: function () {
    var self = this;
    
    this.$addGroup.on('click', function () {
      if (Object.keys(self.filters).length < STATISTIC_MAX_GROUPS) {
        var $newGroupFilter = self.getClonedFilter();
        self.getLastFIlter().after($newGroupFilter);
        self.filters[$newGroupFilter.data('filter-count')] = $newGroupFilter;
        self.showHideButtons();
      }
    });
  },
  
  getClonedFilter: function () {
    var self = this;
    var $filter = this.$clonedGroupFilter.clone();
    var count = ++self.filtersCount;
    var prevSelectedOptions = [];
  
    $filter.data('filter-count', count);
    
    for (var key in this.filters) {
      var $selectedOption = this.filters[key].find(':selected');
      if (!$selectedOption.data('remove')) {
        prevSelectedOptions.push($selectedOption.val());
      }
      
      if ($selectedOption.val() === this.sourceVal) {
        prevSelectedOptions.push(this.linkVal);
      }
  
      if ($selectedOption.val() === this.linkVal) {
        prevSelectedOptions.push(this.sourceVal);
      }
    }
    
    $filter.prepend($('<option>', {
      value: '',
      'data-remove': true,
      text: window.removeGroupFilterLabel
    }));
    
    $filter.find('option').each(function () {
      var $this = $(this);
      var curVal = $this.val();
  
      if (typeof self.removedOptions[count] !== 'object') {
        self.removedOptions[count] = {};
      }
      
      if ($.inArray($this.val(), prevSelectedOptions) !== -1) {
        self.removedOptions[count][curVal] = $this;
        $this.remove();
      }
      
      if ($this.prop('selected')) {
        $this.prop('selected', false);
      }
    });
    
    $filter.on('change', function () {
      self.removeDuplicateOptions($(this));
      if ($(this).find(':selected').data('remove') === true) {
        self.removeGroup($(this));
      }
    });
    
    return $filter;
  },
  
  getLastFIlter: function () {
    return this.filters[Object.keys(this.filters).slice(-1)];
  },
  
  showHideButtons: function () {
    if (Object.keys(this.filters).length > STATISTIC_MAX_GROUPS - 1) {
      this.$addGroup.addClass('hide');
    } else {
      this.$addGroup.removeClass('hide');
    }
  },
  
  removeGroup: function (contenxt) {
    delete this.filters[contenxt.data('filter-count')];
    delete this.removedOptions[contenxt.data('filter-count')];
    contenxt.remove();
    this.showHideButtons();
    $('select.auto_filter').trigger('change');
  },
  
  removeDuplicateOptions: function (context) {
    var curFilterKey = context.data('filter-count');
    var curSelectedVal = context.val();
    var isRemove = context.find(':selected').data('remove');
  
    // Удаляем повторяющиеся опции
    for (var key in this.filters) {
      if (key !== String(curFilterKey)) {
        var $option = this.filters[key].find('option[value="' + curSelectedVal + '"]');
        if (typeof this.removedOptions[key] !== 'object') {
          this.removedOptions[key] = {};
        }
        if (isRemove !== true) {
          this.removedOptions[key][$option.val()] = $option;
          $option.remove();
        }
        
        // Удаляем взаимоисключающие параметры "источники" и "ссылки"
        if (curSelectedVal === this.sourceVal) {
          var $linkOption = this.filters[key].find('option[value="' + this.linkVal + '"]');
          this.removedOptions[key][$linkOption.val()] = $linkOption;
          $linkOption.remove();
        } else if (curSelectedVal === this.linkVal) {
          var $sourceOption = this.filters[key].find('option[value="' + this.sourceVal + '"]');
          this.removedOptions[key][$sourceOption.val()] = $sourceOption;
          $sourceOption.remove();
        }
      }
    }
    
    // Возвращаем удаленные опции, которые не повторяются после изменения селекта
    for (var filter in this.removedOptions) {
      var filtersCount = Object.keys(this.filters).length;
      var removeOptionsCount = Object.keys(this.removedOptions[filter]).length;
      if ((filter !== String(curFilterKey) && removeOptionsCount >= filtersCount) || isRemove) {
        for (var option in this.removedOptions[filter]) {
          if (this.removedOptions[filter][option].val() !== curSelectedVal
            && typeof this.removedOptions[curFilterKey] !== 'undefined'
            && $.inArray(option, Object.keys(this.removedOptions[curFilterKey])) === -1) {
            // Если это взаимоиключающие параметры, то не добавляем
            if ((curSelectedVal === this.sourceVal && option === this.linkVal)
              || (curSelectedVal === this.linkVal && option === this.sourceVal)) {
              continue;
            }
            
            this.filters[filter].append(this.removedOptions[filter][option]);
            delete this.removedOptions[filter][option];
          }
        }
      }
    }
  }
};

$(function () {
  new statisticGroupFilters().init();
});