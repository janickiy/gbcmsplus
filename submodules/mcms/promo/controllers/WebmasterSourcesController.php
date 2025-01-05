<?php

namespace mcms\promo\controllers;

use Exception;
use mcms\common\helpers\ArrayHelper;
use mcms\common\exceptions\ModelNotSavedException;
use mcms\common\traits\Translate;
use mcms\common\widget\alert\Alert;
use mcms\promo\components\events\SourceCreatedModeration;
use mcms\promo\components\SourceLandingSetsSync;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use mcms\promo\models\SourceOperatorLanding;
use Yii;
use mcms\promo\models\Source;
use mcms\promo\models\search\SourceSearch;
use mcms\common\controller\AdminBaseController;
use yii\base\Model;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use mcms\common\web\AjaxResponse;
use yii\widgets\ActiveForm;
use yii\data\ArrayDataProvider;

/**
 * WebmasterSourcesController implements the CRUD actions for Source model.
 */
class WebmasterSourcesController extends AdminBaseController
{

  use Translate;

  public $layout = '@app/views/layouts/main';

  /**
   *
   */
  const LANG_PREFIX = "promo.webmaster_sources.";
  const EDITABLE_COLUMN_EMPTY_MESSAGE = '';
  const EDITABLE_COLUMN_OPERATORS_GLUE = ', ';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'landing-sets-sync' => ['POST'],
          'landing-sets-delete' => ['POST'],
          'add-landing' => ['POST'],
          'update-landing' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->controllerTitle = self::translate('main');

