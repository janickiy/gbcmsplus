<?php

namespace mcms\pages\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\web\AjaxResponse;
use Yii;
use mcms\pages\models\Faq;
use mcms\pages\models\FaqSearch;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * FaqController implements the CRUD actions for Faq model.
 */
class FaqController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->controllerTitle = Yii::_t('faq.faq');

    return parent::beforeAction($action);
  }

  /**
   * Lists all Faq models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new FaqSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Displays a single Faq model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);

    $this->view->title = $model->question;
    $this->controllerTitle = $model->id;

    return $this->renderAjax('view', [
      'model' => $this->findModel($id),
    ]);
  }

  /**
   * Creates a new Faq model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate()
  {
    $this->view->title = Yii::_t('faq.create');
    $this->controllerTitle = $this->view->title;

    $model = new Faq(['scenario' => Faq::SCENARIO_CREATE]);

    return $this->handleSaveForm($model);
  }

  /**
   * Updates an existing Faq model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_UPDATE);

    $this->view->title = Yii::_t('faq.update') . ' | ' . $model->id;
    $this->controllerTitle = $this->view->title;

    return $this->handleSaveForm($model);
  }

  /**
   * Deletes an existing Faq model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    return AjaxResponse::set($model->deleteFaq());
  }

  /**
   * Метод возвращает значения для поля sort в dropdown.
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionGetSortDropDownArray()
  {
    $modelId = ArrayHelper::getValue(Yii::$app->request->post(), 'depdrop_all_params.faq_id');
    $selectedCategoryId = ArrayHelper::getValue(Yii::$app->request->post(), 'depdrop_all_params.faq_category_id_select');
    $model = $modelId == "" ? new Faq() : $this->findModel($modelId);
    return Json::encode($model->getDropDownFaqRangeArray($selectedCategoryId));
  }
  
  /**
   * Finds the Faq model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Faq the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Faq::findOne($id)) !== null) {
      return $model;
    } else {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
  }

  protected function handleSaveForm($model)
  {
    if ($model->load(Yii::$app->request->post())) {
      if (!Yii::$app->request->post('submit')) {
        // Валидация
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ActiveForm::validate($model);
      } else {
        // Сохранение
        return AjaxResponse::set($model->saveFaq());
      }
    }

    return $this->renderAjax('form', [
      'model' => $model,
      'canViewDropDown' => Html::hasUrlAccess(['faq/get-sort-drop-down-array/']),
    ]);
  }
}