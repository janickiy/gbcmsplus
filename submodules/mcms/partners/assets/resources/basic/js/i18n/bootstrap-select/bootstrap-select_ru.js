/*
 * Translated default messages for bootstrap-select.
 * Locale: RU (Russian; Русский)
 * Region: RU (Russian Federation)
 */
(function ($) {
    $.fn.selectpicker.defaults = {
        noneSelectedText: 'Ничего не выбрано',
        noneResultsText: 'Совпадений не найдено {0}',
        countSelectedText: function (numSelected, numTotal) {
            numSelected = Math.abs(numSelected);
            numSelected %= 100;
            if (numSelected >= 5 && numSelected <= 20) {
                return 'Выбрано {0} пунктов';
            }
            numSelected %= 10;
            if (numSelected == 1) {
                return 'Выбран {0} пункт';
            }
            if (numSelected >= 2 && numSelected <= 4) {
                return 'Выбрано {0} пункта';
            }
            return 'Выбрано {0} пунктов';
        },
        maxOptionsText: ['Достигнут предел ({n} {var} максимум)', 'Достигнут предел в группе ({n} {var} максимум)', ['items', 'item']],
        selectAllText: 'Выбрать все',
        deselectAllText: 'Убрать все',
        doneButtonText: 'Закрыть',
        multipleSeparator: ', '
    };
})(jQuery);