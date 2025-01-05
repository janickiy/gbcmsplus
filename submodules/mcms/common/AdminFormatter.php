<?php

namespace mcms\common;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use Yii;
use yii\db\ActiveRecord;

/**
 */
class AdminFormatter extends \rgk\utils\components\i18n\Formatter
{
  /**
   * @var int
   */
  public $decimals;

  /**
   * @inheritdoc
   */
  public $icons = [
    'rub' => '₽',
    'usd' => '$',
    'eur' => '€',
  ];

  /**
   * Цена на ленде
   * @param $value
   * @param null $currency
   * @param array $options
   * @param array $textOptions
   * @return string
   */
  public function asLandingPrice($value, $currency = null, array $options = [], array $textOptions = [])
  {
    return $this->asMagicDecimalsPrice($value, $currency, $options, $textOptions);
  }

  /**
   * Магическая цена.
   * Показывает 3 знака после запятой, если у значения есть этот знак. Если нету, то 2 знака всегда, даже в целых.
   * Примеры:
   * 0.2241 => 0,224
   * 0.2249 => 0,225
   * 0.223 => 0,223
   * 0.220 => 0,22
   * 0.2 => 0,20
   * 2000.0 => 2 000,00
   * 20000000.0 => 20 000 000,00
   *
   * @param $value
   * @param null $currency
   * @param array $options
   * @param array $textOptions
   * @return string
   */
  public function asMagicDecimalsPrice($value, $currency = null, array $options = [], array $textOptions = [])
  {
    // TRICKY захардкодил, чтобы в рублях суммы отображались с 2 знаками после запятой
    $precision = 3;
    if ($currency === 'rub' || abs(round($value - round($value, 2), 3)) <= 0) {
      $precision = 2;
    }

    //TRICKY Если убрать round результат деления 5*100/1600 будет 0.312 вместо 0.313
    $value = round($value, $precision);
    return $this->asCurrency($value, $currency, $options, $textOptions, $precision);
  }

  /**
   * Если после плавающей точки 0, вернет как целое
   * @param $value
   * @param int $decimals
   * @return string
   */
  public function asMagicDecimals($value, $decimals = 2)
  {
    if (abs( round(round($value, $decimals) - round($value), $decimals)) == 0) {
      $decimals = 0;
    }

    return $this->asDecimal($value, $decimals);
  }

  /**
   * Функция для формата цены, если передан второй параметр, то выведет и название валюты.
   * @param number $value
   * @param string $currency Валюта, если не указана, валюта указана не будет. RUR EUR USD
   * @param array $options
   * - isPlusVisible[false] - отображать плюс для положительных чисел
   * - decorate[false] - @see decorateValue()
   * - append[null] - добавить строку после значения. Например: 1234 RUB (10%)
   * @param array $textOptions
   * @return string
   * @deprecated используй:
   * @see AdminFormatter::asCurrency() или любой другой
   */
  public function asPrice($value, $currency = null, array $options = [], array $textOptions = [])
  {
    $currencyCodes = $this->icons;

    $isPlusVisible = ArrayHelper::remove($options, 'isPlusVisible', false);
    $decorate = ArrayHelper::remove($options, 'decorate', false);
    $append = ArrayHelper::remove($options, 'append', null);

    $formattedValue = $this->asDecimal($value, 2, $options, $textOptions) . ' ' .
      ArrayHelper::getValue($currencyCodes, $currency);

    if ($isPlusVisible && $value > 0) {
      $formattedValue = '+' . $formattedValue;
    }
    if ($append) {
      $formattedValue .= $append;
    }
    if ($decorate) {
      $formattedValue = $this->decorateValue($value, $formattedValue);
    }

    return $formattedValue;
  }

  /**
   * Декорировать значение
   * Поведение по умолчанию: подсвечивать положительное значение - зеленым, отрицательное - красным.
   * Пример использования:
   * ```
   * Formatter::decorateValue(5, '5 USD (10%)'); // Возвратит <span class="text-success">5 USD (10%)</span>
   * Formatter::decorateValue(-5, '-5 USD (10%)'); // Возвратит <span class="text-danger">5 USD (10%)</span>
   * Formatter::decorateValue(0, '0 USD (0%)'); // Возвратит 0 USD (0%)</span>
   * ```
   * @param number $number Число, по которому будет выбираться positiveOptions или negativeOptions
   * @param string $content Контент для декорирования
   * @param array $positiveOptions Опции для положительного числа
   * @param array $negativeOptions Опции для отрицательного числа
   * @return string
   */
  public function decorateValue($number, $content, array $positiveOptions = [], array $negativeOptions = [])
  {
    if (!isset($positiveOptions['class'])) {
      $positiveOptions['class'] = 'text-success';
    }
    if (!isset($negativeOptions['class'])) {
      $negativeOptions['class'] = 'text-danger';
    }

    return $number == 0 ? $content : Html::tag('span', $content, $number > 0 ? $positiveOptions : $negativeOptions);
  }

