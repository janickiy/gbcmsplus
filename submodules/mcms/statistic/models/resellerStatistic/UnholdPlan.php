<?php

namespace mcms\statistic\models\resellerStatistic;

use mcms\promo\models\Country;
use rgk\utils\components\CurrenciesValues;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Модель расхолда для одной даты. Представляет собой как-бы датапровайдер для данных по расхолдам.
 *
 *
 * Class UnholdPlan
 * @package mcms\statistic\models\resellerStatistic
 */
class UnholdPlan extends Model
{
  /**
   * [['date' => '2017-06-27', 'country' => Country, 'values' => CurrencyValues], ...]
   * @var  array
   */
  private $_values;

  /**
   * @param $date
   * @param Country $country
   * @param CurrenciesValues $values
   * @return $this
   */
  public function addValue($date, Country $country, CurrenciesValues $values)
  {
    $this->_values[] = [
      'date' => $date,
      'country' => $country,
      'values' => $values
    ];
    return $this;
  }

  /**
   * Получить сгруппированные по date => countryId значения
   *
   * @return array ['2017-06-27' => [
   *  1 => ['date' => '2017-06-27', 'country' => Country, 'values' => CurrencyValues],
   *  2 => ['date' => '2017-06-27', 'country' => Country, 'values' => CurrencyValues]]
   * ]
   */
  public function getMappedValues()
  {
    return ArrayHelper::map($this->_values, 'country.id', function ($row) {
      return $row;
    }, 'date');
  }
}