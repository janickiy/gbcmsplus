<?php

namespace mcms\partners\components\mainStat;

use mcms\statistic\models\Cr;
use mcms\statistic\Module;
use Yii;

/**
 * Переопределенная модель для статы в ПП
 */
class Row extends \mcms\statistic\components\mainStat\mysql\Row
{

  /** @var bool принятый траф считаем как уникальный принятый. Это чтобы ратио был пизже */
  public $isRatioByUniques;

  /**
   * Для ПП это сумма видимых продаж и ИК
   * @return int
   */
  public function getCpaOns()
  {
    return $this->getSoldVisible() + $this->getVisibleOnetime();
  }

  /**
   * Сумма ИК и продаж
   * @return float
   */
  public function getCpaPartnerProfit()
  {
    return $this->getSoldPartnerProfit() + $this->getOnetimeProfit();
  }

  /**
   * Для ПП это сумма принятого трафа для продаж и ИК
   * @return int
   */
  public function getCpaAccepted()
  {
    if (!$this->isRatioByUniques) {
      return parent::getCpaAccepted() + $this->getAcceptedOnetime();
    }

    return $this->getCpaAcceptedUniques();
  }

  /**
   * @return int
   */
  public function getRevshareAccepted()
  {
    if (!$this->isRatioByUniques) {
      return parent::getRevshareAccepted();
    }

    return $this->getRevshareAcceptedUniques();
  }


  /**
   * не вычитаем тут ИК в отличие от родительского
   * @return int
   */
  public function getCpaUniques()
  {
    return (int)$this->rowDataDto->cpaUniques;
  }

  /**
   * не вычитаем тут ИК в отличие от родительского
   * @return int
   */
  public function getCpaUniquesTb()
  {
    return (int)$this->rowDataDto->cpaUniquesTb;
  }

  /**
   * @return float
   */
  public function getECPM()
  {
    $profit = $this->getCpaPartnerProfit();

    $accepted = $this->getCpaAccepted();

    if (!$accepted) {
      return 0;
    }

    return $profit / ($accepted / 1000);
  }

  /**
   * Столбец жалоб для партнера
   * @return int
   */
  public function getComplains()
  {
    $sum = 0;
    /** @var Module $module */
    $module = Yii::$app->getModule('statistic');
    if ($module->canPartnerViewComplainText()) {
      $sum += parent::getComplains();
    }
    if ($module->canPartnerViewComplainCall()) {
      $sum += $this->getCalls();
    }
    if ($module->canPartnerViewComplainCallMno()) {
      $sum += $this->getCallsMno();
    }
    if ($module->canPartnerViewComplainAuto24()) {
      $sum += $this->getAuto24Complains();
    }
    if ($module->canPartnerViewComplainAutoMoment()) {
      $sum += $this->getAutoMomentComplains();
    }
    if ($module->canPartnerViewComplainAutoDuplicate()) {
      $sum += $this->getAutoDuplicateComplains();
    }

    return $sum;
  }

  /**
   * TODO временно переопределил чтобы убрать форматтер asDecimal, т.к. он не работает с precision=1
   * Ревшар ратио
   * @param string $format
   * @return string
   */
  public function getRevshareRatio($format = '1:%s')
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getRevshareAccepted();
    $cr->fullCount = $this->getOns();
    $rightRatioValue = round($cr->getRate(), 1);

    return sprintf($format, $rightRatioValue);
  }

  /**
   * TODO временно переопределил чтобы убрать форматтер asDecimal, т.к. он не работает с precision=1
   * CPA ратио
   * @param string $format
   * @return string
   */
  public function getCpaRatio($format = '1:%s')
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCpaAccepted();
    $cr->fullCount = $this->getCpaOns();
    $rightRatioValue = round($cr->getRate(), 1);

    return sprintf($format, $rightRatioValue);
  }
}
