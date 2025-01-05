<?php

namespace mcms\payments\components\api;


use mcms\common\module\api\ApiResult;
use mcms\payments\components\UserBalance as UserBalanceComponent;
use yii\helpers\ArrayHelper;

class UserBalance extends ApiResult
{

  public $userId;
  public $currency;
  /** @var UserBalanceComponent */
  private $result;

  function init($params = [])
  {
    if (!$this->userId = ArrayHelper::getValue($params, 'userId')) {
      $this->addError('userId required');
    }
    $this->currency = ArrayHelper::getValue($params, 'currency');
  }

  private function setResult()
  {
    $this->result = $this->getResult();
  }

  public function getResult()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) {
      $this->result = new UserBalanceComponent(['userId' => $this->userId, 'currency' => $this->currency]);
    }
    return $this->result;
  }

  public function getBalance()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getBalance();
  }

  public function getMain()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getMain();
  }

  public function getHold()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getHold();
  }

  /**
   * Возвращает профиты пользователя, находящиеся в холде, сгруппированные по дате и стране
   * Используется при конвертации, для создания соответствующих инвойсов
   *
   * Возвращает массив вида $result[country_id][date] = balance
   *
   * @return array|bool
   */
  public function getGroupedBalance()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getGroupedBalance();
  }

  public function getCurrency()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getCurrency();
  }

  public function getCurrencyLabel()
  {
    if ($this->getErrors()) {
      return false;
    }
    return $this->result->getCurrencyLabel();
  }

  public function getPaymentSettings()
  {
    if ($this->getErrors()) {
      return false;
    }
    if ($this->result === null) $this->setResult();
    return $this->result->getPaymentSettings();
  }
}
