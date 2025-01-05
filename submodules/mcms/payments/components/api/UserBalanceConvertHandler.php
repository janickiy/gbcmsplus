<?php

namespace mcms\payments\components\api;


use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\payments\components\events\UserBalanceConvert;
use mcms\payments\models\UserBalanceInvoice;

class UserBalanceConvertHandler extends ApiResult
{
  private $currencies = ['rub', 'usd', 'eur'];

  /**
   * @var int
   */
  private $userId;

  /**
   * Текущий баланс пользователя
   * @var UserBalance
   */
  private $currentUserBalanceApi;

  /**
   * Старый баланс пользователя
   * @var UserBalance|null
   */
  private $oldUserBalanceApi;

  /**
   * @var bool
   */
  private $hasOldBalance = false;

  /**
   * @var bool
   */
  private $hasOldPayments = false;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::remove($params, 'userId')) {
      $this->addError('Missing required param "userId"');
    }

    $this->prepareUserBalanceApi();
  }

  /**
   * @return bool
   */
  public function getResult()
  {
    if ($this->getErrors()) return false;

    if (!$this->oldUserBalanceApi) {
      /** Старый баланс не найден */
      $this->addError('No old balance found');
      return false;
    }

    $defaultTransferParams = [
      'userId' => $this->userId,
      'currencyFrom' => $this->oldUserBalanceApi->getCurrency(),
      'currencyTo' => $this->currentUserBalanceApi->getCurrency(),
      'scenarioIncrease' => UserBalanceInvoice::SCENARIO_CONVERT_INCREASE,
      'scenarioDecrease' => UserBalanceInvoice::SCENARIO_CONVERT_DECREASE,
    ];

    $groupedBalance = $this->oldUserBalanceApi->getGroupedBalance();

    if (!$groupedBalance) {
      return false;
    }

    $converter = new UserBalanceTransfer($defaultTransferParams + [
        'oldGroupedProfits' => $groupedBalance,
        'convertedGroupedProfits' => $this->getGroupedBalanceConverted(),
      ]);

    if (!$converter->getResult()) {
      /** Если не удалось сконвертировать, добавляем ошибки */
      $this->errors = array_merge($this->errors, $converter->getErrors());
      return false;
    }

    (new UserBalanceConvert($converter))->trigger();

    return true;
  }

  /**
   * Метод конвертирует сумму из старого баланса в новую валюту
   * @return int
   */
  public function getGroupedBalanceConverted()
  {
    if (!$this->oldUserBalanceApi) {
      return 0;
    }
    $groupedBalance = $this->oldUserBalanceApi->getGroupedBalance();

    $partnerCurrenciesProvider = PartnerCurrenciesProvider::getInstance();
    $result = [];
    foreach ($groupedBalance as $country => $dates) {
      foreach ($dates as $date => $amount) {
        $result[$country][$date] = round($partnerCurrenciesProvider
          ->getCurrencies()
          ->getCurrency($this->oldUserBalanceApi->getCurrency())
          ->convert($amount, $this->currentUserBalanceApi->getCurrency()), 3);
      }
    }
    return $result;
  }

  /**
   * @return UserBalance
   */
  public function getCurrentUserBalance()
  {
    return $this->currentUserBalanceApi;
  }

  /**
   * @return UserBalance|null
   */
  public function getOldUserBalance()
  {
    return $this->oldUserBalanceApi;
  }

  /**
   * @return bool
   */
  public function getHasOldBalance()
  {
    return $this->hasOldBalance;
  }

  /**
   * @return bool
   */
  public function getHasOldPayments()
  {
    return $this->hasOldPayments;
  }

  /**
   * Метод подготавливает новый баланс и ищет старый
   */
  private function prepareUserBalanceApi()
  {
    // TODO Надо рефакторить
    $userPayments = new UserPayments(['userId' => $this->userId]);
    // TODO API нельзя вызывать на прямую
    $userSettingsData = (new UserSettingsData(['userId' => $this->userId]))->getResult();
    $partnerCurrency = $userSettingsData ? $userSettingsData->currency : null;
    if (!$partnerCurrency) {
      return;
    }
    $this->currentUserBalanceApi = new UserBalance([
      'userId' => $this->userId,
      'currency' => $partnerCurrency
    ]);

    foreach ($this->currencies as $currency) {
      if ($currency == $partnerCurrency) {
        continue;
      }

      $userBalanceApi = new UserBalance(['userId' => $this->userId, 'currency' => $currency]);
      $this->hasOldBalance = $userBalanceApi->getBalance() != 0;
      $this->hasOldPayments = $userPayments->hasAwaitingPayments($currency);

      // Если баланс больше нуля, берем старую валюту
      // TODO Логика дублируется из @see \mcms\payments\models\UserPaymentSetting::getCurrentCurrency(). Нужно избавится от этого
      // TODO При мерже или рефакторинге нужно учесть, что теперь учитывается только наличие денег на балансе в иной валюте. Выплаты игнорируются
      if ($this->hasOldBalance) {
        $this->oldUserBalanceApi = $userBalanceApi;
        return;
      }
    }
  }

  /**
   * Возвращает общий баланс старого счета в новой валюте
   * @return int
   */
  public function getUserNewBalance()
  {
    return $this->getUserNewMainSum();
  }

  /**
   * Метод конвертирует сумму из старого баланса в новую валюту
   * @return int
   */
  public function getUserNewMainSum()
  {
    if (!$this->oldUserBalanceApi) {
      return 0;
    }
    $currencyObj = PartnerCurrenciesProvider::getInstance()->getCurrencies()->getCurrency($this->oldUserBalanceApi->getCurrency());

    $mainSum = $currencyObj->convert($this->oldUserBalanceApi->getMain(), $this->currentUserBalanceApi->getCurrency());
    $holdSum = $currencyObj->convert($this->oldUserBalanceApi->getHold(), $this->currentUserBalanceApi->getCurrency());

    return round($mainSum + $holdSum, 3);
  }
}