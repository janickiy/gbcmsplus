<?php

namespace mcms\promo\controllers;

use mcms\promo\models\UserOperatorTrafficFiltersOff;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\DeleteAjaxAction;
use rgk\utils\actions\UpdateModalAction;
use mcms\common\controller\AdminBaseController;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use rgk\utils\components\response\AjaxResponse;
use yii\widgets\ActiveForm;

/**
 * Отключение фильтров трафика по партнеру+оператору
 */
class TrafficFiltersOffController extends AdminBaseController
{

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'delete' => [
        'class' => DeleteAjaxAction::class,
        'modelClass' => UserOperatorTrafficFiltersOff::class,
      ]
    ];
  }

  /**
   * @param $userId
   * @return array|string
   */
  public function actionCreateModal($userId)
  {
    $model = new UserOperatorTrafficFiltersOff(['user_id' => $userId]);

    return $this->handleAjaxForm($model);
  }

  /**
   * @param $id
   * @return array|string
   */
  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  /**
   * @param UserOperatorTrafficFiltersOff $model
   * @return array|string
   */
  private function handleAjaxForm($model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model,
    ]);
  }

  /**
   * Finds the TrafficBlock model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return UserOperatorTrafficFiltersOff the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = UserOperatorTrafficFiltersOff::findOne($id)) !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
