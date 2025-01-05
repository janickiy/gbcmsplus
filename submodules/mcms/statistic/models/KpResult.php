<?php
namespace mcms\statistic\models;

use yii\helpers\ArrayHelper;

/**
 * Модель результата ответа КП по ОДНОЙ конверсии.
 */
class KpResult
{
  /** @see KpResult::$status */
  const STATUS_OK = 1;
  /** @see KpResult::$status */
  const STATUS_FAIL = 0;

  /**
   * @var int Айди транзакции
   */
  public $transactionId;
  /**
   * @var string Тип транзакции on|off|rebill|onetime
   */
  public $transactionType;
  /**
   * @var int статус
   */
  public $status;

  /**
   * Ответ КП можно воткнуть сюда в виде результата одной конверсии и получить объект класса
   * @param array $result
   * @return KpResult
   */
  public static function getModel(array $result)
  {
    $model = new self();
    $model->transactionId = ArrayHelper::getValue($result, 'transaction_id');
    $model->transactionType = ArrayHelper::getValue($result, 'transaction_type');
    $model->status = ArrayHelper::getValue($result, 'status');
    return $model;
  }

  /**
   * Ответ КП можно воткнуть сюда в виде результата массива конверсий и получить объект класса
   * @param $results
   * @return KpResult[]
   */
  public static function getModels($results)
  {
    $models = [];
    foreach ($results as $result) {
      $models[] = self::getModel($result);
    }
    return $models;
  }
}