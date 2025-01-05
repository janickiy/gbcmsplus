<?php

namespace mcms\payments\controllers\apiv1;

use mcms\payments\components\paysystem\PaysystemImport;
use rgk\utils\components\response\AjaxResponse;
use Yii;

/**
 * Управление платежными системами
 */
class PaysystemsController extends ApiController
{
  /**
   * Импорт данных о платежной системе из МГМП
   * @return array
   */
  public function actionImport()
  {
    $import = new PaysystemImport;
    $import->mgmpPaysystem = Yii::$app->request->post('paysystem');
    $import->isMgmpPaymentsAvailable = Yii::$app->request->post('isPaymentsAvailable');

    return AjaxResponse::set($import->execute());
  }
}