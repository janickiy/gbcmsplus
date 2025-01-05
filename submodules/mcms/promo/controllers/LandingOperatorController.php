<?php

namespace mcms\promo\controllers;

use mcms\promo\models\LandingOperator;
use mcms\common\controller\AdminBaseController;
use mcms\promo\models\LandingOperatorPayType;
use yii\web\NotFoundHttpException;
use mcms\common\web\AjaxResponse;

/**
 * LandingOperatorController implements the CRUD actions for LandingOperator model.
 */
class LandingOperatorController extends AdminBaseController
{
  /**
   * @param $landingId
   * @param $operatorId
   * @return array
   */
  public function actionDelete($landingId, $operatorId)
  {
    $model = $this->findModel($landingId, $operatorId);

    foreach ($model->landingOperatorPayTypes as $payType) {
      /** @var LandingOperatorPayType $payType */
      $payType->delete();
    }

    return AjaxResponse::set($model->delete());
  }

  /**
   * @param integer $landingId
   * @param integer $operatorId
   * @return LandingOperator
   * @throws NotFoundHttpException
   */
  protected function findModel($landingId, $operatorId)
  {
    $model = LandingOperator::findOne(['landing_id' => $landingId, 'operator_id' => $operatorId]);
    if ($model !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
