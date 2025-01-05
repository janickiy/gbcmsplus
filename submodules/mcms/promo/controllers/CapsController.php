<?php

namespace mcms\promo\controllers;

use mcms\promo\models\ExternalProvider;
use mcms\promo\models\search\CapSearch;
use mcms\statistic\controllers\AbstractStatisticController;
use rgk\utils\actions\IndexAction;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 *
 */
class CapsController extends AbstractStatisticController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => CapSearch::class,
      ],
    ];
  }

  /**
   * @param $id
   * @return array
   * @throws NotFoundHttpException
   */
  public function actionUpdateExternalProvider($id)
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $model = $this->findEPModel($id);
    if (!$model || !$model->load(Yii::$app->request->post()) || !$model->save()) {
      return ['output' => '', 'message' => Yii::_t('promo.caps.external_provider-save_error')];
    }

    return ['output' => $model->local_name, 'message' => ''];
  }

  /**
   * @param $id
   * @return ExternalProvider|null
   * @throws NotFoundHttpException
   */
  private function findEPModel($id)
  {
    $model = ExternalProvider::findOne($id);
    if ($model === null) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }

    return $model;
  }
}
