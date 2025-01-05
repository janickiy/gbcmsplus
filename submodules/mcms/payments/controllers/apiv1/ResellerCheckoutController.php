<?php

namespace mcms\payments\controllers\apiv1;

use mcms\common\web\AjaxResponse;
use mcms\payments\lib\mgmp\handlers\SendPaymentsRequestHandler;
use mcms\payments\lib\mgmp\handlers\SendPaymentsResellerInvoicesRequestHandler;
use mcms\payments\lib\mgmp\handlers\SendProfitsRequestHandler;

/**
 * Class ResellerCheckoutController
 * @package mcms\payments\controllers
 */
class ResellerCheckoutController extends ApiController
{
  /**
   * Endpoint получения выплат которые должны быть обработаны на mgmp
   * @param $from_time
   * @return array
   */
  public function actionGetPayments($from_time)
  {
    $handler = new SendPaymentsRequestHandler;

    $requestData = ['from_time' => $from_time];

    if (empty($requestData)) {
      return AjaxResponse::error('Request data error');
    }

    return $handler->handle($requestData);
  }

  /**
   * Реселлерские инвойсы выплат
   * @return array
   */
  public function actionGetPaymentsResellerInvoices()
  {
    $handler = new SendPaymentsResellerInvoicesRequestHandler;
    $paymentsIds = \Yii::$app->request->post('paymentsIds');

    if (!is_array($paymentsIds)) {
      return AjaxResponse::error('Invalid param paymentsIds');
    }

    return $handler->handle(['paymentsIds' => $paymentsIds]);
  }

  public function actionWeekProfit($from_date)
  {
    $handler = new SendProfitsRequestHandler;

    $requestData = ['from_date' => $from_date];

    if (empty($requestData)) {
      return AjaxResponse::error('Request data error');
    }
    return $handler->handle($requestData);
  }
}