<?php

namespace mcms\payments\dbfix;

use Yii;
use console\components\Migration;
use yii\helpers\ArrayHelper;
use mcms\user\Module as UserModule;
use yii\helpers\Console;

class unhold extends Migration
{
  const UBI_TABLE = 'user_balance_invoices';
  const UBGD_TABLE = 'user_balances_grouped_by_day';

  public function up()
  {
    $rootIds = Yii::$app->authManager->getUserIdsByRole(UserModule::ROOT_ROLE);
    $rootId = end($rootIds);

    // Ищем все нерасхолдированные доходы
    $sql = <<<SQL
SELECT
  ubgd.user_id,
  IFNULL(ubgd.currency, ubi.currency)            AS currency,
  IFNULL(ubgd.profit, 0) + IFNULL(ubi.amount, 0) AS hold_sum
FROM (
       SELECT
         bal.user_id,
         SUM(IF(user_currency = 'rub', profit_rub,
                IF(user_currency = 'usd', profit_usd, IF(user_currency = 'eur', profit_eur, 0)))) AS profit,
         bal.user_currency                                                                            AS currency
       FROM user_balances_grouped_by_day bal
       INNER JOIN auth_assignment aa ON aa.user_id = bal.user_id
       WHERE is_hold = 1 AND aa.item_name='partner'
       GROUP BY user_currency, user_id
     ) ubgd
  LEFT JOIN
  (
    SELECT
      inv.user_id,
      SUM(inv.amount) AS amount,
      inv.currency
    FROM user_balance_invoices inv
  INNER JOIN auth_assignment aa ON aa.user_id = inv.user_id
    WHERE is_hold = 1 AND aa.item_name='partner'
    GROUP BY currency, user_id
  ) ubi
    ON ubgd.user_id = ubi.user_id AND ubgd.currency = ubi.currency
HAVING hold_sum > 0;
SQL;
    $holds = $this->db->createCommand($sql)->queryAll();
    $date = date('Y-m-d');
    // Компенсируем партнерам их нерасхолдированные доходы
    foreach ($holds as $hold) {
      $userId = ArrayHelper::getValue($hold, 'user_id');
      $currency = ArrayHelper::getValue($hold, 'currency');
      $holdSum = ArrayHelper::getValue($hold, 'hold_sum');

      if (!Console::confirm("У юзера #$userId нашли холд $holdSum $currency, перевести из холда в обычный счет?")) {
        continue;
      }

      $this->insert(self::UBI_TABLE, [
        'user_id' => $userId,
        'currency' => $currency,
        'amount' => $holdSum,
        'description' => 'Автоматический расхолд',
        'created_at' => time(),
        'date' => $date,
        'created_by' => $rootId,
        'type' => 8
      ]);
    }
  }

}
