<?php

namespace mcms\promo\controllers;

use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\events\LinkCreatedModeration;
use mcms\promo\components\UsersHelper;
use mcms\promo\models\Domain;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Operator;
use mcms\promo\models\search\SourceOperatorLandingSearch;
use mcms\promo\models\SourceOperatorLanding;
use mcms\promo\models\Stream;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\promo\models\Source;
use mcms\promo\models\search\SourceSearch;
use mcms\common\controller\AdminBaseController;
use yii\data\ArrayDataProvider;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use mcms\common\web\AjaxResponse;
use mcms\common\helpers\Select2;
use yii\base\Model;
use yii\base\Exception;
use yii\widgets\ActiveForm;

/**
 * ArbitrarySourcesController implements the CRUD actions for Source model.
 */
class ArbitrarySourcesController extends AdminBaseController
{

  use Translate;

  public $layout = '@app/views/layouts/main';

  /**
   *
   */
  const LANG_PREFIX = "promo.arbitrary_sources.";

  const EDITABLE_COLUMN_EMPTY_MESSAGE = '';
  const EDITABLE_COLUMN_OPERATORS_GLUE = ', ';

  /**
   * Lists all Source models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new SourceSearch([
      'source_type' => SourceSearch::SOURCE_TYPE_LINK,
      'orderByFieldStatus' => SourceSearch::STATUS_MODERATION
    ]);

    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    $streams = Stream::find()->where(['status' => Stream::STATUS_ACTIVE]);
    if (!Yii::$app->user->can('PromoViewOtherPeopleStreams')) {
      $streams->andWhere(['user_id' => Yii::$app->user->id]);
    }

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'rowClass' => function ($model) {
        return ['class' => ArrayHelper::getValue($model::getStatusColors(), $model->status, '')];
      },
      'operatorsMap' => Operator::getOperatorsDropDown([], false),
      'streamNamesData' => ArrayHelper::map($streams->each(), 'id', 'name')
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
   * Displays a single Source model.
   * @return mixed
   */
  public function actionView()
  {
    $modelId = Yii::$app->request->isAjax ? Yii::$app->request->post('expandRowKey') : Yii::$app->request->get('id');
    $model = $this->findModel($modelId);
    $sourceOperatorLandings = $model->getSourceOperatorlandingsDataProvider();

    return Yii::$app->request->isAjax
      ? $this->renderAjax('_view', ['model' => $model, 'sourceOperatorLandings' => $sourceOperatorLandings])
      : $this->render('view', ['model' => $model, 'sourceOperatorLandings' => $sourceOperatorLandings]);
  }

  public function actionDisableModal($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ADMIN_SET_DECLINED_ARBITRARY_SOURCE_STATUS);
    $model->setDeclined();

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }

    return $this->renderAjax('disable-form-modal', [
      'model' => $model,
      'currency' => Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $model->user->id])->getResult()
    ]);
  }

  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario(Source::SCENARIO_ADMIN_CHANGE_STATUS);
    $model->setActive();
    $model->reject_reason = null;
    $saveResult = $model->save();
    $this->setNotificationAsViewed(LinkCreatedModeration::class, $model->id);
    return AjaxResponse::set($saveResult);
  }

  /**
   * Updates an existing Source model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param $id
   * @return array|string|Response
   * @throws Exception
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ADMIN_UPDATE_ARBITRARY_SOURCE);

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return array_merge(
        ActiveForm::validate($model),
        ActiveForm::validateMultiple($model->landingModels, SourceOperatorLanding::getAttributesArray(['source_id']))
      );
    }

    $model->landingModels = new ArrayDataProvider([
      'allModels' => $model->sourceOperatorLanding,
    ]);

    if (
      $model->load(Yii::$app->request->post()) &&
      $model->validate() &&
      Model::validateMultiple($model->landingModels, SourceOperatorLanding::getAttributesArray(['source_id']))
    ) {
      if (!$model->isDeclined()) {
        $model->reject_reason = null;
      }
      $transaction = Yii::$app->db->beginTransaction();
      try {
        if (!$model->save(false)) {
          throw new ModelNotSavedException();
        }
        $transaction->commit();
        if (!$model->isStatusModeration()) {
          $this->setNotificationAsViewed(LinkCreatedModeration::class, $model->id);
        }
        $this->flashSuccess('promo.arbitrary_sources.source_saved');
        return $this->redirect(['update', 'id' => $model->id]);
      } catch (Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }

    if (empty($model->landingModels)) $model->landingModels = [new SourceOperatorLanding()];

    return $this->render('form', [
      'model' => $model,
      'domainDropDownItems' => Domain::getUserActiveDomainItems($model->user_id, $model->domain_id),
      //'select2InitValues' => $this->getSelect2InitValues($model),
      'currency' => Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $model->user->id])->getResult(),
      'globalPostbackUrl' => UserPromoSetting::getGlobalPostbackUrl($model->user_id),
      'globalComplainsPostbackUrl' => UserPromoSetting::getGlobalComplainsPostbackUrl($model->user_id),
    ]);

  }

  /**
   * Finds the Source model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Source the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Source::findOne(['id' => $id, 'source_type' => Source::SOURCE_TYPE_LINK])) !== null
      && Yii::$app->user->identity->canViewUser($model->user_id)
    ) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * @return array
   */
  public function actionSelect2()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Select2::getItems(new SourceSearch());
  }

  /**
   * tricky: переопределено для того, чтобы игнорился вызов метода из AbstractBaseController.
   * Он будет игнориться, т.к. там не передается event, а в методе getNotificationModuleId делается проверка на его наличие
   * @inheritdoc
   */
  protected function setNotificationAsViewed($event = null, $fn = null, $onlyOwner = false)
  {
    $binModuleId = $this->getNotificationModuleId($event, $fn);
    if (!$binModuleId) return null;

    return parent::setNotificationAsViewed($event, $binModuleId, $onlyOwner);
  }

}
