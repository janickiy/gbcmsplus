<?php

namespace mcms\statistic\controllers\apiv1;

use mcms\common\mgmp\ApiController;
use mcms\common\web\AjaxResponse;
use mcms\statistic\components\ResellerProfits;
use yii\db\Query;

/**
 * Class ResellerProfitsController
 * @package mcms\statistic\controllers
 */
class ResellerProfitsController extends ApiController
{
  /**
   * @param $date
   * @return array
   */
  public function actionIndex($date)
  {
    $data = (new Query())
      ->from(ResellerProfits::tableName())
      ->andWhere(['date' => $date])
      ->all();

    return AjaxResponse::success($data);
  }
}