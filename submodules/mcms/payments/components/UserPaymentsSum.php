<?php

namespace mcms\payments\components;

use mcms\payments\models\UserPayment;
use yii\db\Expression as Exp;
use yii\db\Query;

/**
 * суммы по выплатам
 */
class UserPaymentsSum
{
  private $userId;

  /**
   * @param $userId
   */
  public function __construct($userId)
  {
    $this->userId = $userId;
  }

  /**
   * получение разных сумм, сгруппированных по валюте выплаты
   * (на данный момент реализовано только получение суммы выполненных выплат)
   * @return array
   */
  public function getGroupedByPaymentCurrency()
  {
    $query = new Query();
    $query
      ->select([
        'currency' => 'currency',
        'payCompletedSum' => new Exp('SUM(IF(status = :completed, amount, 0))'),
      ])
      ->from(UserPayment::tableName())
      ->andWhere(['user_id' => $this->userId])
      ->params([
        ':completed' => UserPayment::STATUS_COMPLETED
      ])
      ->groupBy('currency');

    return $query->all();
  }

  /**
   * получение разных сумм, сгруппированных по валюте баланса юзера в момент выплаты
   * (на данный момент реализовано только получение суммы списаний с баланса)
   * @return array
   */
  public function getGroupedByInvoiceCurrency()
  {
    $query = new Query();
    $query
      ->select([
        'currency' => 'invoice_currency',
        'chargedSum' => new Exp('SUM(invoice_amount)'),
      ])
      ->from(UserPayment::tableName())
      ->andWhere(['user_id' => $this->userId])
      ->groupBy('invoice_currency');

    return $query->all();
  }
}
