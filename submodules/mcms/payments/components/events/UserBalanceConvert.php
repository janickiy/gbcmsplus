<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\components\api\UserBalanceTransfer;
use Yii;

/**
 * Уведомление о конвертации баланса
 */
class UserBalanceConvert extends Event
{
  /** @var UserBalanceTransfer */
  private $transfer;

  /**
   * @inheritdoc
   */
  function __construct($transfer = null)
  {
    $this->transfer = $transfer;
  }

  /**
   * @inheritdoc
   */
  public function getOwner()
  {
    return $this->transfer->getUser();
  }

  /**
   * @inheritdoc
   */
  function getEventName()
  {
    return Yii::_t('payments.events.user-balance-convert');
  }

  /**
   * @inheritdoc
   */
  public function getAdditionalReplacements()
  {
    return $this->transfer ? [
      'transfer.amountFrom' => $this->transfer->getAmountFrom(),
      'transfer.currencyFrom' => strtoupper($this->transfer->getCurrencyFrom()),
      'transfer.amountTo' => $this->transfer->getAmountTo(),
      'transfer.currencyTo' => strtoupper($this->transfer->getCurrencyTo()),
    ] : [];
  }
  
  public static function getUrl($id = null)
  {
    return ['/partners/payments/balance/'];
  }
}