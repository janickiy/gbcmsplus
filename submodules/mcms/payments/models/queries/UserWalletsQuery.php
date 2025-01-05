<?php

namespace mcms\payments\models\queries;

use mcms\payments\models\UserWallet;
use mcms\payments\models\wallet\Wallet;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Поиск кошельков пользователей
 */
class UserWalletsQuery extends ActiveQuery
{
  /**
   * Учитывать активность платежных систем
   * @param bool|null $activity Активность ПС @see Wallet::find()
   * @return $this
   */
  public function paysystemsActivity($activity)
  {
    if (!is_bool($activity)) return $this;

    if (!$activity) {
      return $this;
    }
    return $this->innerJoin(Wallet::tableName() . ' w', UserWallet::tableName() . '.wallet_type=w.id')
      ->andWhere(
        new Expression('IF(' . UserWallet::tableName() . '.currency = "rub", w.is_rub, IF(' . UserWallet::tableName() . '.currency = "usd", w.is_usd, w.is_eur)) = 1')
      )
      ->andWhere(['w.is_active' => 1]);
  }
}