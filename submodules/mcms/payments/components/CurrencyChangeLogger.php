<?php

namespace mcms\payments\components;


use mcms\payments\models\CurrencyLog;
use mcms\payments\models\UserPaymentSetting;
use Yii;
use yii\base\Exception;

/**
 * Логирование смены валюты партнера в таблицу currency_log
 */
class CurrencyChangeLogger
{
  /**
   * @var UserPaymentSetting
   */
  public $userPaymentSetting;

  /**
   * CurrencyChangeLogger constructor.
   * @param UserPaymentSetting $userPaymentSetting
   */
  public function __construct(UserPaymentSetting $userPaymentSetting)
  {
    $this->userPaymentSetting = $userPaymentSetting;
  }

  /**
   * @param string $oldCurrency предыдущая валюта
   * @return bool
   */
  public function log($oldCurrency)
  {
    $errorMessage = "Не удалось залогировать смену валюты партнером #{$this->userPaymentSetting->user_id}. Старая валюта - $oldCurrency, новая - {$this->userPaymentSetting->currency}, время - " . time();
    $transaction = Yii::$app->db->beginTransaction();
    try {
      // tricky: Если это первая смена валюты партнера фиксируем первоначальную валюту
      $currencyChangesExists = CurrencyLog::find()->where(['user_id' => $this->userPaymentSetting->user_id])->exists();
      if (!$currencyChangesExists) {
        $startLog = new CurrencyLog(['user_id' => $this->userPaymentSetting->user_id, 'currency' => $oldCurrency, 'created_at' => 0]);
        if (!$startLog->save()) {
          Yii::error($errorMessage, __METHOD__);
          $transaction->rollBack();
          return false;
        }
      }
      // Логирование смены валюты
      $log = new CurrencyLog(['user_id' => $this->userPaymentSetting->user_id, 'currency' => $this->userPaymentSetting->currency]);
      if (!$log->save()) {
        Yii::error($errorMessage, __METHOD__);
        $transaction->rollBack();
        return false;
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      return false;
    }
    return true;
  }
}