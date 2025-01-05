<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\TrafficbackProvider;
use Yii;
use mcms\promo\models\search\TrafficbackProviderSearch;
use mcms\common\controller\AdminBaseController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * TrafficbackProvidersController implements the CRUD actions for Provider model.
 */
class TrafficbackProvidersController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws HttpException
   */
  public function beforeAction($action)
  {
    if (!Yii::$app->user->identity) {
      return parent::beforeAction($action);
    }
    if (!Yii::$app->user->identity->canManageTbProviders()) {
      throw new HttpException(403);
    }

    $this->getView()->title = Yii::_t('promo.trafficback_providers.main');
    return parent::beforeAction($action);
  }

  /**
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new TrafficbackProviderSearch;
    $searchModel->scenario = TrafficbackProviderSearch::SCENARIO_ADMIN;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'rowClass' => function ($model) {
        return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
      }
    ]);
  }

  /**
   * @return mixed
   */
  public function actionCreate()
  {
    $this->getView()->title = Yii::_t('promo.trafficback_providers.create');

    $model = new TrafficbackProvider;

    return $this->handleSaveForm($model);
  }

  /**
   * @param integer $id
   * @return mixed
   * @throws \yii\web\NotFoundHttpException
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = Yii::_t('promo.trafficback_providers.update') . ' | ' . $model->name;

    return $this->handleSaveForm($model);
  }

  /**
   * @param integer $id
   * @return TrafficbackProvider the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    $model = TrafficbackProvider::findOne($id);
    if ($model !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @param TrafficbackProvider $model
   * @return array|string
   */
  protected function handleSaveForm($model)
  {
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      }
      // Сохранение
      return AjaxResponse::set($model->save());
    }

    // TRICKY: В params-local необходим параметр pbHandlerUrl - ссылка на прием ПБ от ТБ провайдера.
    // Пример: 'pbHandlerUrl' => 'http://mcms-ml-handler.lc'
    $pbUrl = ArrayHelper::getValue(Yii::$app->params, 'pbHandlerUrl');

    if ($pbUrl) {
      $pbUrl .= '/sold-tb';
    }


    return $this->renderAjax('form', [
      'model' => $model,
      'postbackLink' => $pbUrl
    ]);
  }

  /**
   * Деактивировать
   * @param $id
   * @return array
   * @throws \yii\web\NotFoundHttpException
   */
  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setDisabled();
    return AjaxResponse::set($model->save());
  }

  /**
   * Активировать
   * @param $id
   * @return array
   * @throws \yii\web\NotFoundHttpException
   */
  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setEnabled();
    return AjaxResponse::set($model->save());
  }
}
