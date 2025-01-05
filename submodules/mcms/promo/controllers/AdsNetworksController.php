<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use Yii;
use mcms\promo\models\AdsNetwork;
use mcms\promo\models\search\AdsNetworkSearch;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * AdsNetworksController implements the CRUD actions for AdsNetwork model.
 */
class AdsNetworksController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.ads-networks.main');

    return parent::beforeAction($action);
  }

  /**
   * Lists all AdsNetwork models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new AdsNetworkSearch(['scenario' => AdsNetworkSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id)
    ]);
  }

  public function actionView($id)
  {
    $model = $this->findModel($id);
    $this->getView()->title = $model->name;
    return $this->render('view', [
      'model' => $model
    ]);
  }

  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = AdsNetwork::translate('update') . ' | ' . $model->name;

    return $this->handleSaveForm($model);
  }

  public function actionCreate()
  {
    $model = new AdsNetwork();

    $this->getView()->title = AdsNetwork::translate('create');

    return $this->handleSaveForm($model);
  }

  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setDisabled();
    return AjaxResponse::set($model->save());
  }

  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setEnabled();
    return AjaxResponse::set($model->save());
  }

  /**
   * Finds the AdsNetwork model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return AdsNetwork the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = AdsNetwork::findOne($id)) !== null) return $model;
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