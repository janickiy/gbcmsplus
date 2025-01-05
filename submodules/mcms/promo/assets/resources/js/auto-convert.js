/*
config - объект вида {courses: {usdEur: 1.2, usdRub: 1.2, rubEur: 1.2, rubUsd: 1.2, eurUsd: 1.2, eurRub: 1.2}, rubInputId: 'rub-id', usdInputId: 'usd-id', eurInputId: 'eur-id'}
 */

var autoConvert = function (config) {
  const AC_CLASS_NAME = 'auto-convert-field';
  const DATA_ATTRIBUTE_NAME = 'cur';
  const RUB = 'rub';
  const USD = 'usd';
  const EUR = 'eur';

  // Курсы валют
  var courses = {
    RUB: {USD: config.courses.rubUsd, EUR: config.courses.rubEur},
    USD: {RUB: config.courses.usdRub, EUR: config.courses.usdEur},
    EUR: {RUB: config.courses.eurRub, USD: config.courses.eurUsd}
  };

  // инпуты
  var $inputs = {
    RUB: $('#' + config.rubInputId),
    USD: $('#' + config.usdInputId),
    EUR: $('#' + config.eurInputId)
  };

  for (key in $inputs) {
    $inputs[key].addClass(AC_CLASS_NAME);
    $inputs[key].data(DATA_ATTRIBUTE_NAME, key);
  }

  // Возвращаем селектор поля для навешивания событий
  this.getSelector =  function () {
    return '.' + AC_CLASS_NAME;
  };

  // Установить значение
  this.setResult =  function (from, to) {
    // Если это текущая валюта, пропускаем
    if (from === to) return;

    // Если поле заполнено, пропускаем
    if ($inputs[to].val() !== '') return;

    var result = ($inputs[from].val() * courses[from][to]).toFixed(2);
    $inputs[to].val(result);
  };

  // Конвертируем все незаполненые поля
  this.convertFields = function (t) {
    var value = t.val();
    // Если ввели не число, ничего не делаем
    if (!$.isNumeric(value)) return;

    var currency = t.data(DATA_ATTRIBUTE_NAME);
    for (key in $inputs) {
      this.setResult(currency, key);
    }
  };

  return this;
};
