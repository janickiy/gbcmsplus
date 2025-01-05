<?php

namespace mcms\promo\controllers;

use Yii;
use mcms\promo\models\LandingPayType;
use mcms\promo\models\search\LandingPayTypeSearch;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;
use mcms\common\web\AjaxResponse;

/**
 * LandingPayTypesController implements the CRUD actions for LandingPayType model.
 */
class LandingPayTypesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  /**
   * Lists all LandingPayType models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new LandingPayTypeSearch(['scenario' => LandingPayTypeSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  /**
   * Updates an existing LandingPayType model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  /**
   * @param \mcms\promo\models\LandingPayType $model
   * @return array|string
   */
  private function handleAjaxForm($model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model
    ]);
  }


  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setEnabled();
    return AjaxResponse::set($model->save());
  }

  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setDisabled();
    return AjaxResponse::set($model->save());
  }

  /**
   * Finds the LandingPayType model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingPayType the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingPayType::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
