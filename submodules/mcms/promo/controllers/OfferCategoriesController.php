<?php

namespace mcms\promo\controllers;

use Exception;
use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\web\AjaxResponse;
use mcms\promo\models\OfferCategory;
use mcms\promo\models\search\OfferCategorySearch;
use Yii;
use mcms\promo\models\LandingCategory;
use mcms\promo\models\search\LandingCategorySearch;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * LandingCategoriesController implements the CRUD actions for LandingCategory model.
 */
class OfferCategoriesController extends AdminBaseController
{
  /**
   * @var string
   */
  public $layout = '@app/views/layouts/main';

  /**
   * @param \yii\base\Action $action
   * @return bool
   */
  public function beforeAction($action)
  {
    $this->view->title = Yii::_t('promo.offer_categories.main');

    return parent::beforeAction($action);
  }

  /**
   *
   * Lists all LandingCategory models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new OfferCategorySearch([
      'scenario' => OfferCategorySearch::SCENARIO_ADMIN,
    ]);
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
    ]);
  }

  /**
   * @param $id
   * @return string
   */
  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id)
    ]);
  }

  /**
   * @return array|string
   */
  public function actionCreateModal()
  {
    $model = new OfferCategory();

    return $this->handleAjaxForm($model);
  }

  /**
   * @param $id
   * @return array|string
   */
  public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);

    return $this->handleAjaxForm($model);
  }

  /**
   * @param OfferCategory $model
   * @return array|string
   * @throws Exception
   */
  private function handleAjaxForm(OfferCategory $model)
  {
    if (!$model->load(Yii::$app->request->post())) {
      return $this->renderAjax('form-modal', [
        'model' => $model,
      ]);
    }

    Yii::$app->response->format = Response::FORMAT_JSON;

    if (!Yii::$app->request->post("submit")) {
      return ActiveForm::validate($model);
    }

    return AjaxResponse::set($model->save());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return array
   */
  public function actionEnable($id)
  {
    $model = $this->findModel($id);

    return AjaxResponse::set($model->setEnabled()->save());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return array
   */
  public function actionDisable($id)
  {
    $model = $this->findModel($id);

    return AjaxResponse::set($model->setDisabled()->save());
  }

  /**
   * Finds the LandingCategory model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return OfferCategory the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = OfferCategory::findOne($id)) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
