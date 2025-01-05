<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\events\DomainAdded;
use mcms\promo\components\events\DomainBanned;
use mcms\promo\components\events\SystemDomainAdded;
use mcms\promo\components\events\SystemDomainBanned;
use mcms\promo\components\UsersHelper;
use Yii;
use mcms\promo\models\Domain;
use mcms\promo\models\search\DomainSearch;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * DomainsController implements the CRUD actions for Domain model.
 */
class DomainsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Domain::translate('main');

    return parent::beforeAction($action);
  }

  /**
   * Lists all Domain models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new DomainSearch(['scenario' => DomainSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'select2InitValues' => $this->getSelect2InitValues($searchModel),
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
   * Displays a single Domain model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);
    $this->getView()->title = $model->url;

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
    return $this->handleAjaxForm(new Domain());
  }

  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  private function handleAjaxForm(Domain $model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model,
      'select2InitValues' => $this->getSelect2InitValues($model),
      'aDomainIp' => Yii::$app->getModule('promo')->getSettingsDomainIp(),
    ]);
  }

  /**
   * Finds the Domain model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Domain the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Domain::findOne($id)) !== null && Yii::$app->user->identity->canViewUser($model->user_id)) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
