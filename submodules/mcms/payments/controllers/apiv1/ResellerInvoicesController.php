<?php

namespace mcms\payments\controllers\apiv1;

use mcms\payments\models\UserPayment;
use rgk\utils\actions\DownloadFileAction;
use mcms\common\mgmp\ApiController;

/**
 * Получить invoice_file из выплаты
 */
class ResellerInvoicesController extends ApiController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return parent::actions() + [
        'download-file' => [
          'class' => DownloadFileAction::class,
          'modelClass' => UserPayment::class,
          'attribute' => 'invoice_file',
        ],
      ];
  }
}