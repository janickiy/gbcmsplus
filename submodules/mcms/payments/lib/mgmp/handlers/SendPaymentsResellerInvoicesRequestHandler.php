<?php

namespace mcms\payments\lib\mgmp\handlers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * Реселлерские инвойсы выплат
 */
class SendPaymentsResellerInvoicesRequestHandler
{
  public function handle($params)
  {
    $paymentsIds = ArrayHelper::getValue($params, 'paymentsIds', []);

    $invoices = [];
    $invoicesQuery = UserBalanceInvoice::find()
    ->andWhere(['user_id' => UserPayment::getResellerId()])
    ->andWhere(['user_payment_id' => $paymentsIds]);

    /** @var UserBalanceInvoice $invoice */
    foreach ($invoicesQuery->each() as $invoice) {
      $invoices[] = [
        'id' => $invoice->id,
        'payment_id' => $invoice->user_payment_id,
        'currency' => $invoice->currency,
        'amount' => $invoice->amount,
        'created_at' => $invoice->created_at
      ];
    }

    return AjaxResponse::success($invoices);
  }
}
