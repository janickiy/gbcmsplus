<?php


namespace mcms\statistic\components\columnstore\checkers;

use mcms\statistic\components\columnstore\BaseChecker;
use Yii;

/**
 * Проверяем onetimes
 */
class Onetimes extends BaseChecker
{

  /**
   * @inheritdoc
   */
  public function getInnoDbCount()
  {
    return (int) Yii::$app->db
      ->createCommand('select count(1) from onetime_subscriptions where date between :dateFrom AND :dateTo')
      ->bindValues([
        ':dateFrom' => $this->dateFrom,
        ':dateTo' => $this->dateTo,
      ])
      ->queryScalar();
  }

  /**
   * @inheritdoc
   */
  public function getColumnStoreCount()
  {
    return (int) Yii::$app->dbCs
      ->createCommand('select count(*) from onetime_subscriptions where date between :dateFrom AND :dateTo')
      ->bindValues([
        ':dateFrom' => $this->dateFrom,
        ':dateTo' => $this->dateTo,
      ])
      ->queryScalar();
  }

  /**
   * @inheritdoc
   */
  public function getColumnStoreDuplicatesCount()
  {
    return (int) Yii::$app->dbCs
      ->createCommand('
        select count(*) from (
          select count(*) as dup
          from onetime_subscriptions
          where date between :dateFrom AND :dateTo
          group by hit_id
          having dup > 1
        ) c')
      ->bindValues([
        ':dateFrom' => $this->dateFrom,
        ':dateTo' => $this->dateTo,
      ])
      ->queryScalar();
  }
}
