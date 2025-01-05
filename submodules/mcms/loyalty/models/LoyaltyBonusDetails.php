<?php

namespace mcms\loyalty\models;

use mcms\common\traits\Translate;
use rgk\utils\components\CurrenciesValues;
use Yii;
use yii\base\Model;
use yii\base\NotSupportedException;

/**
 * Контейнер данных по которым был произведен рассчет бонуса
 * @property TurnoverRule $turnoverRule
 * @property GrowRule $growRule
 * @property CurrenciesValues $turnoverLastMonth
 * @property CurrenciesValues $turnoverBeforeLastMonth
 * @property CurrenciesValues $turnoverThreeMonthAgo
 */
class LoyaltyBonusDetails extends Model implements \JsonSerializable
{
  use Translate;

  const LANG_PREFIX = 'loyalty.bonus_details.';

  // Даты
  /** @var string Предыдущий месяц (формат Y-m) */
  public $dateLastMonth;
  /** @var string Позапрошлый месяц (формат Y-m) */
  public $dateBeforeLastMonth;
  /** @var string 3 месяца назад (формат Y-m) */
  public $dateThreeMonthAgo;

  // Доход по валютам
  /**
   * @var CurrenciesValues Доход за прошлый месяц по каждой валюте
   * @see LoyaltyBonusProcess::calcTurnoverSumUsd
   */
  private $_turnoverLastMonth;

  /**
   * @var CurrenciesValues Доход за позапрошлый месяц по каждой валюте
   * @see LoyaltyBonusProcess::calcTurnoverSumUsd
   */
  private $_turnoverBeforeLastMonth;
  /**
   * @var CurrenciesValues Доход за позапозапрошлый месяц по каждой валюте
   * @see LoyaltyBonusProcess::calcTurnoverSumUsd
   */
  private $_turnoverThreeMonthAgo;

  // Суммарный доход в долларах
  /** @var number Суммарный доход в долларах за прошлый месяц */
  public $turnoverLastMonthSum;
  /** @var number Суммарный доход в долларах за позапрошлый месяц */
  public $turnoverBeforeLastMonthSum;
  /** @var number Суммарный доход в долларах за позапозапрошлый месяц */
  public $turnoverThreeMonthAgoSum;

  // Рассчеты
  /**
   * @var float Рост оборота за прошлый месяц по сравнению с позапрошлым месяцем
   * @see LoyaltyBonusProcess::calcTurnoverGrowPercent
   */
  public $growPercent;

  // Правила бонусов
  /**
   * @var array Бонус по обороту за прошлый месяц
   * @see setTurnoverRule()
   * @see getTurnoverRule()
   */
  public $turnoverRuleData;
  /**
   * @var array Бонус по росту оборота за прошлый месяц по сравнению с позапрошлым месяцем
   * @see setGrowRule()
   * @see getGrowRule()
   */
  public $growRuleData;

