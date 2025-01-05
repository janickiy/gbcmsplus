<?php

namespace mcms\payments\components\api;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\payments\models\UserBalanceInvoice;
use Yii;

/**
 * Компонент для перевода средств из одной валюту в другую.
 * TRICKY Компонент не конвертирует средства
 */
class UserBalanceTransfer extends ApiResult
{
  private $userId;
  /** @var string Валюта из которой нужно произвести конвертацию средств */
  private $currencyFrom;
  /** @var string Валюта в которую нужно произвести конвертацию средств */
  private $currencyTo;
  /** @var number Сумма, которую нужно списать со счета в одной валюте */
  private $amountFrom;
  /** @var number Сумма, которую нужно зачислить на счет в другой валюте */
  private $amountTo;
  private $scenarioIncrease;
  private $scenarioDecrease;


  private $oldGroupedProfits;
  private $convertedGroupedProfits;

  public function init($params = [])
  {
    if (!$this->userId = ArrayHelper::remove($params, 'userId')) {
      $this->addError('Missing required param "userId"');
    }
    if (!$this->currencyFrom = ArrayHelper::remove($params, 'currencyFrom')) {
      $this->addError('Missing required param "currencyFrom"');
    }
    if (!$this->currencyTo = ArrayHelper::remove($params, 'currencyTo')) {
      $this->addError('Missing required param "currencyTo"');
    }
    if (!($this->oldGroupedProfits = ArrayHelper::remove($params, 'oldGroupedProfits'))) {
      $this->addError('Invalid param "oldGroupedProfits"');
    }
    if (!($this->convertedGroupedProfits = ArrayHelper::remove($params, 'convertedGroupedProfits'))) {
      $this->addError('Invalid param "convertedGroupedProfits"');
    }

    if (!$this->scenarioIncrease = ArrayHelper::remove($params, 'scenarioIncrease')) {
      $this->addError('Missing required param "scenarioIncrease"');
    }
    if (!$this->scenarioDecrease = ArrayHelper::remove($params, 'scenarioDecrease')) {
      $this->addError('Missing required param "scenarioDecrease"');
    }
  }

  /**
   * TODO: покрыть тестами
   * @return bool
   */
  public function getResult()
  {
    if ($this->getErrors()) return false;

    $transaction = Yii::$app->db->beginTransaction();

    try {
      foreach ($this->oldGroupedProfits as $countryId => $dates) {
        foreach ($dates as $date => $amountFrom) {

          // Если $amountFrom = 0, инвойс не создается, так как не имеет смысла
          if ($amountFrom > 0) {
            /** Списываем со счета сумму в старой валюте */
            if (!$this->decreaseBalance($this->currencyFrom, abs($amountFrom), $countryId, $date)) {
              throw new ModelNotSavedException('Не удалось списать со счета сумму в старой валюте');
            }
          } else if ($amountFrom < 0) {
            // TRICKY: Отрицательное значение может быть в случае наличия отрицательных инвойсов (например штраф)
            /** Добавляем компенсацию на счет в старой валюте */
            if (!$this->increaseBalance($this->currencyFrom, abs($amountFrom), $countryId, $date)) {
              throw new ModelNotSavedException('Не удалось добавить компенсацию на счет в старой валюте');
            }
          }
          $amountTo = ArrayHelper::getValue($this->convertedGroupedProfits, $countryId . '.' . $date);

          // TRICKY Если $amountTo = 0, инвойс не создается, так как не имеет смысла
          if ($amountTo > 0) {
            /** Добавляем на счет сумму в новой валюте */
            if (!$this->increaseBalance($this->currencyTo, abs($amountTo), $countryId, $date)) {
              throw new ModelNotSavedException('Не удалось добавить на счет сумму в новой валюте');
            }
          } else if ($amountTo < 0) {
            // TRICKY: Отрицательное значение может быть в случае наличия отрицательных инвойсов (например штраф)
            /** Списываем со счета сумму в новой валюте */
            if (!$this->decreaseBalance($this->currencyTo, abs($amountTo), $countryId, $date)) {
              throw new ModelNotSavedException('Не удалось списать со счета сумму в новой валюте');
            }
          }
        }
      }
      $transaction->commit();
    } catch (\Exception $e) {
      Yii::error($e->getMessage(), __METHOD__);
      $transaction->rollBack();
      return false;
    }

    return true;
  }

  /**
   * @return \mcms\user\models\User
   */
  public function getUser()
  {
    return Yii::$app->getModule('users')->api('user')->getModelByPk($this->userId);
  }

  /**
   * @return string
   */
  public function getCurrencyFrom()
  {
    return $this->currencyFrom;
  }

  /**
   * @return string
   */
  public function getCurrencyTo()
  {
    return $this->currencyTo;
  }

  /**
   * @return number
   */
  public function getAmountFrom()
  {
    if ($this->amountFrom) return $this->amountFrom;
    $this->amountFrom = 0;
    foreach($this->oldGroupedProfits as $dates) {
      $this->amountFrom += array_sum($dates);
    }
    return $this->amountFrom;
  }

  /**
   * @return number
   */
  public function getAmountTo()
  {
    if ($this->amountTo) return $this->amountTo;
    $this->amountTo = 0;
    foreach($this->convertedGroupedProfits as $dates) {
      $this->amountTo += array_sum($dates);
    }
    return $this->amountTo;
  }

  /**
   * @param $currency
   * @param $amount
   * @param $countryId
   * @param $date
   * @return bool
   */
  private function decreaseBalance($currency, $amount, $countryId, $date)
  {
    $invoice = new UserBalanceInvoice([
      'user_id' => $this->userId,
      'currency' => $currency,
      'amount' => $amount,
      'country_id' => $countryId,
      'date' => $date,
    ]);
    $invoice->setScenario($this->scenarioDecrease);

    if (!$invoice->save()) {
      foreach ($invoice->errors as $error) {
        $this->addError($error);
      }
      return false;
    }

    return true;
  }

  /**
   * @param $currency
   * @param $amount
   * @param $countryId
   * @param $date
   * @return bool
   */
  private function increaseBalance($currency, $amount, $countryId, $date)
  {
    $invoice = new UserBalanceInvoice([
      'user_id' => $this->userId,
      'currency' => $currency,
      'amount' => $amount,
      'country_id' => $countryId,
      'date' => $date,
    ]);
    $invoice->setScenario($this->scenarioIncrease);

    if (!$invoice->save()) {
      foreach ($invoice->errors as $error) {
        $this->addError($error);
      }
      return false;
    }

    return true;
  }
}