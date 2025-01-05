<?php

namespace mcms\payments\models\forms;


use mcms\common\helpers\ArrayHelper;
use mcms\currency\components\ResellerCurrenciesProvider;
use mcms\payments\components\api\ExchangerCourses;
use mcms\payments\components\UserBalance;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use Yii;
use yii\base\Model;

/**
 * Форма для конвертации баланса реселлера
 */
class ResellerConvertForm extends Model
{
  public $amountFrom;
  public $currencyFrom;
  public $currencyTo;

  private $_resellerBalanse = [];

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return [
      static::SCENARIO_DEFAULT => ['amountFrom', 'currencyFrom', 'currencyTo'],
    ];
  }

  /**
   * @inheritDoc
   */
  public function rules()
  {
    return [
      [['amountFrom', 'currencyFrom', 'currencyTo'], 'required'],
      [['amountFrom'], 'number', 'min' => 1],
      [['currencyFrom', 'currencyTo'], 'string'],
      [['currencyFrom', 'currencyTo'], function ($attribute) {
        if ($this->currencyFrom === $this->currencyTo) {
          $this->addError($attribute, Yii::_t('payments.reseller-convert-form.same-currencies-error'));
          return false;
        }
        return true;
      }],
      ['amountFrom', function ($attribute) {
        if (!$this->currencyFrom) return true;
        if ($this->getResellerBalance() <= 0) {
          $this->addError($attribute, Yii::_t('payments.reseller-convert-form.empty-balance'));
          return false;
        }
        if ($this->amountFrom > $this->getResellerBalance()) {
          $this->addError($attribute, Yii::_t('payments.reseller-convert-form.much-error', [
            'sum' => $this->getResellerBalance()
          ]));
          return false;
        }
        return true;
      }],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'amountFrom' => Yii::_t('payments.reseller-convert-form.amountFrom'),
      'currencyFrom' => Yii::_t('payments.reseller-convert-form.currencyFrom'),
      'currencyTo' => Yii::_t('payments.reseller-convert-form.currencyTo'),
    ];
  }

  /**
   * Список валют
   * @return array
   */
  public function getCurrencies()
  {
    return $currencies = [
      'rub' => Yii::_t('payments.reseller-profit-log.rub'),
      'usd' => Yii::_t('payments.reseller-profit-log.usd'),
      'eur' => Yii::_t('payments.reseller-profit-log.eur')
    ];
  }

  /**
   * сохранение конвертации
   * @return bool
   */
  public function save()
  {
    if (!$this->validate()) return false;

    // Списание
    $modelDecrease = new UserBalanceInvoice([
      'user_id' => UserPayment::getResellerId(),
      'currency' => $this->currencyFrom,
      'amount' => $this->amountFrom * -1, // tricky: Уменьшение баланса при конвертации должно быть отрицательным числом
      'type' => UserBalanceInvoice::TYPE_CONVERT_DECREASE,
      'description' => 'Reseller balance convert - decrease',
    ]);

    // Начисление
    $modelIncrease = new UserBalanceInvoice([
      'user_id' => UserPayment::getResellerId(),
      'currency' => $this->currencyTo,
      'amount' => $this->calcConvert(),
      'type' => UserBalanceInvoice::TYPE_CONVERT_INCREASE,
      'description' => 'Reseller balance convert - increase',
    ]);

    $transaction = Yii::$app->db->beginTransaction();
    try {
      if ($modelDecrease->save() && $modelIncrease->save()) {
        $transaction->commit();
        return true;
      }
      $transaction->rollBack();
      return false;
    } catch (\Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  /**
   * Рассчитать результат конвертации amountFrom в валюту currencyTo
   * @return float|bool
   */
  public function calcConvert()
  {
    if (!$this->currencyFrom || !$this->amountFrom || !$this->currencyTo) return false;

    return ResellerCurrenciesProvider::getInstance()
      ->getCurrencies()
      ->getCurrency($this->currencyFrom)
      ->convert($this->amountFrom, $this->currencyTo);
  }

  /**
   * Баланс реселлера в валюте currencyFrom
   * @return float
   */
  public function getResellerBalance()
  {
    if(isset($this->_resellerBalanse[$this->currencyFrom])) return $this->_resellerBalanse[$this->currencyFrom];
    return $this->_resellerBalanse[$this->currencyFrom] = (new UserBalance(['userId' => UserPayment::getResellerId(), 'currency' => $this->currencyFrom]))->getResellerBalance();
  }
}