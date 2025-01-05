<?php
namespace mcms\payments\tests\traits;

use Yii;

/**
 * Хэлпер для тестов выплат
 */
trait PaymentsTrait
{
  /**
   * Очистка всех операций по балансу и установка нужного значения
   * @param int $balance Новый баланс
   * @param string $currency
   * @param int|null $userId
   * @throws \Exception
   */
  public function resetBalance($balance, $currency = 'rub', $userId = null)
  {
    if (!$userId) $userId = Yii::$app->user->id;
    if (!$userId) throw new \Exception('Не удалось определить ID пользователя для сброса баланса');
    Yii::$app->db->createCommand('DELETE FROM user_payments')->execute();
    Yii::$app->db->createCommand('DELETE FROM user_balance_invoices')->execute();
    Yii::$app->db->createCommand('DELETE FROM user_balances_grouped_by_day')->execute();
    Yii::$app->db->createCommand()->insert('user_balance_invoices', ['user_id' => $userId, 'currency' => $currency, 'amount' => $balance])->execute();
  }
}