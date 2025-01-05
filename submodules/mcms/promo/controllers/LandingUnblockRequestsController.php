<?php

namespace mcms\promo\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\common\web\AjaxResponse;
use mcms\promo\components\events\LandingUnblockRequestCreated;
use mcms\promo\components\UsersHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\UnblockRequest;
use Yii;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\search\LandingUnblockRequestSearch;
use mcms\common\controller\AdminBaseController;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * LandingUnblockRequestsController implements the CRUD actions for LandingUnblockRequest model.
 */
class LandingUnblockRequestsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->getView()->title = LandingUnblockRequest::translate('main');

    return parent::beforeAction($action);
  }

  /**
   * Lists all LandingUnblockRequest models.
   *
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new LandingUnblockRequestSearch([
      'scenario' => LandingUnblockRequestSearch::SCENARIO_ADMIN,
      'orderByFieldStatus' => LandingUnblockRequestSearch::STATUS_MODERATION
    ]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'select2InitValues' => $this->getSelect2InitValues($searchModel),
      'countries' => Country::getDropdownItems(),
    ]);
  }


  /**
   * @param $model
   * @return array
   */
  protected function getSelect2InitValues($model)
  {
    $select2InitValues = [];
    if ($model->landing_id) {
      $select2InitValues['landing_id'] = Landing::findOne($model->landing_id)->getStringInfo();
    }
    if ($model->user_id) {
      $select2InitValues['user_id'] = UsersHelper::getUserString($model->user_id);
    }
    return $select2InitValues;
  }

  public function actionViewModal($id)
  {
    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id)
    ]);
  }

  public function actionCreateModal()
  {
    return $this->handleAjaxForm(new UnblockRequest());
  }

  public function actionUpdateModal($id)
  {
    return $this->handleAjaxForm($this->findModel($id));
  }

  /**
   * @param $model UnblockRequest|LandingUnblockRequest
   * @return array|string
   */
  private function handleAjaxForm($model)
  {
    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post('submit')) {
        // если указан провайдер, создаем по всем лендам провайдера
        if ($model instanceof UnblockRequest && !empty($model->providerId)) {
          return AjaxResponse::set($model->saveByProvider());
        }

        // если указан оператор - создаем по всем лендам оператора
        if ($model instanceof UnblockRequest && !empty($model->operatorId)) {
          return AjaxResponse::set($model->saveByOperator());
        }

        $saveResult = $model->save();
        if ($saveResult && !$model->isStatusModeration()) {
          $this->setNotificationAsViewed(LandingUnblockRequestCreated::class, $model->id);
        }
        return AjaxResponse::set($saveResult);
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('form-modal', [
      'model' => $model,
      'select2InitValues' => $this->getSelect2InitValues($model)
    ]);
  }


  /**
   * Finds the LandingUnblockRequest model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return LandingUnblockRequest the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = LandingUnblockRequest::findOne($id)) !== null
      && Yii::$app->user->identity->canViewUser($model->user_id)) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * tricky: переопределено для того, чтобы игнорился вызов метода из AbstractBaseController.
   * Он будет игнориться, т.к. там не передается event, а в методе getNotificationModuleId делается проверка на его наличие
   * @inheritdoc
   */
  protected function setNotificationAsViewed($event = null, $fn = null, $onlyOwner = false)
  {
    $binModuleId = StringEncoderDecoder::encode($fn);

    return parent::setNotificationAsViewed($event, $binModuleId, $onlyOwner);
  }
}
