<?php

namespace mcms\payments\components\events;


use mcms\common\event\Event;
use mcms\payments\models\UserBalanceInvoice;
use Yii;

class UserBalanceInvoiceCompensation extends Event
{

  /** @var  UserBalanceInvoice */
  public $userBalanceInvoice;

  /**
   * @inheritDoc
   */
  function __construct(UserBalanceInvoice $userBalanceInvoice = null)
  {
    $this->userBalanceInvoice = $userBalanceInvoice;
  }

  public function getOwner()
  {
    return $this->userBalanceInvoice->user;
  }

  public function getModelId()
  {
    return $this->userBalanceInvoice->id;
  }

  public static function getUrl($id = null)
  {
    $currentUser = Yii::$app->user;

    if ($currentUser->can('PaymentsResellerInvoicesIndex')) {
      return ['/payments/reseller-invoices/index/'];
    }
    if ($currentUser->can('PartnersPaymentsBalance')) {
      return ['/partners/payments/balance/'];
    }

    return Event::getUrl($id);
  }

  function getEventName()
  {
    return Yii::_t('payments.events.user-balance-invoice-compensation');
  }
}