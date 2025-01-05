<?php

namespace mcms\promo\components\api\personal_profit;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\PersonalProfit;
use yii\base\Object;

/**
 * Class PersonalProfitValues
 * @package mcms\promo\components\api\personal_profit
 */
class PersonalProfitValues extends Object
{


  /** @var  float|null */
  public $rebillPercent;
  /** @var  float|null */
  public $buyoutPercent;
  /** @var  float|null */
  public $fixedCpaProfitRub;
  /** @var  float|null */
  public $fixedCpaProfitEur;
  /** @var  float|null */
  public $fixedCpaProfitUsd;
  /** @var  int|null */
  public $updatedAt;

  /**
   * @return bool
   */
  public function isFilled()
  {
    return !empty($this->rebillPercent) && !empty($this->buyoutPercent);
  }

  /**
   * @param PersonalProfit|array $values
   */
  public function loadValuesIfEmpty($values)
  {
    $rebillPercent = ArrayHelper::getValue($values, 'rebill_percent');
    $buyoutPercent = ArrayHelper::getValue($values, 'buyout_percent');
    $cpaProfitRub = ArrayHelper::getValue($values, 'cpa_profit_rub');
    $cpaProfitEur = ArrayHelper::getValue($values, 'cpa_profit_eur');
    $cpaProfitUsd = ArrayHelper::getValue($values, 'cpa_profit_usd');
    $updatedAt = ArrayHelper::getValue($values, 'updated_at');

    if (empty($this->rebillPercent) && !empty($rebillPercent)) {
      $this->rebillPercent = (float)$rebillPercent;
    }

    if (empty($this->buyoutPercent) && !empty($buyoutPercent)) {
      $this->buyoutPercent = (float)$buyoutPercent;
    }

    if (empty($this->fixedCpaProfitRub) && !empty($cpaProfitRub)) {
      $this->fixedCpaProfitRub = (float)$cpaProfitRub;
    }

    if (empty($this->fixedCpaProfitEur) && !empty($cpaProfitEur)) {
      $this->fixedCpaProfitEur = (float)$cpaProfitEur;
    }

    if (empty($this->fixedCpaProfitUsd) && !empty($cpaProfitUsd)) {
      $this->fixedCpaProfitUsd = (float)$cpaProfitUsd;
    }

    if ((int)($this->updatedAt) < (int)($updatedAt)) {
      $this->updatedAt = (int)$updatedAt;
    }
  }
}