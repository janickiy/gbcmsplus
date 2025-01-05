<?php

namespace mcms\loyalty\controllers\apiv1;

use mcms\common\mgmp\ApiController;
use mcms\loyalty\components\LoyaltyBonusImport;
use rgk\utils\components\response\AjaxResponse;
use Yii;

/**
 * API для добавления задачи по обновлению бонусов реселлера в очередь
 */
class BonusesController extends ApiController
{
  /**
   * @return array
   * @throws \yii\base\InvalidParamException
   */
  public function actionImport()
  {
    $import = new LoyaltyBonusImport(['mgmpBonus' => Yii::$app->request->post('bonus')]);
    return AjaxResponse::set($import->execute());
  }
}