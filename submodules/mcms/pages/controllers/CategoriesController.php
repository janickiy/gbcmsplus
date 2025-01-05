<?php

namespace mcms\pages\controllers;

use kartik\form\ActiveForm;
use mcms\common\web\AjaxResponse;
use mcms\pages\models\CategoryProp;
use mcms\pages\models\CategoryPropEntity;
use Yii;
use mcms\pages\models\Category;
use mcms\pages\models\search\CategorySearch;
use mcms\common\controller\AdminBaseController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoriesController implements the CRUD actions for Category model.
 */
class CategoriesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';


  /**
   * Lists all Category models.
   * @return mixed
   */
  public function actionIndex()
  {

    $this->controllerTitle = Category::translate('list');

    $searchModel = new CategorySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }


  /**
   * Creates a new Category model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate($id = null)
  {
    $model = $id ? $this->findModel($id) : new Category();

    $title = $model->isNewRecord ? Category::translate('create') : (string)$model->name;
    $this->controllerTitle = $title;

    return $this->handleForm($model);
  }

  private function handleForm(Category $model)
  {
    if (!Yii::$app->request->isAjax || !$model->load(Yii::$app->request->post())) {
      return $this->renderForm($model);
    }

    $isNew = $model->isNewRecord;

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($model);
    }

    $result = $model->save();

    if ($isNew) {
      if (!$result) return $this->renderForm($model);
      $this->flashSuccess('app.common.Saved successfully');
      $this->redirect(['create', 'id' => $model->id]);
    }

    return AjaxResponse::set($result);
  }

  private function renderForm(Category $model)
  {
    return $this->render('form', [
      'model' => $model,
      'propsDataProvider' => new ActiveDataProvider([
        'query' => $model->getProps()->orderBy(['id' => SORT_DESC]),
        'pagination' => false
      ])
    ]);
  }

  public function actionPropModal($categoryId, $id = null)
  {
    $model = $id ? CategoryProp::findOne($id) : new CategoryProp([
      'page_category_id' => $categoryId
    ]);

    return $this->handlePropForm($model);
  }

  private function handlePropForm(CategoryProp $model)
  {
    if (!Yii::$app->request->isAjax || !$model->load(Yii::$app->request->post())) {
      return $this->renderAjax('prop-form_modal', [
        'model' => $model
      ]);
    }

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($model);
    }

    return AjaxResponse::set($model->save());
  }

  public function actionPropEntityModal($propId, $id = null)
  {
    $prop = CategoryProp::findOne($propId);
    $entity = $id ? CategoryPropEntity::findOne($id) : new CategoryPropEntity(['page_category_prop_id' => $propId]);
    return $this->handlePropEntityForm($prop, $entity);
  }

  public function actionPropEntityDelete($id)
  {
    $model = CategoryPropEntity::findOne($id);
    $model->delete();
    return AjaxResponse::success();
  }

  public function actionPropDelete($id)
  {
    $model = CategoryProp::findOne($id);
    $model->delete();
    return AjaxResponse::success();
  }

  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    $model->delete();
    return AjaxResponse::success();
  }

  private function handlePropEntityForm(CategoryProp $prop, CategoryPropEntity $entity)
  {
    if (!Yii::$app->request->isAjax || !$entity->load(Yii::$app->request->post())) {

      $entitiesDataProvider = new ActiveDataProvider([
        'query' => $prop->getPropEntities(),
        'pagination' => false,
        'sort' => [
          'defaultOrder' => ['id' => SORT_DESC]
        ]
      ]);

      return $this->renderAjax('prop-entity_modal', [
        'prop' => $prop,
        'entity' => $entity,
        'entitiesDataProvider' => $entitiesDataProvider
      ]);
    }

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($entity);
    }

    return AjaxResponse::set($entity->save());
  }

  /**
   * Finds the Category model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Category the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Category::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

}
