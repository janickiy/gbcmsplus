<?php

namespace mcms\statistic\components\cron;

use mcms\common\traits\LogTrait;
use mcms\payments\components\api\GetGroupedBalanceProfitTypes;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\db\Query;

/**
 * Class AbstractTableHandler
 * @package mcms\statistic\components\cron
 *
 * @property array $mainCurrencies
 * @property GetGroupedBalanceProfitTypes $profitTypes
 * @property string $notAdminAndResellerQuery
 * @property int $resellerId
 */
abstract class AbstractTableHandler extends Object
{
  const OFFS_SCOPE_HOURS = 24;

  use LogTrait;

  /** @var  array кэш валют */
  private static $_mainCurrencies;

  /** @var GetGroupedBalanceProfitTypes кэш типов профита */
  private static $_profitTypes;

  /** @var int кэш id реселлера */
  private static $_resellerId;

  /** @var  CronParams */
  public $params;

  /**
   * @var array $trialOperators id орпеаторов именющих trial
   */
  public $trialOperators;


  public function init()
  {
    parent::init();
    if (!isset($this->params)) {
      throw new InvalidConfigException('[[params]] property must be defined');
    }
    if (!$this->params instanceof CronParams) {
      throw new InvalidConfigException('[[params]] property must be instance of ' . CronParams::class);
    }
  }


  /**
   * валюты [code => id]
   * @return array
   */
  public function getMainCurrencies()
  {
    if (self::$_mainCurrencies) return self::$_mainCurrencies;

    return self::$_mainCurrencies = Yii::$app->getModule('promo')
      ->api('mainCurrencies')
      ->setResultTypeMap()
      ->setMapParams(['code', 'id'])
      ->getResult();
  }

  /**
   * запускает обработчик
   */
  abstract function run();

  /**
   * @return GetGroupedBalanceProfitTypes
   */
  public function getProfitTypes()
  {
    if (self::$_profitTypes) return self::$_profitTypes;

    return self::$_profitTypes = Yii::$app->getModule('payments')->api('getGroupedBalanceProfitTypes');
  }

  /**
   * @return int
   */
  public function getTypeRebill()
  {
    return $this->profitTypes->getTypeRebill();
  }

  /**
   * @return int
   */
  public function getTypeBuyout()
  {
    return $this->profitTypes->getTypeBuyout();
  }

  /**
   * @return int
   */
  public function getTypeOnetime()
  {
    return $this->profitTypes->getTypeOnetime();
  }

  /**
   * @return int
   */
  public function getTypeSellTrafficback()
  {
    return $this->profitTypes->getTypeSellTrafficback();
  }

  /**
   * @return int
   */
  public function getTypeReferral()
  {
    return $this->profitTypes->getTypeReferral();
  }

  /**
   * @return bool|int
   */
  protected function getResellerId()
  {
    if (self::$_resellerId) return self::$_resellerId;

    if ($reseller = Yii::$app->getModule('users')->api('usersByRoles', ['reseller'])->getResult()) {
      return self::$_resellerId = current($reseller)['id'];
    }
    return false;
  }

  /**
   * Условие по trial операторам
   * @param string $operatorField поле для которого применить условие
   * @param bool $not NOT IN
   * @return string
   */
  public function getTrialOperatorsInCondition($operatorField, $not = false)
  {
    $this->trialOperators = Yii::$app->getModule('promo')->api('trialOperators')->getResult();
    if (empty($this->trialOperators)) {
      return '1 = 1';
    }
    return $operatorField . ($not ? ' NOT' : '') . ' IN (' . implode(', ', $this->trialOperators) . ')';
  }

  /**
   * Период для поиска отписок по подписке. 24ч
   * @return int
   */
  public function getOffsScopeHours()
  {
    return self::OFFS_SCOPE_HOURS;
  }

  /**
   * Начальная дата подписок для обработки
   * @return int
   */
  public function getFromDate()
  {
    /* Раздвигаем диапазон запроса по левой границе дат. Иначе в него не попадут подписки,
     * совершенные ранее $fromTime, хотя и будут в пределах $groupOffsHours */
    return date('Y-m-d', $this->params->fromTime - $this->getOffsScopeHours() * 60 * 60);
  }

  /**
   * Получить максимальную дату, до которой включительно баланс партнеров не группировался по стране (country_id=0)
   * @return string Y-m-d
   */
  protected function getMaxDateBalanceCountryEmpty()
  {
    $maxDateHasEmptyCountry = (new Query())
      ->from('user_balances_grouped_by_day')
      ->where(['country_id' => 0])
      ->max('date');

    return $maxDateHasEmptyCountry;
  }
}
