<?php

namespace mcms\pages\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\common\web\AjaxResponse;
use Yii;
use mcms\pages\models\FaqCategory;
use mcms\pages\models\FaqCategorySearch;
use yii\base\Model;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * FaqCategoryController implements the CRUD actions for FaqCategory model.
 */
class FaqCategoriesController extends AdminBaseController
{
  public $layout = '@app/views/layouts/main';

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->controllerTitle = Yii::_t('faq.faq_categories');
    return parent::beforeAction($action);
  }

  /**
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new FaqCategorySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * @return mixed
   */
  public function actionCreate()
  {
    $this->view->title = Yii::_t('faq.create');
    $this->controllerTitle = $this->view->title;

    $model = new FaqCategory(['scenario' => FaqCategory::SCENARIO_CREATE]);

    return $this->handleSaveForm($model);
  }

  /**
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_UPDATE);

    $this->view->title = Yii::_t('faq.update') . ' | ' . $model->name;
    $this->controllerTitle = $this->view->title;

    return $this->handleSaveForm($model);
  }

  /**
   * @param integer $id
   * @return mixed
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    return AjaxResponse::set($model->deleteCategory());
  }

  /**
   * @param integer $id
   * @return FaqCategory the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = FaqCategory::findOne($id)) !== null) {
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
        return AjaxResponse::set($model->saveCategory());
      }
    }

    return $this->renderAjax('form', ['model' => $model]);
  }
}