    return parent::beforeAction($action);
  }

  /**
   * Lists all Source models.
   * @return mixed
   */
  public function actionIndex()
  {
    $searchModel = new SourceSearch([
      'source_type' => SourceSearch::SOURCE_TYPE_WEBMASTER_SITE,
      'orderByFieldStatus' => SourceSearch::STATUS_MODERATION
    ]);

    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'operatorsMap' => Operator::getOperatorsDropDown([], false)
    ]);
  }

  /**
   * Displays a single Source model.
   * @return mixed
   */
  public function actionView()
  {
    $modelId = Yii::$app->request->isAjax ? Yii::$app->request->post('expandRowKey') : Yii::$app->request->get('id');
    $model = $this->findModel($modelId);
    if (!$model) {
      throw new ModelNotSavedException();
    }

    $sourceOperatorLandings = $model->getSourceOperatorlandingsDataProvider();

    return Yii::$app->request->isAjax
      ? $this->renderAjax('_view', ['model' => $model, 'sourceOperatorLandings' => $sourceOperatorLandings])
      : $this->render('view', ['model' => $model, 'sourceOperatorLandings' => $sourceOperatorLandings]);
  }

  public function actionDisableModal($id)
  {
    $model = $this->findModel($id);
    $model->setScenario(Source::SCENARIO_ADMIN_SET_WEBMASTER_DECLINED_STATUS);
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

  public function actionEnableModal($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ADMIN_CHANGE_STATUS);
    $model->setActive();

    if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      $model->reject_reason = null;

      if (Yii::$app->request->post("submit")) {
        return AjaxResponse::set($model->save());
      }
      return ActiveForm::validate($model);
    }
    return $this->renderAjax('enable-form-modal', [
      'model' => $model
    ]);

  }

  public function actionUpdateCategory()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $model = $this->findModel(Yii::$app->request->post('editableKey'));
    if (!$model) {
      return ['output' => self::EDITABLE_COLUMN_EMPTY_MESSAGE, 'message' => Yii::_t('promo.webmaster_sources.cant_find_source')];
    }

    $model->setScenario(Source::SCENARIO_ADMIN_UPDATE_CATEGORY);

    $model->category_id = ArrayHelper::getValue(Yii::$app->request->post(), ['Source', Yii::$app->request->post('editableIndex'), 'category_id']);
    if (!$model->save()) {
      return ['output' => self::EDITABLE_COLUMN_EMPTY_MESSAGE, 'message' => Yii::_t('promo.webmaster_sources.cant_save_source')];
    }

    $userLang = Yii::$app->user->identity->language;
    if ($model->category_id) {
      return ['output' => $model->getCurrentCategoryName()->$userLang, 'message' => self::EDITABLE_COLUMN_EMPTY_MESSAGE];
    }
    return ['output' => Yii::_t('promo.sources.empty'), 'message' => self::EDITABLE_COLUMN_EMPTY_MESSAGE];
  }

  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE);

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
      if ($model->oldAttributes['landing_set_autosync']) {
        $model->landingModels = $model->sourceOperatorLanding;
      }
      if (!$model->isDeclined()) {
        $model->reject_reason = null;
      }
      $transaction = Yii::$app->db->beginTransaction();
      try {
        if (!$model->save(false)) {
          throw new ModelNotSavedException();
        }
        $model->linkBanners();
        $transaction->commit();
        $this->setNotificationAsViewed(SourceCreatedModeration::class, $model->id);
        $this->flashSuccess('promo.webmaster_sources.source_saved');
        return $this->redirect(['update', 'id' => $model->id]);
      } catch (Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    } else {
      $model->updateBannersIds();
    }

    return $this->render('form', [
      'model' => $model,
      'currency' => Yii::$app->getModule('payments')->api('getUserCurrency', ['userId' => $model->user->id])->getResult()
    ]);
  }

  public function actionLandingSetsAutosyncEnable($id)
  {
    return $this->landingSetsAutosync($id);
  }

  public function actionLandingSetsAutosyncDisable($id)
  {
    return $this->landingSetsAutosync($id, false);
  }

  private function landingSetsAutosync($id, $isEnable = true)
  {
    $source = $this->findModel($id);

    $source->landing_set_autosync = $isEnable;

    return AjaxResponse::set($source->save());
  }

  public function actionLandingSetsSync($id)
  {
    $source = $this->findModel($id);

    $source->set_id && (new SourceLandingSetsSync(['sourceId' => $source->id]))->run();

    if(Yii::$app->request->isAjax) {
      return AjaxResponse::success();
    }

    $this->flashSuccess('app.common.operation_success');

    return $this->redirect(['update', 'id' => $source->id]);
  }

  public function actionLandingSetsDelete($id)
  {
    $source = $this->findModel($id);
    $source->setScenario($source::SCENARIO_ADMIN_UPDATE_WEBMASTER_SOURCE);

    if (!$source->set_id) {
      $this->flashFail('app.common.operation_failure');
      return $this->redirect(['update', 'id' => $source->id]);
    }

    $source->set_id = null;

    if ($source->save()) {
      $this->flashSuccess('app.common.operation_success');
    } else {
      $errors = $source->getFirstErrors();
      Yii::$app->session->setFlash(Alert::TYPE_DANGER, array_shift($errors));
    }

    return $this->redirect(['update', 'id' => $source->id]);
  }

  public function actionAddLanding($sourceId)
  {
    $sourceOperatorLanding = new SourceOperatorLanding(['source_id' => $sourceId]);

    if (!$sourceOperatorLanding->load(Yii::$app->request->post())) {
      $this->getView()->title = Yii::_t('promo.landing_set_items.add-landing');

      return $this->renderAjax('add_landing_modal', [
        'landingModel' => $sourceOperatorLanding,
        'landings' => [],
        'key' => null,
      ]);
    }

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;

      return ActiveForm::validate($sourceOperatorLanding);
    }

    return AjaxResponse::set($sourceOperatorLanding->save());
  }

  public function actionUpdateLanding($sourceId, $key, $landingId, $operatorId, $profitType)
  {
    $sourceOperatorLanding = SourceOperatorLanding::findOne([
      'source_id' => $sourceId,
      'landing_id' => $landingId,
      'operator_id' => $operatorId,
    ]);
    $sourceOperatorLanding->scenario = SourceOperatorLanding::SCENARIO_ADMIN_UPDATE;

    if (!$sourceOperatorLanding) $sourceOperatorLanding = new SourceOperatorLanding([
      'source_id' => $sourceId,
      'landing_id' => $landingId,
      'operator_id' => $operatorId,
      'profit_type' => $profitType,
    ]);

    if (!$sourceOperatorLanding->load(Yii::$app->request->post())) {
      $this->getView()->title = Yii::_t('promo.landing_set_items.edit-landing');
      $landing = Landing::findOne(['id' => $landingId]);

      return $this->renderAjax('add_landing_modal', [
        'landingModel' => $sourceOperatorLanding,
        'landings' => [$landing->id => $landing->name],
        'key' => $key,
        'update' => true,
      ]);
    }

    if (!Yii::$app->request->post("submit")) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      return ActiveForm::validate($sourceOperatorLanding);
    }

    return AjaxResponse::set($sourceOperatorLanding->save());
  }

  /**
   * Удаление лендинга из источника вебмастера
   * @param $sourceId
   * @param $landingId
   * @param $operatorId
   * @return array
   */
  public function actionDeleteLanding($sourceId, $landingId, $operatorId)
  {
    $sourceOperatorLanding = SourceOperatorLanding::findOne([
      'source_id' => $sourceId,
      'landing_id' => $landingId,
      'operator_id' => $operatorId,
    ]);
    $result = $sourceOperatorLanding && $sourceOperatorLanding->delete();

    return AjaxResponse::set($result);
  }

  /**
   * Отклонить заявку на разблокировку лендинга
   * @param int $id id SourceOperatorLanding
   * @return array
   */
  public function actionLockLanding($id)
  {
    $sourceOperatorLanding = $this->findSourceOperatorLanding($id);
    $landingUnblockRequest = $sourceOperatorLanding->getLandingUnblockRequest();
    $description = 'Locked from link №' . $sourceOperatorLanding->source_id;
    return AjaxResponse::set($landingUnblockRequest->setDisabled($description)->save());
  }

  /**
   * Одобрить заявку на разблокировку лендинга
   * @param int $id id SourceOperatorLanding
   * @return array
   */
  public function actionUnlockLanding($id)
  {
    $sourceOperatorLanding = $this->findSourceOperatorLanding($id);
    $landingUnblockRequest = $sourceOperatorLanding->getLandingUnblockRequest();
    $description = 'Unlocked from link №' . $sourceOperatorLanding->source_id;
    return AjaxResponse::set($landingUnblockRequest->setUnlocked($description)->save());
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
    $model = Source::findOne(['id' => $id, 'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE]);
    if ($model !== null
      && Yii::$app->user->identity->canViewUser($model->user_id)
    ) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Поиск модели SourceOperatorLanding
   * @param $id
   * @return SourceOperatorLanding
   * @throws NotFoundHttpException
   */
  protected function findSourceOperatorLanding($id)
  {
    $model = SourceOperatorLanding::findOne(['id' => $id]);
    if ($model !== null
      && Yii::$app->user->identity->canViewUser($model->source->user_id)
    ) {
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
    $binModuleId = $this->getNotificationModuleId($event, $fn);
    if (!$binModuleId) return null;

    return parent::setNotificationAsViewed($event, $binModuleId, $onlyOwner);
  }
}