  /**
   * @param $value
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function asGridDate($value)
  {
    return $value ? Yii::$app->getFormatter()->asDate($value) : '';
  }

  /**
   * @param $value
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function asPartnerDate($value)
  {
    return $value ? Yii::$app->getFormatter()->asDate($value, 'php:d.m.Y') : '';
  }

  /**
   * @param $longIp
   * @return string
   */
  public function asIpFromLong($longIp)
  {
    return long2ip($longIp);
  }

  /**
   * todo не понимаю отличие от $this->nullDisplay, хотя используется много где
   * @param $value
   * @return string
   */
  public function asStringOrNull($value)
  {
    return empty($value)
      ? '<span class="not-set">' . Yii::t('yii', '(not set)', [], $this->locale) . '</span>'
      : $value;

  }

  /**
   * покажет null как 0 в отличие от родительского
   * @param mixed $value
   * @param array $options
   * @param array $textOptions
   * @return string
   */
  public function asInteger($value, $options = [], $textOptions = [])
  {
    return parent::asInteger((int)$value, $options, $textOptions);
  }

  /**
   * покажет null как 0 в отличие от родительского
   * @param mixed $value
   * @param null $decimals
   * @param array $options
   * @param array $textOptions
   * @return string
   */
  public function asDecimal($value, $decimals = null, $options = [], $textOptions = [])
  {
    return parent::asDecimal((float)$value, $decimals, $options, $textOptions);
  }

  /**
   * Возвращает сумму с валютой и добавит кол-во знаков после плавающей точки из настройки
   * @param float $value
   * @param string $currency
   * @return string
   */
  public function asCurrencyCustomDecimal($value, $currency)
  {
    return $this->asCustomDecimal($value) . ' '
      . $this->asCurrencyIcon($currency);
  }

  /**
   * Возвращает сумму с валютой. Если $value пустое, вернет null
   * @param float $value
   * @param string $currency
   * @return string
   */
  public function asCurrencyDecimal($value, $currency)
  {
    if ($value === null) {
      return $value;
    }
    return $this->asDecimal($value) . ' ' . $this->asCurrencyIcon($currency);
  }

  /**
   * Возвращает число с плавающей точкой с кол-во знаков после точки взятым из настройки
   * @param float $value
   * @return string
   */
  public function asCustomDecimal($value)
  {
    return $this->asDecimal($value, $this->decimals);
  }

  /**
   * Форматтер для статистики
   * @param $value
   * @param array $options
   * @param array $textOptions
   * @return string
   */
  public function asStatisticSum($value, array $options = [], array $textOptions = [])
  {
    return $this->asCurrency($value, null, $options, $textOptions, 2);
  }

  /**
   * Вывод названия лендинга в формате "#id. name"
   * @param int $id
   * @param string $name
   * @return string
   * @deprecated наверно не лучшее решение для форматтера, лучше не использовать
   */
  public function asLanding($id, $name)
  {
    return sprintf('#%s. %s', $id, $name);
  }

  /**
   * @param $value
   * @param $percent
   * @param bool $useFormatter Использовать asPercent для форматирования процентов
   * @return string
   */
  public function asRatio($value, $percent = null, $useFormatter = false)
  {
    $rationRightPart = $this->asDecimal((float)$value, 1);
    if ($percent !== null) {
      if ($useFormatter) {
        return sprintf('1:%s (%s)', $rationRightPart, $this->asPercent($percent / 100, 2));
      }
      return sprintf('1:%s (%s%%)', $rationRightPart, $percent);
    }
    return sprintf('1:%s', $rationRightPart);
  }

  /**
   * @param $value
   * @return mixed
   */
  public function asXSSFilterUrl($value)
  {
    if (!$value) {
      return $value;
    }


    $stripped = strip_tags(strtr($value, [
      '%3C' => '<',
      '%3E' => '>',
    ]));

    //TRICKY заменяем фильтрацию через filter_var на регулярку т.к. FILTER_SANITIZE_URL вырезает кириллицу
    //FILTER_SANITIZE_URL: This filter allows all letters, digits and $-_.+!*'(),{}|\\^~[]`"><#%;/?:@&=
    $filtered = preg_replace('#[^а-яА-Яa-zA-Z0-9$\-_\.+!*\'\(\),{}\|/^~\[\]\`\"\>\<\#%;\?:@&=]#u', '', $stripped);

    return $filtered;
  }

  /**
   * Можно было бы просто вызвать функцию idn_to_ascii(), но она возвращает ошибку если длина строки превышена.
   * Потому конвертим только наименование домена при помощи этого метода
   *
   * @param $value
   * @return mixed|string
   */
  public function asIdnToAscii($value)
  {
    $parsed = parse_url($value);

    if (!$parsed) {
      return '';
    }

    return str_replace($parsed['host'], idn_to_ascii($parsed['host']), $value);
  }

  /**
   * Подсчет процентного соотношения
   * Пример:
   * ```
   * // Вернет 20.0
   * Formatter::calcPercent(2000, 10000, 2);
   * ```
   * TRICKY Метод только считает проценты и не учитывает настройку decimalSeparator. Используйте asPercent для форматирования
   * @param number $value
   * @param number $maxValue
   * @param null|int $decimals Количество цифр после запятой
   * @return float|int
   */
  public function calcPercent($value, $maxValue, $decimals = null)
  {
    return $value && $maxValue ? round($value / ($maxValue / 100), $decimals) : 0;
  }

