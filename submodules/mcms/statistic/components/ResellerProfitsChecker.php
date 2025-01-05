<?php

namespace mcms\statistic\components;

use DateTime;
use mcms\common\helpers\ArrayHelper;
use rgk\utils\interfaces\ExecutableInterface;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\Query;

/**
 * Проверка балансов реселлера
 */
class ResellerProfitsChecker extends Object implements ExecutableInterface
{
  public $dateFrom = '-1 week';
  public $dateTo = '-1 day';
  public $logger;

  /**
   * @inheritdoc
   */
  public function execute()
  {
    if (!$this->logger) throw new InvalidParamException('Параметр ResellerProfitsChecker::logger обязателен');

    $dateFrom = $this->getDateFrom()->format('Y-m-d');
    $dateTo = $this->getDateTo()->format('Y-m-d');
    $this->log("Start. Date from $dateFrom to $dateTo\n");

    $this->recalc();
    $this->compare();

    $this->log("Finish\n");
  }

  /**
   * @return DateTime
   */
  private function getDateFrom()
  {
    return new DateTime($this->dateFrom);
  }

  /**
   * @return DateTime
   */
  private function getDateTo()
  {
    return new DateTime($this->dateTo);
  }

  /**
   * @return bool
   */
  private function recalc()
  {
    $this->log("Recalc...\n");

    return (new ResellerProfits(['dateFrom' => $this->getDateFrom()->format('Y-m-d'), 'logger' => function () {
      echo '.';
    }]))->execute();
  }

  private function compare()
  {
    $this->log("\nCompare...\n");

    $dateTime = $this->getDateFrom();
    $dateTimeTo = $this->getDateTo();

    $dataOld = $this->getDataOld();
    $dataNew = $this->getDataNew();

    while ($dateTime <= $dateTimeTo) {
      $date = $dateTime->format('Y-m-d');
      foreach (['rub', 'usd', 'eur'] as $currency) {
        $oldProfit = (float)ArrayHelper::getValue($dataOld, "$date.profit_$currency");
        $newProfit = (float)ArrayHelper::getValue($dataNew, "$date.profit_$currency");
        $difference = $oldProfit - $newProfit;
        if (!$oldProfit && !$newProfit) continue;
        $this->log($dateTime->format('Y-m-d') . " $currency: "
          . (!$difference ? "true ($oldProfit)" : "false (old $oldProfit - new $newProfit = $difference)") . "\n");
      }

      $dateTime->modify('+1 day');
    }
  }

  /**
   * @return array[]
   */
  private function getDataOld()
  {
    return $this->getData('reseller_profits_old');
  }

  /**
   * @return array[]
   */
  private function getDataNew()
  {
    return $this->getData('reseller_profits');
  }

  /**
   * @param string $tableName
   * @return array[]
   */
  private function getData($tableName)
  {
    return (new Query)
      ->select(['date', 'SUM(profit_rub) as profit_rub', 'SUM(profit_usd) as profit_usd', 'SUM(profit_eur) as profit_eur'])
      ->from($tableName)
      ->andWhere(['>=', 'date', $this->getDateFrom()->format('Y-m-d')])
      ->andWhere(['<=', 'date', $this->getDateTo()->format('Y-m-d')])
      ->orderBy(['date' => SORT_ASC])
      ->groupBy('date')
      ->indexBy('date')
      ->all();
  }

  /**
   * @param $message
   * @param bool $breakAfter
   * @param bool $breakBefore
   */
  private function log($message, $breakAfter = true, $breakBefore = false)
  {
    call_user_func($this->logger, $message, $breakAfter, $breakBefore);
  }
}