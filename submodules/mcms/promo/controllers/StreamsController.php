<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Select2;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\UsersHelper;
use Yii;
use mcms\promo\models\Stream;
use mcms\promo\models\search\StreamSearch;
use mcms\common\controller\AdminBaseController;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * StreamsController implements the CRUD actions for Stream model.
 */
class StreamsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'enable' => ['POST'],
          'disable' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->getView()->title = Stream::translate('main');

    return parent::beforeAction($action);
  }

  /**
   * Lists all Stream models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new StreamSearch(['scenario' => StreamSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'select2InitValues' => $this->getSelect2InitValues($searchModel)
    ]);
  }

  /**
   * @param $model
   * @return array
   */
  protected function getSelect2InitValues($model)
  {
    $select2InitValues = [];
    if ($model->user_id) {
      $select2InitValues['user_id'] = UsersHelper::getUserString($model->user_id);
    }
    return $select2InitValues;
  }

  /**
   * Displays a single Stream model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = $model->name;

    return $this->render('view', [
      'model' => $model,
    ]);
  }

  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id)
    ]);
  }

  public function actionCreateModal()
  {
    return $this->handleAjaxForm(new Stream());
  }

  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  private function handleAjaxForm(Stream $model)
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
   * @return array
   */
  public function actionStreamSearch()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Select2::getItems(new StreamSearch());
  }

  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionEnable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setEnabled()->save());
  }

  /**
   * @param $id
   * @return \yii\web\Response
   */
  public function actionDisable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setDisabled()->save());
  }

  /**
   * Finds the Stream model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Stream the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Stream::findOne($id)) !== null && Yii::$app->user->identity->canViewUser($model->user_id)) {
      $model->setScenario($model::SCENARIO_ADMIN_EDIT);
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
