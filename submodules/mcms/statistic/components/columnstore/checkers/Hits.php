<?php


namespace mcms\statistic\components\columnstore\checkers;

use mcms\statistic\components\columnstore\BaseChecker;
use Yii;

/**
 * Проверяем хиты
 */
class Hits extends BaseChecker
{

  /**
   * @inheritdoc
   */
  public function getInnoDbCount()
  {
    return (int) Yii::$app->db
      ->createCommand('select count(1) from hits where date between :dateFrom AND :dateTo')
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
      ->createCommand('select count(*) from hits where date between :dateFrom AND :dateTo')
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
    // слишком тяжелый запрос для хитов, не проверяем дубли
    return 0;
  }
}
