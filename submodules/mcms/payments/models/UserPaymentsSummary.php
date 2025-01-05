<?php

namespace mcms\payments\models;


use mcms\common\traits\Translate;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class UserBalanceSummary
 * @package mcms\payments\models
 *
 * @property double $week
 * @property double $month
 * @property double $total
 */
class UserPaymentsSummary extends Model
{
  use Translate;
  const LANG_PREFIX = 'payments.user-payments-summary.';

  public $userId;
  public $currency;

  private $totalData;
  private $data;

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [
      [['userId'], 'required']
    ];
  }

  /**
   * @inheritDoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels(['total', 'week', 'month']);
  }

  private function getPayments()
  {
    if (!$this->validate()) return [];
    if ($this->data) return $this->data;

    /** @var UserPayment $model */
    return $this->data = ArrayHelper::map(
      UserPayment::getCompletedPaymentsDateFrom($this->userId, strtotime('first day of this month')),
      'user_id',
      function (UserPayment $model) {
        return $model;
      }
    );
  }

  public function getWeek()
  {
    $date = strtotime('last monday');

    return array_sum(array_map(function (UserPayment $item) use ($date) {
      if ($item->payed_at < $date) return 0;
      if ($item->currency != $this->currency) return 0;
      return $item->amount;
    }, $this->getPayments()));
  }

  public function getMonth()
  {
    return array_sum(array_map(function (UserPayment $item) {
      if ($item->currency != $this->currency) return 0;
      return $item->amount;
    }, $this->getPayments()));
  }

  public function getTotal()
  {
    if (!$this->validate()) return 0;
    if ($this->totalData) return $this->totalData;

    return UserPayment::getCompletePaymentSum($this->userId, $this->currency);
  }

  public function getCurrencyLabel()
  {
    $currencyList = Yii::$app->getModule('promo')->api('mainCurrencies')->setMapParams(['code', 'symbol'])->getMap();

    return ArrayHelper::getValue($currencyList, $this->currency);
  }

}