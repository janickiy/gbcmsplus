<?php

namespace mcms\statistic\models;

use mcms\common\mgmp\MgmpClient;
use mcms\promo\models\Country;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class ResellerHoldRule
 * @package mcms\statistic\models
 */
class ResellerHoldRule extends Model
{
  const UNHOLD_RANGE_TYPE_DAY = 1;
  const UNHOLD_RANGE_TYPE_WEEK = 2;
  const UNHOLD_RANGE_TYPE_MONTH = 3;
  const AT_DAY_TYPE_WEEK = 1;
  const AT_DAY_TYPE_MONTH = 2;

  /**
   * кол-во полных дней|недель|месяцев сколько расхолдиваем
   * @var int
   */
  public $unholdRange;
  /**
   * тип unholdRange (дней=1|недель=2|месяцев=3)
   * @var int
   */
  public $unholdRangeType;
  /**
   * через сколько минимум дней|недель|месяцев расхолдиваем
   * @var int
   */
  public $minHoldRange;
  /**
   * тип min_hold_range (дней=1|недель=2|месяцев=3)
   * @var int
   */
  public $minHoldRangeType;
  /**
   * в какой день (недели|месяца) расхолд (необязательно)
   * @var int
   */
  public $atDay;
  /**
   * тип at_day (день недели=1|день месяца=2)
   * @var int
   */
  public $atDayType;

  /**
   * Пример элемента массива:
   * [
   *   'countryId' => 1, // совпадение по стране (персональное значение под страну)
   *   'isReseller' => true, // совпадение по реселлеру (персональное значение под реса)
   *   'rule' => new self([
   *    'unholdRange' => 3,
   *    'unholdRangeType' => 2,
   *    'minHoldRange' => 1,
   *    'minHoldRangeType' => 2,
   *    'atDay' => null,
   *    'atDayType' => null,
   *   ])
   * ]
   * @var array
   */
  protected static $_fetchedRules;


  /**
   * @return self[]
   */
  public static function getCountriesRules()
  {
    $countries = Country::find()->all();

    $models = [];

    foreach ($countries as $country) {
      /** @var Country $country */
      $models[$country->id] = self::getMatchedRule($country->id);
    }

    return $models;
  }

  /**
   * @return array
   * @throws Exception
   */
  private static function fetchRules()
  {
    if (isset(self::$_fetchedRules)) return self::$_fetchedRules;

    $mgmpResponse = Yii::$app->mgmpClient->requestData(MgmpClient::URL_RESELLER_HOLD_SETTINGS);

    if (!$mgmpResponse->getIsOk()) {
      throw new Exception('MGMP Api not work');
    }

    self::$_fetchedRules = [];

    $data = $mgmpResponse->getData();

    if (!is_array($data)) throw new Exception('MGMP Api returned not array');
    if (!ArrayHelper::getValue($data, 'success')) throw new Exception('MGMP Api returned success=false');
    if (!$items = ArrayHelper::getValue($data, 'data')) throw new Exception('MGMP Api returned data=false');
    if (!is_array($items)) throw new Exception('MGMP Api returned data not array');

    foreach ($items as $item) {
      self::$_fetchedRules[] = [
        'countryId' => $item['country_id'],
        'isReseller' => !!$item['reseller_id'],
        'rule' => new self([
          'unholdRange' => $item['unhold_range'],
          'unholdRangeType' => $item['unhold_range_type'],
          'minHoldRange' => $item['min_hold_range'],
          'minHoldRangeType' => $item['min_hold_range_type'],
          'atDay' => $item['at_day'],
          'atDayType' => $item['at_day_type'],
        ])
      ];
    }

    return self::$_fetchedRules;
  }

  /**
   * @param $countryId
   * @return self|null
   * @throws Exception
   */
  private static function getMatchedRule($countryId)
  {
    // Сначала ищем по совпадению реселлера и страны
    if ($found = self::getByConditions($countryId, true)) {
      return $found;
    }

    // Ищем по совпадению реселлера
    if ($found = self::getByConditions(null, true)) {
      return $found;
    }

    // Ищем по совпадению страны
    if ($found = self::getByConditions($countryId, false)) {
      return $found;
    }

    // Ищем без совпадения
    if ($found = self::getByConditions(null, false)) {
      return $found;
    }

    Yii::error('No unhold rule for country ' . $countryId);

    return null;
  }

  /**
   * @param $countryId
   * @param $isReseller
   * @return self|null
   */
  private static function getByConditions($countryId, $isReseller)
  {
    foreach (self::fetchRules() as $rule) {
      $fethedCountryId = ArrayHelper::getValue($rule, 'countryId');
      $fetchedIsReseller = ArrayHelper::getValue($rule, 'isReseller');
      $fetchedRule = ArrayHelper::getValue($rule, 'rule');

      if ($fethedCountryId == $countryId AND $fetchedIsReseller == $isReseller) return $fetchedRule;
    }
    return null;
  }


}