  /**
   * Форматирования в виде процента.
   * Для автоматического подсчета процентного соотношения передайте в параметр $value массив [0 => value, 1 => max_value]
   * @param number|array $value
   * Пример:
   * ```
   * // Вернет 20,0 %
   * Formatter::asPercent(2000, 2);
   * // Посчитает и вернет 20,0 %
   * Formatter::asPercent([2000, 10000], 2);
   * ```
   * @inheritdoc
   */
  public function asPercent($value, $decimals = null, $options = [], $textOptions = [], $sign = ' %')
  {
    if (is_array($value)) {
      $value = $this->calcPercent($value[0], $value[1], $decimals) / 100;
    }

    $isPlusVisible = ArrayHelper::remove($options, 'isPlusVisible', false);
    $decorate = ArrayHelper::remove($options, 'decorate', false);

    $formattedValue = parent::asPercent($value, $decimals, $options, $textOptions, $sign);

    if ($isPlusVisible && $value > 0) {
      $formattedValue = '+' . $formattedValue;
    }
    if ($decorate) {
      $formattedValue = $this->decorateValue($value, $formattedValue);
    }

    return $formattedValue;
  }

  /**
   * Отличия от стандартного asPercent()
   * - не нужно делить на 100 передаваемое значение
   * - количество цифр запятой по умолчанию 2
   *
   * @param number $value Проценты
   * @param integer|false|null $decimals По умолчанию 2
   * - integer - количество после запятой
   * - false - не отображать цифры после запятой
   * - null - установить значение по умолчанию
   * @param array $options
   * @param array $textOptions
   * @see asPercent()
   * @return string
   */
  public function asPercentSimple($value, $decimals = null, $options = [], $textOptions = [])
  {
    if ($decimals === null) {
      $decimals = 2;
    }
    return $this->asPercent($value / 100, $decimals, $options, $textOptions);
  }

  /**
   * Список валют
   * @param string[] $currencies
   * @param string[] $activeCurrencies
   * @param string $glue
   * @return string
   */
  public function asCurrenciesList($currencies, $activeCurrencies, $glue = ', ')
  {
    // Сортировка
    $result = ['rub' => false, 'usd' => false, 'eur' => false];
    foreach ($currencies as $currency) {
      $result[$currency] = in_array($currency, $activeCurrencies)
      ? strtoupper($currency)
      : Html::tag('span', strtoupper($currency), ['class' =>'text-danger', 'title' => Yii::_t('payments.wallets.currency-off')]);
    }

    return implode($glue, array_filter($result));
  }

  /**
   * Вывод денежных значений с валютой в виде строки 100.00 Р | $ 15.00 | € 12.00
   * @param array $values ['rub' => 100, 'usd' => 15, 'eur' => 12]
   * @param string $glue Разделитель между значениями
   * @param array $currencies Валюты для отображения
   * @return string
   */
  public function asPrices($values, $glue = ' | ', $currencies = ['rub', 'usd', 'eur'])
  {
    // $valuesFormatted сделан для сортировки
    $valuesFormatted = ['rub' => null, 'usd' => null, 'eur' => null];
    foreach ($currencies as $currency) {
      if (!isset($values[$currency]) || $values[$currency] === null) {
        continue;
      }

      $valuesFormatted[$currency] = $this->asPrice($values[$currency], $currency);
    }

    $valuesFormatted = array_filter($valuesFormatted);
    return $valuesFormatted ? implode($glue, $valuesFormatted) : $this->nullDisplay;
  }

  /**
   * Аналог asPrices(), но значения автоматически извлекаются из модели
   * @see asPrices()
   * @param ActiveRecord $model
   * @param string $attributePattern Например: %s_max_payout_sum (rub_max_payout_sum, usd_max_payout_sum, eur_max_payout_sum)
   * @param string $glue @see asPrices()
   * @param array $currencies @see asPrices()
   * @return string
   */
  public function asPricesByModel($model, $attributePattern, $glue = ' | ', $currencies = ['rub', 'usd', 'eur'])
  {
    $values = [];
    foreach ($currencies as $currency) {
      $values[$currency] = $model->{sprintf($attributePattern, $currency)};
    }

    return $this->asPrices($values, $glue, $currencies);
  }

  /**
   * Преобразование диапазона дат в формате БД в формат указанный в конфиге
   * @param $value
   * @param string $delimiter
   * @return string
   */
  public function asDateRangeFormat($value, $delimiter = ' - ')
  {
    $dateRange = explode($delimiter, $value);
    $dateStart = ArrayHelper::getValue($dateRange, 0);
    $dateEnd = ArrayHelper::getValue($dateRange, 1);

    if (!$dateEnd) {
      return $this->asDate($value);
    }
    return $this->asDate(trim($dateStart)) . $delimiter . $this->asDate(trim($dateEnd));
  }
}
