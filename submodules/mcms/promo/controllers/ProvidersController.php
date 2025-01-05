<?php

namespace mcms\promo\controllers;

use mcms\common\web\AjaxResponse;
use mcms\promo\commands\SyncProvidersController;
use mcms\promo\components\events\LandingCreated;
use mcms\promo\components\events\LandingCreatedReseller;
use mcms\promo\components\events\LandingListCreated;
use mcms\promo\components\events\LandingListCreatedReseller;
use mcms\promo\components\KpProviderTester;
use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\dto\Stream;
use mcms\promo\components\provider_instances_sync\KpApiClient;
use mcms\promo\components\provider_instances_sync\requests\CreateStreamRequest;
use mcms\promo\components\ProviderSyncInterface;
use mcms\promo\models\form\ProviderTestForm;
use mcms\promo\models\Landing;
use mcms\promo\models\ProviderSettingsKp;
use mcms\promo\Module;
use RuntimeException;
use Yii;
use mcms\promo\models\Provider;
use mcms\promo\models\search\ProviderSearch;
use mcms\common\controller\AdminBaseController;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * ProvidersController implements the CRUD actions for Provider model.
 */
class ProvidersController extends AdminBaseController
{

  const DEPENDENT_OUTPUT_PARAM = 'output';

  public $layout = '@app/views/layouts/main';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'get-providers' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('promo.providers.main');
    return parent::beforeAction($action);
  }

  /**
   * Lists all Provider models.
   * @return mixed
   */
  public function actionIndex()
  {

    $searchModel = new ProviderSearch;
    $searchModel->scenario = ProviderSearch::SCENARIO_ADMIN;
    $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $searchModel,
      'rowClass' => function ($model) {
        return $model->status === $model::STATUS_INACTIVE ? ['class' => 'danger'] : [];
      },
      'canViewAllFields' => Provider::canEditAllProviders(),
    ]);
  }

  /**
   * @param $id
   * @return string
   * @throws NotFoundHttpException
   */
  public function actionViewModal($id)
  {
    $model = $this->findModel($id);

    $this->getView()->title = $model->name;

    return $this->renderAjax('view-modal', [
      'model' => $this->findModel($id),
      'settings' => $model->getSettings(),
      'canViewAllFields' => Provider::canEditAllProviders(),
    ]);
  }

  /**
   * Displays a single Provider model.
   * @param integer $id
   * @return mixed
   */
  public function actionView($id)
  {

    $model = $this->findModel($id);

    $this->getView()->title = $model->name;

    return $this->render('view', [
      'model' => $model,
      'settings' => $model->getSettings(),
      'canViewAllFields' => Provider::canEditAllProviders(),
    ]);
  }

  /**
   *
   * Creates a new Provider model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate()
  {
    $this->getView()->title = Yii::_t('promo.providers.create');
    $model = new Provider;
    $model->setScenario(Provider::SCENARIO_CREATE);

    return $this->handleForm($model);
  }

  /**
   * Создание внешнего провайдера
   * @return array|string
   */
  public function actionCreateExternal()
  {
    $this->getView()->title = Yii::_t('promo.providers.create');

    $model = new Provider;
    $model->setScenario(Provider::SCENARIO_CREATE_EXTERNAL);

    return $this->handleForm($model);
  }

  /**
   * Updates an existing Provider model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   */
  public function actionUpdate($id)
  {
    $model = $this->findModel($id);
    $this->getView()->title = Yii::_t('promo.providers.update') . ' | ' . $model->name;

    $scenario = $model->is_rgk === 1 ? Provider::SCENARIO_UPDATE : Provider::SCENARIO_UPDATE_EXTERNAL;
    $model->setScenario($scenario);

    return $this->handleForm($model);
  }

  /**
   * @param integer $id
   * @return mixed
   */
  public function actionRedirect($id)
  {
    $model = $this->findModel($id);

    $model->scenario = $model::SCENARIO_REDIRECT;

    $this->getView()->title = Yii::_t('promo.providers.redirect') . ' | ' . $model->name;

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }
    return $this->render('redirect_form', [
      'model' => $model,
    ]);
  }

  /**
   * @param $id
   * @return array
   */
  public function actionEnable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setEnabled()->save());
  }

  /**
   * @param $id
   * @return array
   */
  public function actionDisable($id)
  {
    return AjaxResponse::set($this->findModel($id)->setDisabled()->save());
  }

  /**
   * Finds the Provider model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Provider the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
    $model = Provider::findOne($id);
    if ($model !== null) {
      return $model;
    }
    throw new NotFoundHttpException('The requested page does not exist.');
  }

  /**
   * Обработка формы сохранения провайдера
   * @param Provider $provider
   * @return array|string
   */
  private function handleForm(Provider $provider)
  {
    // Инициализация моделей
    $requestData = Yii::$app->request->post();
    $isProviderLoaded = $provider->load($requestData);

    $settings = $provider->getSettings();
    if ($settings) {
      $settings->load($requestData);
      $provider->setSettings($settings);
    }

    // Рендер формы, если данные не переданы или это обновление pjax-блока
    if (!$isProviderLoaded || Yii::$app->request->isPjax) {
      $view = $provider->isExternalScenario()
        ? 'external_provider_form'
        : 'form';

      $pbHandlerUrl = ArrayHelper::getValue(Yii::$app->params, 'pbHandlerUrl');

      $pbHandlerUrl = $pbHandlerUrl ? rtrim($pbHandlerUrl, '/') . '/default' : null;

      return $this->renderAjax($view, [
        'model' => $provider,
        'settings' => $settings,
        'formUrl' => Yii::$app->request->getUrl(),
        'pbHandlerUrl' => $pbHandlerUrl,
      ]);
    }

    // Валидация
    if (!Yii::$app->request->post('submit')) {
      Yii::$app->response->format = Response::FORMAT_JSON;
      $errors = ActiveForm::validate($provider);
      if ($settings) {
        $errors = array_merge($errors, ActiveForm::validate($settings));
      }
      return $errors;
    }

    // Сохранение
    return AjaxResponse::set($provider->save());
  }

  /**
   * @param null $instanceHost
   * @return bool|KpApiClient
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  private function getKpApi($instanceHost = null)
  {
    $settingsModel = new ProviderSettingsKp();

    return $settingsModel->getKpApi($instanceHost);
  }

  /**
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function actionGetInstances()
  {
    $api = $this->getKpApi();
    $instances = $api->getInstances();

    return $instances;
  }

  public function actionGetProviders()
  {
    // получить по инстанц айди хост
    $instanceId = ArrayHelper::getValue($_POST, ['depdrop_all_params', 'providersettingskp-instanceid']);
    Yii::$app->response->format = Response::FORMAT_JSON;

    $api = $this->getKpApi();

    $instances = $api->getInstances();

    if ($instances instanceof Error) {
      // показать ошибку
      return $instances;
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    /** @var Instance $instance */
    $instance = isset($instances[$instanceId])
      ? $instances[$instanceId]
      : null
    ;

    $api = $this->getKpApi($instance->domain);
    /** @var \mcms\promo\components\provider_instances_sync\dto\Provider[] $providers */
    $providers = $api->getProviders();

    if ($providers instanceof Error) {
      return [
        'success' => false,
        'data' => $providers
      ];
    }

    return [
      self::DEPENDENT_OUTPUT_PARAM => array_map(function($provider) {
        return ['id' => $provider->id, 'name' => $provider->name];
      }, $providers)
    ];
  }

  public function actionGetStreams()
  {
    $instanceId = ArrayHelper::getValue($_POST, ['depdrop_all_params', 'providersettingskp-instanceid']);

    Yii::$app->response->format = Response::FORMAT_JSON;

    // получить по инстанц айди хост
    $instanceId = (int) $instanceId;
    $api = $this->getKpApi();

    $instances = $api->getInstances();

    if ($instances instanceof Error) {
      return [];
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    /** @var Instance $instance */
    $instance = isset($instances[$instanceId])
      ? $instances[$instanceId]
      : null
    ;

    $api = $this->getKpApi($instance->domain);
    $streams = $api->getStreams();

    return [
      self::DEPENDENT_OUTPUT_PARAM => array_map(function ($stream) {
        return ['id' => $stream->id, 'name' => sprintf('#%d %s', $stream->id, $stream->name)];
      }, $streams)
    ];
  }

  /**
   * @param $instanceId
   * @return array
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function actionCreateStream($instanceId)
  {
    $api = $this->getKpApi();
    $instances = $api->getInstances();

    Yii::$app->response->format = Response::FORMAT_JSON;
    if ($instances instanceof Error) {
      // показать ошибку
      return [
        'success' => false,
        'data' => $instances,
      ];
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    /** @var Instance $instance */
    $instance = isset($instances[$instanceId])
      ? $instances[$instanceId]
      : null
    ;

    $api = $this->getKpApi($instance->domain);

    /** @var $promoModule Module*/
    $promoModule = Yii::$app->getModule('promo');

    $request = new CreateStreamRequest();
    $request->postbackUrl = $promoModule->getPostbackUrl();
    $request->secretKey = $promoModule->getKpSecretKey();
    $request->complainUrl = $promoModule->getComplainsUrl();
    $request->trafficbackUrl = $promoModule->getTrafficbackUrl();
    $request->name = 'kp';

    $result = $api->createStream($request);
    if ($result instanceof Error) {
      // отправить сообщение об ошибке
      return [
        'success' => false,
        'data' => $result,
      ];
    }

    $api->clearStreamsCache();

    return [
      'success' => true,
      'data' => $result,
    ];
  }

  /**
   * @param $instanceId
   * @param $providerId
   * @param $streamId
   * @return array|Error
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function actionCollectKpFormData($instanceId, $providerId, $streamId)
  {
    // получить по инстанц айди хост
    $instanceId = (int) $instanceId;
    $api = $this->getKpApi();
    Yii::$app->response->format = Response::FORMAT_JSON;
    $instances = $api->getInstances();

    if ($instances instanceof Error) {
      return $instances;
    }

    $instances = ArrayHelper::map($instances, 'id', function($item) {
      return $item;
    });

    /** @var Instance $instance */
    $instance = isset($instances[$instanceId])
      ? $instances[$instanceId]
      : null
    ;

    $api = $this->getKpApi($instance->domain);
    $providers = $api->getProviders();

    if ($providers instanceof Error) {
      // показать ошибку
      return $providers;
    }

    $providers = ArrayHelper::index($providers, 'id');
    if (!isset($providers[$providerId])) {
      return [
        'success' => false,
        'message' => 'Provider not found',
      ];
    }

    $streams = $api->getStreams();
    if ($streams instanceof Error) {
      return $streams;
    }

    $streams = ArrayHelper::index($streams, 'id');
    if (!isset($streams[$streamId])) {
      return [
        'success' => false,
        'message' => 'Stream not found',
      ];
    }

    /** @var Stream $stream */
    $stream = $streams[$streamId];

    /** @var \mcms\promo\components\provider_instances_sync\dto\Provider $provider */
    $provider = $providers[$providerId];

    $response = [
      // урл для слва трафика
      'tdsUrl' => strtr(':tdsHost/:streamHash/?landing_id={send_id}&operator_id={operator_id}&l1={hit_id}', [
        ':tdsHost' => rtrim($stream->url, '/'),
        ':streamHash' => $stream->hash
      ]),

      // код провайдера
      'providerCode' => $provider->code,

      // хост инстанца
      'providerUrl' => $provider->url,
    ];

    return [
      'success' => true,
      'data' => $response,
    ];
  }

  /**
   * Тестилка провайдеров
   * @return string
   */
  public function actionTest()
  {
    $model = new ProviderTestForm;

    if ($model->load(Yii::$app->request->get())) {
      $model->sendRequest();
      $response = $model->getResponse();

      if ($response === null) {
        die('Api not work, sorry');
      }

      $decode = json_decode($model->getResponse(), true);

      if ($decode === null || Yii::$app->request->get('format') === 'raw') {
        die('<pre>' . print_r($model->getResponse(), true) . '</pre>');
      }

      die('<pre>' . print_r($decode, true) . '</pre>');
    }

    if (empty($model->type)) {
      $model->type = ProviderTestForm::TYPE_LANDING;
    }

    return $this->render('test', [
      'model' => $model,
      'providersDropdownItems' => (new Landing())->getProviders(false),
    ]);
  }

  public function actionTestProvider()
  {
    $model = new ProviderTestForm;

    if (!$model->load(Yii::$app->request->get())) {
      die('<pre>' . $model->errors . '</pre>');
    }

    $provider = $this->findModel($model->providerId);
    if (!$settings = $provider->getSettings() instanceof ProviderSettingsKp) {
      die('<pre>Provider does not support tests</pre>');
    }

    $providerTester = new KpProviderTester($provider);

    $tdsTestStatus = $postbackTestStatus = $landingApiTestStatus = false;
    try {
      $tdsTestStatus = $providerTester->isTdsWorking();
    } catch (\Exception $e) {
      $tdsTestStatus = false;
      Yii::error($e->getMessage(), __METHOD__);
    }

    try {
      $landingApiTestStatus = $providerTester->isLandingsApiWorking();
    } catch (\Exception $e) {
      Yii::error($e->getMessage(), __METHOD__);
      $landingApiTestStatus = false;
    }

    try {
      $postbackTestStatus = $providerTester->isPostbackWorking();
    } catch(\Exception $e) {
      Yii::error($e->getMessage(), __METHOD__);
      $postbackTestStatus = false;
    }

    echo '<pre>';
    echo 'Testing KP provider ' . $provider->name . PHP_EOL;
    echo 'Tds: ' . ($tdsTestStatus ? 'OK' : 'Error') . PHP_EOL;
    echo 'landings api: ' . ($landingApiTestStatus? 'OK' : 'Error') . PHP_EOL;
    echo 'Postback: ' . ($postbackTestStatus? 'OK' : 'Error') . PHP_EOL;
    echo '</pre>';
  }

  public function actionMakeSync()
  {
    $this->makeSync(true);
  }

  public function actionMakeFullSync()
  {
    $this->makeSync(false);
  }

  /**
   * @param bool $checkUpdateTime
   */
  public function makeSync($checkUpdateTime)
  {
    $formModel = new ProviderTestForm;

    if ($formModel->load(Yii::$app->request->get())) {
      $provider = $this->findModel($formModel->providerId);

      $handlerClass = 'mcms\promo\components\handlers\\' . $provider->handler_class_name;

      if (!$provider->handler_class_name || !class_exists($handlerClass)) {
        throw new RuntimeException("Error: Provider handler class name not found! $handlerClass");
      }

      /* @var $handler ProviderSyncInterface */
      $handler = new $handlerClass($provider);

      /*
       * AUTH
       */
      $authResult = $handler->auth();

      if (!$authResult) {
        throw new RuntimeException('Auth Error');
      }

      if ($formModel->type === ProviderTestForm::TYPE_COUNTRY) {
        $handler->syncCountries($checkUpdateTime, false);
      }

      if ($formModel->type === ProviderTestForm::TYPE_OPERATOR) {
        $handler->syncOperators($checkUpdateTime, false);
      }

      if ($formModel->type === ProviderTestForm::TYPE_LANDING) {
        $syncLandingIds = $handler->syncLandings($checkUpdateTime);

        /**
         * SEND NOTIFICATIONS
         * tricky копипаста из @see SyncProvidersController
         */
        $activeLandingList = [];
        foreach ($syncLandingIds as $providerCodeNotify => $landingIds) {
          $landingList = Landing::find()
            ->joinWith('provider')
            ->where([
              Provider::tableName() . '.code' => $providerCodeNotify,
              'send_id' => $landingIds,
              Landing::tableName() . '.status' => Landing::STATUS_ACTIVE,
              'access_type' => Landing::ACCESS_TYPE_NORMAL,
            ])
            ->all();

          $activeLandingList = ArrayHelper::merge($activeLandingList, $landingList);
        }

        if (!empty($activeLandingList)) {
          $landingIdList = array_map(function (Landing $landing) {
            return $landing->id;
          }, $activeLandingList);

          if (count($landingIdList) === 1) {
            (new LandingCreated(reset($activeLandingList)))->trigger();
            (new LandingCreatedReseller(reset($activeLandingList)))->trigger();
          } elseif (count($landingIdList) > 1) {
            (new LandingListCreated($activeLandingList))->trigger();
            (new LandingListCreatedReseller($activeLandingList))->trigger();
          }
        }
      }

      $this->flashSuccess(Yii::_t('app.common.operation_success'));
    }
    $this->redirect(['test']);
  }
}
