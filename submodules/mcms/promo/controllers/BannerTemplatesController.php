<?php

namespace mcms\promo\controllers;

use kartik\form\ActiveForm;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\BannerTemplate;
use mcms\promo\models\BannerTemplateAttribute;
use mcms\promo\models\search\BannerTemplateSearch;
use Yii;
use mcms\common\controller\AdminBaseController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * BannerTemplatesController implements the CRUD actions for Banner model.
 */
class BannerTemplatesController extends AdminBaseController
{

  /**
   * @inheritdoc
   */
  public $layout = '@app/views/layouts/main';

  /**
   * Lists
   * @return mixed
   */
  public function actionIndex()
  {

    $this->controllerTitle = BannerTemplate::translate('list');

    $searchModel = new BannerTemplateSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }


  /**
   * Creates model
   * @return mixed
   */
  public function actionCreate($id = null)
  {
    $model = $id ? $this->findModel($id) : new BannerTemplate();

    $title = $model->isNewRecord ? BannerTemplate::translate('create') : (string)$model->name;
    $this->controllerTitle = $title;

    return $this->handleForm($model);
  }

  /**
   * @param BannerTemplate $model
   * @return array|string
   */
  private function handleForm(BannerTemplate $model)
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

  /**
   * @param BannerTemplate $model
   * @return string
   */
  private function renderForm(BannerTemplate $model)
  {
    return $this->render('form', [
      'model' => $model,
      'attributesDataProvider' => new ActiveDataProvider([
        'query' => $model->getTemplateAttributes()->orderBy(['id' => SORT_DESC]),
        'pagination' => false,
        'sort' => false
      ])
    ]);
  }

  /**
   * @param $templateId
   * @param null $id
   * @return array|string
   */
  public function actionAttributeModal($templateId, $id = null)
  {
    $model = $id ? BannerTemplateAttribute::findOne($id) : new BannerTemplateAttribute([
      'template_id' => $templateId
    ]);

    return $this->handleAttributeForm($model);
  }

  /**
   * @param BannerTemplateAttribute $model
   * @return array|string
   */
  private function handleAttributeForm(BannerTemplateAttribute $model)
  {
    if (!Yii::$app->request->isAjax || !$model->load(Yii::$app->request->post())) {
      return $this->renderAjax('attribute-form_modal', [
        'model' => $model
      ]);
    }

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($model);
    }

    return AjaxResponse::set($model->save());
  }


  /**
   * @param $id
   * @return array
   * @throws \Exception
   */
  public function actionAttributeDelete($id)
  {
    /** @var BannerTemplateAttribute $model */
    $model = BannerTemplateAttribute::findOne($id);
    $model->delete();
    return AjaxResponse::success();
  }

  /**
   * @param $id
   * @return array
   * @throws NotFoundHttpException
   * @throws \Exception
   */
  public function actionDelete($id)
  {
    $model = $this->findModel($id);
    $model->delete();
    return AjaxResponse::success();
  }

  /**
   * Finds the model
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return BannerTemplate the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = BannerTemplate::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }

}
