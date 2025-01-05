<?php

namespace mcms\promo\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use rgk\utils\actions\ViewAction;
use rgk\utils\actions\ViewModalAction;
use Yii;
use mcms\promo\models\Country;
use mcms\promo\models\search\CountrySearch;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * CountriesController implements the CRUD actions for Country model.
 */
class CountriesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.countries.main');

    return parent::beforeAction($action);
  }

  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'view-modal' => [
        'class' => ViewModalAction::class,
        'modelClass' => Country::class,
      ],
      'view' => [
        'class' => ViewAction::class,
        'modelClass' => Country::class,
      ]
    ];
  }

  /**
   * Lists all Country models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new CountrySearch(['scenario' => CountrySearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  /**
   * Creates a new Country model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreateModal()
  {
    return $this->handleAjaxForm(new Country());
  }

  /**
   * Updates an existing Country model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  private function handleAjaxForm(Country $model)
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
   * Finds the Country model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Country the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Country::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}