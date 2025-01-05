<?php

namespace mcms\payments\controllers\apiv1;

use mcms\common\web\AjaxResponse;
use mcms\payments\components\InvoicesExport;
use Yii;

/**
 * Инвойсы
 */
class InvoicesController extends ApiController
{
  /**
   * Экспорт инвойсов
   * @param int[] $types
   * @param int|null $dateFrom
   * @return array
   */
  public function actionExport(array $types, $dateFrom = null)
  {
    $export = new InvoicesExport;
    $export->types = $types;
    $export->dateFrom = $dateFrom;

    $invoices = $export->getInvoices();
    return $invoices === false
      ? AjaxResponse::error($export->getFirstErrors())
      : AjaxResponse::success($invoices);
  }
}