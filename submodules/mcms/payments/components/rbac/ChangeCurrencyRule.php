<?php

namespace mcms\payments\components\rbac;

use mcms\common\helpers\ArrayHelper;
use mcms\payments\components\api\UserBalance;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\promo\Module;
use Yii;
use yii\base\InvalidParamException;
use yii\rbac\Rule;

class ChangeCurrencyRule extends Rule
{
  public $name = 'ChangeCurrencyRule';
  public $description = 'Can change currency';
  private $error = null;

  /**
   * @inheritdoc
   */
  public function execute($user, $item, $params)
  {
    $this->error = null;

    $userId = ArrayHelper::getValue($params, 'userId');
    $currency = ArrayHelper::getValue($params, 'currency');

    if (!$userId) {
      throw new InvalidParamException('Не передан обязательный параметр userId');
    }

    /** @var Module $promoModule */
    $promoModule = Yii::$app->getModule('promo');

    if ($currency && !$promoModule->isCurrencyAvailable($currency)) {
      $this->error = Yii::_t('payments.settings.currency_change_currency_blocked', ['currency' => $currency]);
      return false;
    }

    /** @var UserBalance $balance */
    $balance = Yii::$app->getModule('payments')
      ->api('userBalance', ['userId' => $userId])
      ->getResult();

    // MCMS-843 Запрещаем менять валюту если баланс отрицательный
    if ($balance->getMain() < 0) {
      $this->error = Yii::_t('payments.settings.currency_change_negative_balance');
      return false;
    }

    // При смене валюты другого пользователя
    if (Yii::$app->user->id != $userId) {
      // MCMS-1108 Запрещаем менять валюту если баланс не нулевой
      if ($balance->getMain() != 0) {
        $this->error = Yii::_t('payments.settings.currency_change_not_zero_balance');
        return false;
      }

      // Изменение валюты невозможна если есть хотя бы одна выплата
      if (UserPayment::find()->where(['user_id' => $userId])->count()) {
        $this->error = Yii::_t('payments.settings.currency_change_payments_exists');

        return false;
      }
    }

    return !UserPaymentSetting::fetch($userId)->isCurrencyChanged();
  }

  /**
   * Получить последнюю ошибку
   * TRICKY Не учитывает проверку пермишена @see UserPaymentSetting::canChangeCurrency()
   * @param $params
   * @return string|null
   */
  public function getLastError($params)
  {
    $this->execute(Yii::$app->user->identity, null, $params);

    return $this->error;
  }
}