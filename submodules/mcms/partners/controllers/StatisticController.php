<?php

namespace mcms\partners\controllers;

use mcms\common\controller\SiteBaseController;
use mcms\common\helpers\ArrayHelper;
use mcms\partners\components\subidStat\Fetch;
use mcms\partners\components\mainStat\FiltersDataProvider;
use mcms\partners\components\mainStat\FormModel;
use mcms\partners\components\subidStat\FormModel as LabelFormModel;
use mcms\partners\components\mainStat\Row;
use mcms\partners\components\subidStat\Row as LabelStatRow;
use mcms\partners\components\widgets\PriceWidget;
use mcms\statistic\components\api\LabelStatisticEnable;
use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class StatisticController extends SiteBaseController
{
  public $controllerTitle;
  public $theme = 'basic';

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'get-partners-alive-subscriptions' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->menu = [];
    if (Yii::$app->user->can('PartnersStatisticIndex')) {
      $this->menu[] = [
        'label' => Yii::_t('partners.statistic.statistic_main'),
        'active' => $this->action->id == 'index',
        'url' => ['index'],
      ];
    }


    /** @var LabelStatisticEnable $labelStatisticEnableApi */
    $labelStatisticEnableApi = Yii::$app->getModule('statistic')->api('labelStatisticEnable');

    $canViewLabelStatistic = Yii::$app->user->can('PartnersStatisticLabel');
    $labelStatisticEnabledByUser = $labelStatisticEnableApi->getIsEnabledByUser(Yii::$app->user->id);
    $subidActive = $this->isSubidStatActive();

    if ($canViewLabelStatistic && $labelStatisticEnabledByUser && $subidActive) {
      $this->menu[] = [
        'label' => Yii::_t('partners.statistic.statistic_subid'),
        'active' => $this->action->id === 'subid',
        'url' => ['subid'],
      ];
    }

    $this->controllerTitle = Yii::_t('partners.main.statistic');

    if ($action->actionMethod === 'actionGetBalance') {
      $this->enableCsrfValidation = false;
    }

    return parent::beforeAction($action);
  }

  public function actionTb()
  {

    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('partners.statistic.statistic_tb');

    // Обычно грид фильтруем по пост-параметрам, но при переходе из основной статы ловим гет-параметры
    $requestData = Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();
    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    /* @var $module Module*/
    $module = Yii::$app->getModule('statistic');
    /** @var \mcms\statistic\components\api\MainStatistic $apiClass */
    $apiClass = $module->api('tbStatistic', ['requestData' => $requestData]);

    list($model, $statisticDataProvider) = $apiClass->getGroupStatistic();

    $exportDataProvider = clone $statisticDataProvider; /* @var $exportDataProvider ActiveDataProvider */
    $exportDataProvider->setPagination([
      'pageSize' => $module->getExportLimit()
    ]);

    return $this->render('tb/index', [
      'dataProvider' => $statisticDataProvider,
      'exportDataProvider' => $exportDataProvider,
      'model' => $model,
      'filterDatePeriods' => [
        'today' => [
          'from' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'yesterday' => [
          'from' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
        ],
        'week' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-6 days'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'month' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1 month'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
      ],
      'exportFileName' => $this->exportFileName('tb', $model->start_date, $model->end_date),
      'exportWidgetId' => $exportWidgetId,

    ]);
  }

  /**
   * @return string
   * @throws NotFoundHttpException
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function actionSubid()
  {
    if (!$this->isSubidStatActive()) {
      throw new NotFoundHttpException('Partner can not open subid statistic (isSubidStatActive=false)');
    }
    return $this->getSubidStatistic();
  }

  /**
   * @return bool
   */
  private function isSubidStatActive()
  {
    return (bool)ArrayHelper::getValue(Yii::$app->params, 'isSubidStatActive');
  }

  /**
   * Статистика по subid
   * @return string
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  private function getSubidStatistic()
  {
    $formModel = new LabelFormModel();
    // При пагинации данные передаются через GET
    $data = Yii::$app->request->isPost
      ? Yii::$app->request->post()
      : Yii::$app->request->get();

    $formModel->load($data);

    $this->handleRevshareOrCpaCookie($formModel);

    Yii::$container->set(
      BaseFetch::class,
      Fetch::class
    );

    /** @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel], [
      'rowClass' => LabelStatRow::class
    ]);

    /** @var \mcms\statistic\Module $module */
    $module = Yii::$app->getModule('statistic');

    $dataProvider = $fetch->getDataProvider(['db' => 'sdb']);

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';
    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => $module->getExportLimit()]);
    }

    return $this->render('subid/index', [
      'dataProvider' => $dataProvider,
      'searchModel' => $formModel,
      'filtersDP' => FiltersDataProvider::getInstance(),
      'revshareOrCpaFilter' => $this->getRevshareOrCpaFilter(),
      'filterDatePeriods' => [
        'today' => [
          'from' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'yesterday' => [
          'from' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
        ],
        'week' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-6 days'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'month' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1 month'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
      ],
      'shouldHideGrouping' => false,
      'groupBy' => $formModel->getGroupsList(),
      'exportFileName' => $this->exportFileName('main', $formModel->dateFrom, $formModel->dateTo),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  private function exportFileName($type, $startDate, $endDate)
  {
    return sprintf(
      "statistic_%s_%s-%s",
      $type,
      preg_replace('/\D/', '', $startDate),
      preg_replace('/\D/', '', $endDate)
    );
  }

  /**
   * @return array
   */
  public function actionGetBalance()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;

    $paymentsModule = Yii::$app->getModule('payments');
    $userBalance = $paymentsModule
      ->api('userBalance', [
        'userId' => Yii::$app->user->id,
        'currency' => $paymentsModule
          ->api('userSettingsData', ['userId' => Yii::$app->user->id])
          ->getResult()->currency
      ])
      ->getResult();

    return [
      'balance' => PriceWidget::widget([
        'currency' => $userBalance->currency,
        'value' => $userBalance->getBalance(),
        'small' => true,
      ]),
      'todayProfit' => PriceWidget::widget([
        'currency' => $userBalance->currency,
        'value' => $userBalance->getTodayProfit(),
        'small' => true,
      ])
    ];
  }

  /**
   * @return integer
   */
  public function actionGetPartnersAliveSubscriptions()
  {
    return Yii::$app->getModule('statistic')->api('userDayGroupStatistic',[
      'userId' => Yii::$app->user->id,
    ])->getPartnersAliveSubscriptions();
  }

  /**
   * Статистика по жалобам
   * @return string
   */
  public function actionComplains()
  {

    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('partners.statistic.complains');

    // Обычно грид фильтруем по пост-параметрам, но при переходе из основной статы ловим гет-параметры
    $requestData = Yii::$app->request->isPost ? Yii::$app->request->post() : Yii::$app->request->get();

    /** @var \mcms\statistic\components\api\MainStatistic $apiClass */
    $apiClass = Yii::$app
      ->getModule('statistic')
      ->api('complainsStatistic', ['requestData' => $requestData])
    ;

    list($model, $statisticDataProvider) = $apiClass->getGroupStatistic();

    return $this->render('complains/index', [
      'dataProvider' => $statisticDataProvider,
      'model' => $model,
      'filterDatePeriods' => [
        'today' => [
          'from' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'yesterday' => [
          'from' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
        ],
        'week' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-6 days'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'month' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1 month'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
      ]
    ]);
  }

  /**
   * Основная стата
   * @return string
   * @throws \yii\base\InvalidConfigException
   * @throws \yii\di\NotInstantiableException
   */
  public function actionIndex()
  {
    /** @var \mcms\statistic\Module $module */
    $module = Yii::$app->getModule('statistic');
    $isRatioByUniquesEnabled = $module->isRatioByUniquesEnabled();

    $formModel = new FormModel();
    $formModel->isRatioByUniquesEnabled = $isRatioByUniquesEnabled;
    $formModel->load(Yii::$app->request->post());

    $this->handleRevshareOrCpaCookie($formModel);

    Yii::$container->set(Row::class, [
      // TRICKY сделано присвоение через контейнер, чтобы не внедряться из-за этого свойства в класс Fetch
      'isRatioByUniques' => $formModel->isRatioByUniques
    ]);

    /** @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel], ['rowClass' => Row::class]);

    $dataProvider = $fetch->getDataProvider();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => $module->getExportLimit()]);
    }
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'model' => $formModel,
      'isRatioByUniquesEnabled' => $isRatioByUniquesEnabled,
      'isVisibleComplains' => $module->partnerVisibleComplains(),
      'revshareOrCpaFilter' => $this->getRevshareOrCpaFilter(),
      'groupBy' => $this->getGroupBy(),
      'exportWidgetId' => $exportWidgetId,
      'exportFileName' => $this->exportFileName('main', $formModel->dateFrom, $formModel->dateTo),
      'filtersDataProvider' => FiltersDataProvider::getInstance(),
      'showRatio' => $partnersModule->isShowRatio(),
      'filterDatePeriods' => [
        'today' => [
          'from' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'yesterday' => [
          'from' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:d.m.Y'),
        ],
        'week' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-6 days'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
        'month' => [
          'from' => Yii::$app->formatter->asDate(strtotime('-1 month'), 'php:d.m.Y'),
          'to' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y'),
        ],
      ],
    ]);
  }

  /**
   * @return string[]
   */
  protected function getGroupBy()
  {
    $groupsConfig = [
      Group::BY_DATES => [],
      Group::BY_HOURS => [],
      Group::BY_MONTH_NUMBERS => [],
      Group::BY_WEEK_NUMBERS => [],
      Group::BY_LANDINGS => ['permissionCheckMethod' => 'canGroupByLandings'],
      Group::BY_WEBMASTER_SOURCES => ['permissionCheckMethod' => 'canGroupBySources'],
      Group::BY_LINKS => ['permissionCheckMethod' => 'canGroupBySources'],
      Group::BY_STREAMS => ['permissionCheckMethod' => 'canGroupByStreams'],
      Group::BY_PLATFORMS => ['permissionCheckMethod' => 'canGroupByPlatforms'],
      Group::BY_OPERATORS => ['permissionCheckMethod' => 'canGroupByOperators'],
      Group::BY_COUNTRIES => ['permissionCheckMethod' => 'canGroupByCountries']
    ];

    $groupKeys = array_keys($groupsConfig);
    return array_map(
      function ($key) {
        return Yii::_t(Group::TRANSLATE_GROUP_BY_PREFIX . $key);
      },
      array_combine($groupKeys, $groupKeys)
    );

    return array_filter($groupKeys, function ($groupKey) {
      return in_array($groupKey, [
        Group::BY_DATES,
        Group::BY_HOURS,
        Group::BY_MONTH_NUMBERS,
        Group::BY_WEEK_NUMBERS,
        Group::BY_LANDINGS,
        Group::BY_WEBMASTER_SOURCES,
        Group::BY_LINKS,
        Group::BY_STREAMS,
        Group::BY_PLATFORMS,
        Group::BY_OPERATORS,
        Group::BY_COUNTRIES,
      ], true);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * @return string[]
   */
  protected function getRevshareOrCpaFilter()
  {
    return [
      FormModel::SELECT_ALL => Yii::_t('statistic.statistic.all'),
      FormModel::SELECT_REVSHARE => Yii::_t('statistic.statistic.revshare'),
      FormModel::SELECT_CPA => Yii::_t('statistic.statistic.cpa'),
    ];
  }

  /**
   * @param \mcms\statistic\components\mainStat\FormModel $formModel
   */
  private function handleRevshareOrCpaCookie(\mcms\statistic\components\mainStat\FormModel $formModel)
  {
    $cookieRevshareOrCpa = Yii::$app->request->cookies->get('revshareOrCPA');

    if ($cookieRevshareOrCpa !== null && $formModel->revshareOrCpa === null) {
      $formModel->revshareOrCpa = $cookieRevshareOrCpa->value;
    }

    Yii::$app->response->cookies->add(new Cookie([
      'name' => 'revshareOrCPA',
      'value' => $formModel->revshareOrCpa,
      'expire' => time() + 864000
    ]));
  }
}
