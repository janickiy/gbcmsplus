function FilterInit() {
	var all_checkbox = $('.checkbox__list .checkbox');
	var selected_country = $('#country').val();
	var visible_group = $('.checkbox__list .checkbox').filter('[data-country="'+selected_country+'"]');
	var checkboxes_group = visible_group.not('.cb_deselect_all, .cb_select_all');
	var selectAll_btn = visible_group.filter('.cb_select_all').find('input');
	var deselectAll_btn = visible_group.filter('.cb_deselect_all').find('input');
	var checkBoxFilter = {

		showCheckbox: function () {
			all_checkbox.hide().filter('[data-country="'+selected_country+'"]').show();
		},

		changeCountry: function(country) {
			selected_country = parseInt(country);
			this.showCheckbox();
			this.setVisibleGroup();
		},

		setVisibleGroup: function() {
			visible_group = $('.checkbox__list .checkbox').filter('[data-country="'+selected_country+'"]');
			checkboxes_group = visible_group.not('.cb_deselect_all, .cb_select_all');
			selectAll_btn = visible_group.filter('.cb_select_all').find('input');
			deselectAll_btn = visible_group.filter('.cb_deselect_all').find('input');
		},

		checkAll: function(el) {
			visible_group.not('.cb_deselect_all').find('input').prop('checked', el.prop('checked'));
			visible_group.filter('.cb_deselect_all').find('input').prop('checked', !el.prop('checked'));
			console.log(el);
		},

		unCheckAll: function(el) {
			visible_group.not('.cb_deselect_all').find('input').prop('checked', !el.prop('checked'));
			selectAll_btn.prop('checked', !el.prop('checked'));
		}
	}

	checkBoxFilter.showCheckbox();

	$('#country').on('change', function() {
		checkBoxFilter.changeCountry($(this).val());
	});

	$('.cb_select_all input').on('change', function() {
		checkBoxFilter.checkAll($(this));
	});

	$('.cb_deselect_all input').on('change', function() {
		checkBoxFilter.unCheckAll($(this));
	});

	all_checkbox.find('input').on('change', function() {
		if(checkboxes_group.find('input:checked').length == checkboxes_group.length) {
			selectAll_btn.prop('checked', true);
			deselectAll_btn.prop('checked', false);
		} else if (checkboxes_group.find('input:checked').length == 0) {
			selectAll_btn.prop('checked', false);
			deselectAll_btn.prop('checked', true);
		} else {
			selectAll_btn.prop('checked', false);
			deselectAll_btn.prop('checked', false);
		}
	});
}