<?php

namespace mcms\promo\controllers;

use Exception;
use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\Select2;
use mcms\common\web\AjaxResponse;
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
class LandingCategoriesController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.landing_categories.main');
    return parent::beforeAction($action);
  }

  /**
   *
   * Lists all LandingCategory models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new LandingCategorySearch(['scenario' => LandingCategorySearch::SCENARIO_ADMIN]);
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
   *
   * Displays a single LandingCategory model.
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
    $model = new LandingCategory();
    $model->setScenario(LandingCategory::SCENARIO_CREATE);

    return $this->handleAjaxForm($model);
  }

  public function actionUpdateModal($id)
  {
    $model = $this->findModel($id);
    $model->setScenario(LandingCategory::SCENARIO_UPDATE);

    return $this->handleAjaxForm($model);
  }

  private function handleAjaxForm(LandingCategory $model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
          $result = $model->save();
          if ($result) {
            $model->linkBanners();
          }
          $transaction->commit();
        } catch (Exception $e) {
          $transaction->rollBack();
          throw $e;
        }

        return AjaxResponse::set($result);
      }

      return ActiveForm::validate($model);
    }

    $model->alter_categories = $model->alter_categories ? implode(LandingCategory::DELIMITER, $model->alter_categories) : '';
    $model->updateBannersIds();

    return $this->renderAjax('form-modal', [
      'model' => $model,
    ]);
  }

  public function actionSelect2()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Select2::getItems(new LandingCategorySearch());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return \yii\web\Response
   */
  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->scenario = LandingCategory::SCENARIO_UPDATE;

    return AjaxResponse::set($model->setEnabled()->save());
  }

  /**
   * @Role({"root", "admin"})
   * @param $id
   * @return \yii\web\Response
   */
  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->scenario = LandingCategory::SCENARIO_UPDATE;

    return AjaxResponse::set($model->setDisabled()->save());
  }

  /**
   * Finds the LandingCategory model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingCategory the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingCategory::findOne($id)) !== null) return $model;
    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