  /**
   * @inheritdoc
   */
  public function __construct(array $config = [])
  {
    $this->resetTurnover();

    parent::__construct($config);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'turnoverRule',
      'growRule',
      'growPercent',
    ]);
  }

  /**
   * Сбросить доходы
   */
  public function resetTurnover()
  {
    $this->turnoverLastMonth = CurrenciesValues::createEmpty();
    $this->turnoverBeforeLastMonth = CurrenciesValues::createEmpty();
    $this->turnoverThreeMonthAgo = CurrenciesValues::createEmpty();

    $this->turnoverLastMonthSum = null;
    $this->turnoverBeforeLastMonthSum = null;
    $this->turnoverThreeMonthAgoSum = null;
  }

  /**
   * Установить доход по периоду и валюте.
   * Сделано для удобства
   * @param float $turnover
   * @param string $currency
   * @param string $period
   * @throws NotSupportedException
   */
  public function setTurnover($turnover, $currency, $period)
  {
    switch ($period) {
      case LoyaltyBonusProcess::LAST_MONTH:
        $this->turnoverLastMonth->setValue($currency, $turnover);
        break;
      case LoyaltyBonusProcess::BEFORE_LAST_MONTH:
        $this->turnoverBeforeLastMonth->setValue($currency, $turnover);
        break;
      case LoyaltyBonusProcess::THREE_MONTH_AGO:
        $this->turnoverThreeMonthAgo->setValue($currency, $turnover);
        break;
      default:
        Yii::error('Указан неизвестный период "' . $period . '"', __METHOD__);
    }
  }

  /**
   * Установить суммарный доход в долларах.
   * @param number[] $turnover Суммы доходов по периоду
   */
  public function setTurnoverSum($turnover)
  {
    $this->turnoverLastMonthSum = $turnover[LoyaltyBonusProcess::LAST_MONTH];
    $this->turnoverBeforeLastMonthSum = $turnover[LoyaltyBonusProcess::BEFORE_LAST_MONTH];
    $this->turnoverThreeMonthAgoSum = $turnover[LoyaltyBonusProcess::THREE_MONTH_AGO];
  }

  /**
   * Установить актуальные даты
   */
  public function updateDates()
  {
    $this->dateLastMonth = date('Y-m', strtotime('-1 month'));
    $this->dateBeforeLastMonth = date('Y-m', strtotime('-2 month'));
    $this->dateThreeMonthAgo = date('Y-m', strtotime('-3 month'));
  }

  /**
   * @inheritdoc
   */
  public function jsonSerialize()
  {
    return array_merge($this->getAttributes(), [
      'dateLastMonth' => $this->dateLastMonth,
      'dateBeforeLastMonth' => $this->dateBeforeLastMonth,
      'dateThreeMonthAgo' => $this->dateThreeMonthAgo,
      'turnoverLastMonth' => $this->turnoverLastMonth->toArray(),
      'turnoverBeforeLastMonth' => $this->turnoverBeforeLastMonth->toArray(),
      'turnoverThreeMonthAgo' => $this->turnoverThreeMonthAgo->toArray(),
    ]);
  }

  /**
   * Краткое описание примененного правила
   * @param string $type
   * @param string $glue
   * @return null|string
   * @throws \yii\base\NotSupportedException
   * @throws \yii\base\InvalidParamException
   */
  public function getRuleAsText($type, $glue = '. ')
  {
    if (!$type) return null;

    $formatter = Yii::$app->formatter;

    $text = null;
    switch ($type) {
      case TurnoverRule::getCode():
        $text = static::t('turnover_rule_as_text', [
          'id' => $this->turnoverRule->id,
          'amount' => $formatter->asCurrency($this->turnoverRule->amount, 'usd'),
          'percent' => $formatter->asPercentHandy($this->turnoverRule->percent),
          'glue' => $glue,
        ]);
        break;
      case GrowRule::getCode():
        $text = static::t('grow_rule_as_text', [
          'id' => $this->growRule->id,
          'amount' => $formatter->asPercentHandy($this->growRule->amount),
          'percent' => $formatter->asPercentHandy($this->growRule->percent),
          'glue' => $glue,
        ]);
        break;
      default:
        Yii::error('Указан неизвестный тип правила бонуса "' . $type . '"', __METHOD__);
    }

    return $text;
  }

  /**
   * TRICKY Объект TurnoverRule используется в качестве контейнера данных, поэтому наличие ID не означает существование в БД
   * @return TurnoverRule
   */
  public function getTurnoverRule()
  {
    $rule = new TurnoverRule;
    $rule->setAttributes($this->turnoverRuleData, false);
    return $rule;
  }

  /**
   * @param TurnoverRule $rule
   */
  public function setTurnoverRule(TurnoverRule $rule)
  {
    $this->turnoverRuleData = $rule->toArray();
  }

  /**
   * TRICKY Объект GrowRule используется в качестве контейнера данных, поэтому наличие ID не означает существование в БД
   * @return GrowRule
   */
  public function getGrowRule()
  {
    $rule = new GrowRule;
    $rule->setAttributes($this->growRuleData, false);
    return $rule;
  }

  /**
   * @param GrowRule $rule
   */
  public function setGrowRule($rule)
  {
    $this->growRuleData = $rule->toArray();
  }


  /**
   * @return CurrenciesValues
   */
  public function getTurnoverLastMonth()
  {
    return $this->_turnoverLastMonth;
  }

  /**
   * @param CurrenciesValues $turnoverLastMonth
   */
  public function setTurnoverLastMonth(CurrenciesValues $turnoverLastMonth)
  {
    $this->_turnoverLastMonth = $turnoverLastMonth;
  }

  /**
   * @return CurrenciesValues
   */
  public function getTurnoverBeforeLastMonth()
  {
    return $this->_turnoverBeforeLastMonth;
  }

  /**
   * @param CurrenciesValues $turnoverBeforeLastMonth
   */
  public function setTurnoverBeforeLastMonth(CurrenciesValues $turnoverBeforeLastMonth)
  {
    $this->_turnoverBeforeLastMonth = $turnoverBeforeLastMonth;
  }

  /**
   * @return CurrenciesValues
   */
  public function getTurnoverThreeMonthAgo()
  {
    return $this->_turnoverThreeMonthAgo;
  }

  /**
   * @param CurrenciesValues $turnoverThreeMonthAgo
   */
  public function setTurnoverThreeMonthAgo(CurrenciesValues $turnoverThreeMonthAgo)
  {
    $this->_turnoverThreeMonthAgo = $turnoverThreeMonthAgo;
  }
}