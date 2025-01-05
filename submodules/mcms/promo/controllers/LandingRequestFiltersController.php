<?php

namespace mcms\promo\controllers;

use mcms\promo\models\LandingRequestFilter;
use mcms\promo\models\search\LandingRequestFiltersSearch;
use Yii;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;
use mcms\common\web\AjaxResponse;

/**
 * LandingRequestFiltersController implements the CRUD actions for LandingRequestFilter model.
 */
class LandingRequestFiltersController extends AdminBaseController
{
  /**
   * @var string
   */
  public $layout = '@app/views/layouts/main';

  /**
   * Lists all Platform models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new LandingRequestFiltersSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  /**
   * Creates a new LandingRequestFilter model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreateModal()
  {
    return $this->handleAjaxForm(new LandingRequestFilter);
  }

  /**
   * Updates an existing LandingRequestFilter model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);

    return AjaxResponse::set($model->delete());
  }

  /**
   * @param LandingRequestFilter $model
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
   * Finds the LandingRequestFilter model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingRequestFilter the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingRequestFilter::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
