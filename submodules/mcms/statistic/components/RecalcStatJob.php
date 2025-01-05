<?php

namespace mcms\statistic\components;

use Yii;

/**
 * Проверка в нобходимости и добавление задач для пересчета статы
 */
class RecalcStatJob
{

  const ALLOWED_RANGE_OF_POSTBACKS = 7;

  /* @var int время конверсии постбека*/
  private $conversionTime;
  private $transId;

  /**
   * @param $conversionTime
   * @param $transId
   */
  public function __construct($conversionTime, $transId)
  {
    $this->conversionTime = $conversionTime;
    $this->transId = $transId;
  }

  /**
   * Нужно ли пересчитать стату
   * @return bool
   */
  protected function isNeedRecalcStat()
  {
    return $this->conversionTime >= Yii::$app->formatter->asTimestamp('-' . self::ALLOWED_RANGE_OF_POSTBACKS . ' days');
  }

  /**
   * Добавить задачу на пересчет статистики
   * @return bool
   */
  public function addRecalcStatJob()
  {
    if (!$this->isNeedRecalcStat()) {
      return false;
    }
    return (bool)Yii::$app->db->createCommand()->insert('statistic_recalc', [
      'trans_id' => $this->transId,
      'time' => $this->conversionTime,
    ])->execute();
  }
}
