$(function () {

  $('form').keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  });

  var filter_btn = $('.filter_submit');
  var custom_column = $('.custom_col');
  function setPosition () {
    var cols = $('.col-xs-20:visible');
    var count = cols.length-1;
    if(count%5 == 0) {
      custom_column.addClass('right_pos');
    } else {
      custom_column.removeClass('right_pos');
    }
  }
  setPosition ();
  $('body').on('click', '.user_custom-filter', function(e) {
    e.preventDefault();
    var parent_wrap = $(this).parents('.filter-body');

    var parent_li = $(this).parent();
    parent_li.siblings('li').removeClass('active');

    if(parent_li.hasClass('active')) {
      parent_wrap.removeClass('open_settings');
      parent_li.removeClass('active');
      //$(target_id).addClass('hidden');
    } else {
      parent_wrap.addClass('open_settings');
      parent_li.addClass('active');
    }


    setPosition ();
  });

  $('body').on('click', '.filter-list-settings .btn-success', function() {

    var settings_wrap = $(this).parents('.filter-list-settings');
    var target_link = settings_wrap.prev('a');
    var target_id = target_link.attr('href');

    if($(target_id).hasClass('hidden')) {
      $(target_id).removeClass('hidden');
      target_link.parent().addClass('selected');
      $(this).text($(this).data('delete'));
    } else {
      $(target_id).addClass('hidden');
      target_link.parent().removeClass('selected');
      $(this).text($(this).data('add'));
      settings_wrap.find('input').val('').trigger('change');
    }

    $(document).trigger('click');

    var count_filters = $(this).parents('.statistics_collapsed').find('.col-xs-20:visible');
    filter_btn.removeClass (function (index, css) {
      return (css.match(/col-xs-offset-[0-9]+/g) || []).join(' ');
    }).addClass('col-xs-offset-'+(5-count_filters.length%5)*20);
    setPosition ();


  });

  $('body').on('click', '.show_more_filters', function() {
    if($(this).hasClass('active')) {
      $(this).removeClass('active').children('span').text($(this).data('showText')).parents('li').nextAll().addClass('hidden');
    } else {
      $(this).addClass('active').children('span').text($(this).data('hideText')).parents('li').nextAll().removeClass('hidden');
    }
  });

  $('.filter_add').on('eventClose', function() {
    $(this).find('.filter-body').removeClass('open_settings').find('li').removeClass('active');
  });

  $('body').on('change', '.filter-list-settings input', function() {
    var parent = $(this).parents('.filter-list-settings');
    if(parent.prev('a').length !== 0 ) {
      var main_id = parent.prev('a').attr('href');
    } else {
      var main_id = "#" + parent.parents('.col-xs-20').attr('id');
    }
    var inp_1 = parent.find('input[data-index="1"]');
    var inp_2 = parent.find('input[data-index="2"]');

    var str_path_1 = inp_1.val() !== '' ? parseInt(inp_1.val()) || '' : '';
    var str_path_2 = inp_2.val() !== '' ? parseInt(inp_2.val()) || '' : '';

    var curr_val = $(this).val() !== '' ? parseInt($(this).val()) || '' : '';

    if(curr_val == str_path_1 && str_path_2 !== '' && str_path_1 > str_path_2) {
      inp_2.val(str_path_1);
      str_path_2 = str_path_1;
    }
    if(curr_val == str_path_2 && str_path_1 !== '' && str_path_1 > str_path_2) {
      inp_1.val(str_path_2);
      str_path_1 = str_path_2;
    }

    var str = (str_path_1 !== '' ? inp_1.attr('placeholder') + " " + str_path_1 : '') + (str_path_2 !== '' ? " "+inp_2.attr('placeholder').toLowerCase() + " " + str_path_2 : '');

    $(main_id).find('.filter-header span i').text(str !== '' ? '('+str+')' : '');
    var trgt = $(this).parents('.filter-list').length > 0 ? '[data-id="'+$(this).attr('name')+'"]' : '[name="'+$(this).data('id')+'"]';

    var value = parseInt($(this).val()) || '';
    $(this).val(value);
    $(trgt).val(value);
  });

  $('.delete_filter').on('click', function(e) {
    e.stopPropagation();
    var prnt = $(this).parents('.col-xs-20');
    var trgt = $('.user_custom-filter[href = "#'+prnt.attr('id')+'"]').next('.filter-list-settings');
    trgt.find('.btn-success').trigger('click');
  });


});