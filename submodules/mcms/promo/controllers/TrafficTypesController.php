<?php

namespace mcms\promo\controllers;

use Yii;
use mcms\promo\models\TrafficType;
use mcms\promo\models\search\TrafficTypeSearch;
use mcms\common\controller\AdminBaseController;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use yii\web\Response;
use mcms\common\web\AjaxResponse;

/**
 * TrafficTypesController implements the CRUD actions for TrafficType model.
 */
class TrafficTypesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  /**
   * Lists all TrafficType models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new TrafficTypeSearch(['scenario' => TrafficTypeSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = TrafficType::translate('update') . ' | ' . $model->name;

    return $this->handleSaveForm($model);
  }

  /**
   * Performs ajax validation.
   * @param Model $model
   * @throws \yii\base\ExitException
   */
  protected function performAjaxValidation(Model $model)
  {
    if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      echo json_encode(ActiveForm::validate($model));
      Yii::$app->end();
    }
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
   * Finds the TrafficType model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return TrafficType the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = TrafficType::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @param ActiveRecord $model
   * @return string
   */
  protected function handleSaveForm($model)
  {
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      } else {
        // Сохранение
        return AjaxResponse::set($model->save());
      }
    }

    return $this->renderAjax('form', [
      'model' => $model,
    ]);
  }
}
