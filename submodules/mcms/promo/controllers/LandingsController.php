<?php

namespace mcms\promo\controllers;

use helpers\CurrencyCourses;
use mcms\common\actions\MassUpdateAction;
use mcms\common\behavior\EditableBehaviour;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Select2;
use mcms\common\web\AjaxResponse;
use mcms\currency\models\Currency;
use mcms\promo\components\api\MainCurrencies;
use mcms\promo\models\Country;
use mcms\promo\models\LandingMassModel;
use mcms\promo\models\LandingOperatorPayType;
use mcms\promo\models\Provider;
use mcms\promo\models\search\UserOperatorLandingSearch;
use mcms\promo\models\TrafficType;
use mcms\promo\models\LandingPayType;
use mcms\promo\models\LandingSubscriptionType;
use mcms\promo\models\Platform;
use mcms\promo\Module;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Landing;
use mcms\promo\models\search\LandingSearch;
use mcms\common\controller\AdminBaseController;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\BaseHtml;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * LandingsController implements the CRUD actions for Landing model.
 */
class LandingsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';

  /**
   * @param \yii\base\Action $action
   * @return bool
   * @throws \yii\web\ForbiddenHttpException
   */
  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.landings.main');

    return parent::beforeAction($action);
  }

  public function actions()
  {
    return [
      'update-buyout-profit' => [
        'class' => \mcms\common\actions\EditableAction::class,
        'callback' => 'updateBuyoutProfit',
      ],
      'update-editable' => [
        'class' => \mcms\common\actions\EditableAction::class,
        'callback' => 'updateEditable',
      ],
      'mass-update' => [
        'class' => MassUpdateAction::class,
        'model' => new LandingMassModel(['model' => new Landing]),
      ],
    ];
  }

  /**
   * @return string
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  public function actionPayouts()
  {
    $this->getView()->title = Yii::_t('promo.landing_operator_price.title');
    $searchModel = new UserOperatorLandingSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('payouts', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider
    ]);
  }

  public function updateEditable(array $requestData)
  {
    $landingId = ArrayHelper::getValue($requestData, 'landingId');
    $attribute = ArrayHelper::getValue($requestData, 'attribute');
    $newValue = ArrayHelper::getValue($requestData, $attribute);

    $landing = $this->findModel($landingId);
    $landing->{$attribute} = $newValue;

    return [
      'success' => $landing->save(),
      'message' => BaseHtml::errorSummary($landing)
    ];
  }

  public function updateBuyoutProfit(array $requestData)
  {
    $operatorId = \yii\helpers\ArrayHelper::getValue($requestData, 'operatorId');
    $landingId = \yii\helpers\ArrayHelper::getValue($requestData, 'landingId');
    $attribute = \yii\helpers\ArrayHelper::getValue($requestData, 'attribute');
    $buyoutProfit = \yii\helpers\ArrayHelper::getValue($requestData, $attribute);

    /** @var LandingOperator $landingOperator */
    $landingOperator = LandingOperator::find()->where([
      'operator_id' => $operatorId,
      'landing_id' => $landingId,
    ])->one();

    if ($landingOperator === null) {
      return [
        'success' => false,
        'message' => Yii::_t('app.common.operation_failure'),
      ];
    }

    $landingOperator->{$attribute} = $buyoutProfit;
    /** @var Landing $landing */
    $landing = $landingOperator->getLanding()->one();
    $landing->allow_sync_buyout_prices = 0;
    $saveResult = $landing->save();
    if (!$saveResult) {
      return [
        'success' => false,
        'message' => BaseHtml::errorSummary($landingOperator)
      ];
    }

    $saveResult = $landingOperator->save();

    if (!$saveResult) {
      return [
        'success' => false,
        'message' => BaseHtml::errorSummary($landingOperator)
      ];
    }

    return [
      'success' => true,
    ];
  }

  /**
   *
   * Lists all Landing models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new LandingSearch(['scenario' => LandingSearch::SCENARIO_ADMIN]);
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'countries' => Country::getDropdownItems(),
      'rowClass' => function ($model) {
        return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
      },
      'canEditAllProviders' => Provider::canEditAllProviders(),
    ]);
  }

  /**
   *
   * Displays a single Landing model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException
   */
  public function actionView($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = $model->name;
    $currencies = Yii::$app->getModule('payments')->api('exchangerPartnerCourses')->getCurrencyCourses();

    return $this->render('view', [
      'model' => $model,
      'currencies' => $currencies,
    ]);
  }

  /**
   *
   * Displays a single Landing model.
   * @param integer $id
   * @return mixed
   */
  public function actionViewModal($id)
  {
    $currencies = Yii::$app->getModule('payments')->api('exchangerPartnerCourses')->getCurrencyCourses();
    $model = $this->findModel($id);
    $mc = Yii::$app->getModule('promo')->api('mainCurrencies',['availablesOnly' => true])->getResult();
    
    $mainCurrencies = [];
    /** @var Currency $c */
    foreach ($mc as $c){
      switch ($c['code']){
        case MainCurrencies::RUB:
          $mainCurrencies[MainCurrencies::RUB]['to_rub'] = $c['to_rub'];
          $mainCurrencies[MainCurrencies::RUB]['to_usd'] = $c['to_usd'];
          $mainCurrencies[MainCurrencies::RUB]['to_eur'] = $c['to_eur'];
          break;
        case MainCurrencies::USD:
          $mainCurrencies[MainCurrencies::USD]['to_rub'] = $c['to_rub'];
          $mainCurrencies[MainCurrencies::USD]['to_usd'] = $c['to_usd'];
          $mainCurrencies[MainCurrencies::USD]['to_eur'] = $c['to_eur'];
          break;
        case MainCurrencies::EUR:
          $mainCurrencies[MainCurrencies::EUR]['to_rub'] = $c['to_rub'];
          $mainCurrencies[MainCurrencies::EUR]['to_usd'] = $c['to_usd'];
          $mainCurrencies[MainCurrencies::EUR]['to_eur'] = $c['to_eur'];
          break;
      }
    }
    
    return $this->renderAjax('view-modal', [
      'model' => $model,
      'currencies' => $currencies,
      'canEditAllProviders' => Provider::canEditAllProviders(),
      'mainCurrencies' =>$mainCurrencies
    ]);
  }

  /**
   * Updates an existing Landing model.
   * If update is successful, the browser will be redirected to the 'view' page.
   *
   * @param $id
   * @return string|\yii\web\Response
   * @throws Exception
   * @throws NotFoundHttpException
   * @throws \Exception
   * @throws \yii\db\Exception
   */
  public function actionUpdate($id)
  {
    $model = $this->findModelForAllowedProvider($id);

    $this->getView()->title = Yii::_t('promo.landings.update') . ' | ' . $model->name;

    $model->operatorModels = $model->landingOperator;

    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadOperators(Yii::$app->request->post()) &&
      $model->validate() &&
      Model::validateMultiple($model->operatorModels, (new LandingOperator())->getAttributes(null, ['landing_id']))
    ) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        if ($model->save(false)) {
          $transaction->commit();
          return $this->redirect(['view', 'id' => $model->id]);
        }
        $transaction->rollBack();
      } catch (Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }

    if (empty($model->operatorModels)) $model->operatorModels = [new LandingOperator()];

    $model->platformIds = $this->getPlatformIds($model);
    $model->forbiddenTrafficTypeIds = $this->getForbiddenTrafficTypeIds($model);

    // TODO: надо перенести присвоение внутри сценария для модели $operatorModel, а не здесь
    foreach($model->operatorModels as &$operatorModel){
      $operatorModel->payTypeIds = ArrayHelper::getColumn($operatorModel->payTypes, 'id');
    }

    // Для редактирования ресом своих лендов используем отдельную вьюху
    $view = $model->provider->is_rgk ? 'form' : 'external_provider_form';
    // тип подписки Onetime
    $onetimeSubscriptionType = LandingSubscriptionType::findOne(['code' => LandingSubscriptionType::CODE_ONETIME]);

    return $this->render($view, [
      'model' => $model,
      'platforms' => $this->getPlatformsList(),
      'forbiddenTrafficTypes' => $this->getForbiddenTrafficTypeList(),
      'payTypes' => $this->getPayTypeList(),
      'subscriptionTypes' => $this->getSubscriptionTypeList(),
      'showDaysHold' => $model->provider->is_rgk,
      'onetimeId' => $onetimeSubscriptionType ? $onetimeSubscriptionType->id : null,
    ]);

  }

  /**
   *  Добавить лендинг для внешнего провайдера
   * @return string|Response
   * @throws Exception
   */
  public function actionCreateExternal()
  {
    $model = new Landing();
    $model->setScenario(Landing::SCENARIO_CREATE_WITH_EXTERNAL_PROVIDER);

    $this->getView()->title = Yii::_t('promo.landings.create');

    if (
      $model->load(Yii::$app->request->post()) &&
      $model->loadOperators(Yii::$app->request->post()) &&
      $model->validate() &&
      Model::validateMultiple($model->operatorModels, (new LandingOperator())->getAttributes(null, ['landing_id']))
    ) {
      $transaction = Yii::$app->db->beginTransaction();
      try {
        if ($model->save(false)) {
          $transaction->commit();
          return $this->redirect(['view', 'id' => $model->id]);
        }
        $transaction->rollBack();
      } catch (Exception $e) {
        $transaction->rollBack();
        throw $e;
      }
    }
    // тип подписки Onetime
    $onetimeSubscriptionType = LandingSubscriptionType::findOne(['code' => LandingSubscriptionType::CODE_ONETIME]);

    $model->operatorModels = [new LandingOperator()];

    return $this->render('external_provider_form', [
      'model' => $model,
      'platforms' => $this->getPlatformsList(),
      'forbiddenTrafficTypes' => $this->getForbiddenTrafficTypeList(),
      'payTypes' => $this->getPayTypeList(),
      'subscriptionTypes' => $this->getSubscriptionTypeList(),
      'showDaysHold' => false,
      'onetimeId' => $onetimeSubscriptionType ? $onetimeSubscriptionType->id : null,
    ]);

  }

  /**
   * Находит модели для разрешенного провайдера
   * @param integer $id
   * @return Landing the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModelForAllowedProvider($id)
  {
    $model = $this->findModel($id);
    if ($model->provider->is_rgk && !Provider::canEditAllProviders()) {
      throw new NotFoundHttpException();
    }
    return $model;
  }

  /**
   * Finds the Landing model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Landing the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    if (($model = Landing::findOne($id)) === null) {
      throw new NotFoundHttpException('The requested page does not exist.');
    }
    return $model;
  }

  /**
   * @Description("Search landings from select2")
   * @param $q
   * @return array
   */
  public function actionSelect2()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Select2::getItems(new LandingSearch());
  }

  public function actionStatFiltersSelect2($q = '')
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    $landingIds = StatFilter::getFilteredLandingIds(Yii::$app->request->get('users', []));

    // если нет лендов в стат фильтрах, то не нужно их доставать, ибо придут все
    $items = [];
    if ($landingIds) {
      $items = Module::getInstance()
        ->api('getLandingsByCategory', [
          'landingsId' => $landingIds,
          'filterName' => $q,
          'cache' => false,
          'isActive' => false,
        ])
        ->getResult();
    }

    $result = [];

    foreach ($items as $group => $subItems) {
      $subResult = [];
      foreach ($subItems as $id => $text) {
        $subResult[] = [
          'id' => $id,
          'text' => $text,
        ];
      }
      $result[] = [
        'text' => $group,
        'children' => $subResult,
      ];
    }

    return ['results' => $result];
  }

  private function getPlatformsList()
  {
    $platforms = Platform::find()->where(['status' => Platform::STATUS_ACTIVE])->each();
    return ArrayHelper::map($platforms, 'id', 'name');
  }

  private function getPlatformIds(Landing $model)
  {
    $platforms = $model->getPlatforms()->all();
    return ArrayHelper::getColumn($platforms, 'id');
  }

  private function getForbiddenTrafficTypeList()
  {
    $models = TrafficType::find()->where(['status' => TrafficType::STATUS_ACTIVE])->each();
    return ArrayHelper::map($models, 'id', 'name');
  }

  private function getForbiddenTrafficTypeIds(Landing $model)
  {
    $models = $model->getForbiddenTrafficTypes()->all();
    return ArrayHelper::getColumn($models, 'id');
  }

  private function getPayTypeList()
  {
    $models = LandingPayType::find()->where(['status' => LandingPayType::STATUS_ACTIVE])->each();
    return ArrayHelper::map($models, 'id', 'name');
  }

  private function getSubscriptionTypeList()
  {
    $models = LandingSubscriptionType::find()->where(['status' => LandingSubscriptionType::STATUS_ACTIVE])->each();
    return ArrayHelper::map($models, 'id', 'name');
  }

  public function actionDisable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_CHANGE_STATUS);
    $model->setDisabled();
    return AjaxResponse::set($model->save());
  }

  public function actionEnable($id)
  {
    $model = $this->findModel($id);
    $model->setScenario($model::SCENARIO_CHANGE_STATUS);
    $model->setEnabled();
    return AjaxResponse::set($model->save());
  }

  public function actionDelete($id)
  {
    $model = $this->findModel($id);

    $transaction = Yii::$app->db->beginTransaction();
    try {
      foreach ($model->landingUnblockRequest as $landingUnblockRequest) {
        $landingUnblockRequest->delete();
      }

      foreach ($model->getLandingForbiddenTrafficTypes()->all() as $landingForbiddenTrafficType) {
        $landingForbiddenTrafficType->delete();
      }

      foreach ($model->getLandingPlatforms()->all() as $landingPlatform) {
        $landingPlatform->delete();
      }

      // TODO проверить ключи сюда
      foreach ($model->sourceOperatorLanding as $sourceOperatorLanding) {
        $sourceOperatorLanding->delete();
      }

      foreach ($model->visibleLandingPartners as $visibleLandingPartner) {
        $visibleLandingPartner->delete();
      }

      foreach ($model->landingOperator as $landingOperator) {
        foreach ($landingOperator->landingOperatorPayTypes as $payType) {
          /** @var LandingOperatorPayType $payType */
          $payType->delete();
        }

        $landingOperator->delete();
      }

      // TODO удаление для rebill_correct_conditions

      if (!$model->delete()) {
        $transaction->rollBack();

        return AjaxResponse::error();
      }

      $transaction->commit();
    } catch (\yii\db\Exception $e) {
      $transaction->rollBack();

      return AjaxResponse::error($e->getMessage());
    }

    return AjaxResponse::success();
  }

  public function actionCopyLanding($id)
  {
    $copyFromLanding = $this->findModel($id);
    $newModel = $copyFromLanding->copyLanding();

    if ($newModel === null) {
      $this->goBack();
    }

    $this->redirect(['landings/update', 'id' => $newModel->id]);
  }
}
