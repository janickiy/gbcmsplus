<?php

namespace mcms\partners\components\subidStat;

use mcms\statistic\models\Cr;
use Yii;

/**
 * @property RowDataDto $rowDataDto
 * Модель для статы по меткам в ПП
 */
class Row extends \mcms\statistic\components\mainStat\mysql\Row
{

  public function init()
  {
    parent::init();
    $this->_rowDataDto = new RowDataDto();
  }

  public function getSubid1()
  {
    return (string)$this->rowDataDto->subid1;
  }

  public function getSubid2()
  {
    return (string)$this->rowDataDto->subid2;
  }

  /**
   * @return int
   */
  public function getHits()
  {
    return (int)$this->rowDataDto->hits;
  }

  /**
   * @return int
   */
  public function getUniques()
  {
    return (int)$this->rowDataDto->uniques;
  }

  /**
   * @return int
   */
  public function getTb()
  {
    return (int)$this->rowDataDto->tb;
  }

  /**
   * @return int
   */
  public function getAccepted()
  {
    return $this->getHits() - $this->getTb();
  }

  /**
   * Равшар подписки
   * @return int
   */
  public function getOns()
  {
    return (int)$this->rowDataDto->ons;
  }

  /**
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

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * Ревшар принятые
   * @return int
   */
  public function getRevshareAccepted()
  {
    return (int)$this->rowDataDto->revshareHits - (int)$this->rowDataDto->revshareTb;
  }

  /**
   * Равшар отписки
   * @return int
   */
  public function getOffs()
  {
    return (int)$this->rowDataDto->offs;
  }

  /**
   * Равшар ребиллы
   * @return int
   */
  public function getRebills()
  {
    return (int)$this->rowDataDto->rebills;
  }

  /**
   * Доход с ребилов
   * @return float
   */
  public function getRevshareProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'rebillsProfit' . $currency};
  }

  /**
   * @return int
   */
  public function getCpaOns()
  {
    return (int)$this->rowDataDto->sold + (int)$this->rowDataDto->onetimes;
  }

  /**
   * eCPM СPA
   * @return float
   */
  public function getCpaEcpm()
  {
    $cr = new Cr();
    $cr->convertionsCount = $this->getCpaPartnerProfit();
    $cr->fullCount = $this->getCpaAccepted();

    return $cr->getRate() * 1000;
  }

  /**
   * CPA принятые
   * @return int
   */
  public function getCpaAccepted()
  {
    return (int)$this->rowDataDto->cpaHits - (int)$this->rowDataDto->cpaTb;
  }

  /**
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

    return sprintf($format, Yii::$app->formatter->asDecimal($rightRatioValue, 1));
  }

  /**
   * Сумма ИК и продаж
   * @return float
   */
  public function getCpaPartnerProfit()
  {
    $currency = ucfirst($this->getCurrency());
    return (float)$this->rowDataDto->{'onetimeProfit' . $currency} + (float)$this->rowDataDto->{'soldProfit' . $currency};
  }

  /**
   * Весь профит партнера
   * @return float
   */
  public function getTotalPartnerProfit()
  {
    return $this->getCpaPartnerProfit() + $this->getRevshareProfit();
  }


}