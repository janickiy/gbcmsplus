<?php

namespace mcms\payments\models\search\dataproviders;

use mcms\payments\models\UserPayment;
use mcms\payments\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class UserPaymentDataProvider
 * Переопределили чтобы можно было менять запрос count для пагинации.
 * Ну и также инкапсулировать логику с сортировкой
 * @package mcms\payments\models\search\dataproviders
 */
class UserPaymentDataProvider extends ActiveDataProvider
{
  public function init()
  {
    /** @var Module $module */
    $module = Yii::$app->getModule('payments');

    $tableName = UserPayment::tableName();
    $level2 = Yii::$app->formatter->asDate("today + {$module->getDelayLevel2()} days", 'php:Y-m-d');
    $level1 = Yii::$app->formatter->asDate("today + {$module->getDelayLevel1()} days", 'php:Y-m-d');

    $this->sort = [
      'attributes' => [

        // TRICKY MCMS-1259
        // ошибка -> отложено красное -> отложено оранжевое -> в ожидании -> отложено бесцветное -> все остальные

        'defaultStatus' => [
          'desc' => [
            // сначала ошибки
            new Expression(
              "$tableName.status = :error DESC",
              [':error' => UserPayment::STATUS_ERROR]
            ),
            // отложено красное
            new Expression(
              "$tableName.status = :delay AND $tableName.pay_period_end_date IS NOT NULL AND FROM_UNIXTIME($tableName.pay_period_end_date) < :level1 DESC",
              [':delay' => UserPayment::STATUS_DELAYED, ':level1' => $level1]
            ),
            // отложено оранжевое
            new Expression(
              "$tableName.status = :delay AND $tableName.pay_period_end_date IS NOT NULL AND FROM_UNIXTIME($tableName.pay_period_end_date) >= :level1 AND FROM_UNIXTIME($tableName.pay_period_end_date) < :level2 DESC",
              [':delay' => UserPayment::STATUS_DELAYED, ':level1' => $level1, ':level2' => $level2]
            ),
            // в ожидании
            new Expression(
              "$tableName.status = :await DESC",
              [':await' => UserPayment::STATUS_AWAITING]
            ),
            // отложено бесцветное
            new Expression(
              "$tableName.status = :delay AND $tableName.pay_period_end_date IS NOT NULL AND FROM_UNIXTIME($tableName.pay_period_end_date) >= :level2 DESC",
              [':delay' => UserPayment::STATUS_DELAYED, ':level2' => $level2]
            ),
            // отложено бесцетное, но где вообще не стоит дата до которой отложено
            new Expression(
              "$tableName.status = :delay AND $tableName.pay_period_end_date IS NULL DESC",
              [':delay' => UserPayment::STATUS_DELAYED]
            ),
            // в процессе
            new Expression(
              "$tableName.status = :process DESC",
              [':process' => UserPayment::STATUS_PROCESS]
            ),
            // выполненные
            new Expression(
              "$tableName.status = :done DESC",
              [':done' => UserPayment::STATUS_COMPLETED]
            ),
            // отложенные сортируем по дате откладывания
            new Expression(
              "IF( $tableName.status = :delay, pay_period_end_date, null) ASC",
              [':delay' => UserPayment::STATUS_DELAYED]
            ),
            // остальные по id
            new Expression("$tableName.id DESC"),
          ],
        ],
        'user' => [
          'asc' => [new Expression('users.username ASC')],
          'desc' => [new Expression('users.username DESC')],
        ],
        'created_at',
        'pay_period_end_date' => [
          'desc' => [
            // сначала отложенные
            new Expression(
              "$tableName.status = :delayed DESC, pay_period_end_date DESC",
              [':delayed' => UserPayment::STATUS_DELAYED]
            ),
          ],
          'asc' => [
            // сначала отложенные
            new Expression(
              "$tableName.status = :delayed DESC, pay_period_end_date ASC",
              [':delayed' => UserPayment::STATUS_DELAYED]
            ),
          ],
        ],
        'pay_terms',
        'payed_at',
        'currency',
        'status',
        'wallet_type',
        'id',
        'invoice_amount',
        'amount',
        'processing_type',
        'commission' => [
          'asc' => [new Expression($tableName . '.amount * ' . $tableName . '.reseller_paysystem_percent/100  ASC')],
          'desc' => [new Expression($tableName . '.amount * ' . $tableName . '.reseller_paysystem_percent/100  DESC')],
        ],
      ],
      'defaultOrder' => [
        'defaultStatus' => SORT_DESC,
      ],
    ];
    parent::init();
  }

  /**
   * Вместо SELECT COUNT(*) FROM (SELECT ....) `c`
   * Делаем просто SELECT COUNT(1) FROM user_payments ...
   * То есть без подзапроса
   * @inheritdoc
   */
  protected function prepareTotalCount()
  {
    /** @var Query $query */
    $query = clone $this->query;
    return $query
      ->select([new Expression('COUNT(1)')])
      ->scalar();
  }
}
