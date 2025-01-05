<?php

namespace mcms\promo\actions;
use mcms\common\actions\ModelActionAbstract;
use mcms\promo\models\AdsType;

/**
 * Class UpdateAdsTypesModalAction
 * @package mcms\common\actions
 */
class UpdateAdsTypesModalAction extends ModelActionAbstract
{

  /**
   * @param $id
   * @return array|bool
   * @throws \yii\web\NotFoundHttpException
   */
  public function run($id)
  {
    $model = $this->getModel($id);
    if (!$model instanceof AdsType || !$model->canView()) return false;

    return $this->handleAjaxForm($model);
  }


}
