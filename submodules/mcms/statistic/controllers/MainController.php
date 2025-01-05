<?php

namespace mcms\statistic\controllers;

use mcms\statistic\components\mainStat\BaseFetch;
use mcms\statistic\components\mainStat\FormModel;
use mcms\statistic\components\mainStat\Grid;
use mcms\statistic\components\mainStat\Group;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\models\ColumnsTemplate;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Новая сгруппированная статистика. вынесли в отдельный контроллер, типа начать с чистого листа.
 * Так что постараемся не гадить тут!
 */
class MainController extends AbstractStatisticController
{

  const COOKIE_REVSHARE_OR_CPA = 'revshareOrCPA';
  const COOKIE_DURATION = 864000;

  protected function getFilterDatePeriods($isGroupHour)
  {
    $periods = [
      DatePeriod::PERIOD_TODAY => DatePeriod::getPeriodDates(DatePeriod::PERIOD_TODAY),
      DatePeriod::PERIOD_YESTERDAY => DatePeriod::getPeriodDates(DatePeriod::PERIOD_YESTERDAY)
    ];

    if (!$isGroupHour) {
      $periods = array_merge($periods, [
        DatePeriod::PERIOD_LAST_WEEK => DatePeriod::getPeriodDates(DatePeriod::PERIOD_LAST_WEEK),
        DatePeriod::PERIOD_LAST_MONTH => DatePeriod::getPeriodDates(DatePeriod::PERIOD_LAST_MONTH),
      ]);
    }

    return $periods;
  }

  private function exportFileName($startDate, $endDate)
  {
    return sprintf(
      "statistic_%s-%s",
      preg_replace('/\D/', '', $startDate),
      preg_replace('/\D/', '', $endDate)
    );
  }

  /**
   * Рендерит страницу статы.
   * @return string
   */
  public function actionIndex()
  {
    $this->view->title = Yii::_t('main_statistic_refactored.title');

    // Если передан шаблон - устанавливаем
    if (Yii::$app->request->post('template') !== null) {
      ColumnsTemplate::setTemplate(Yii::$app->request->post('template'));
    }

    $formModel = new FormModel();

    $filters = Yii::$app->request->get('FormModel') ?: [];

    if (empty(ArrayHelper::getValue($filters, 'dateFrom')) && empty(ArrayHelper::getValue($filters, 'dateTo'))) {
      // TRICKY. Если не выбрана никакая дата в фильтре, то надо показать включенной кнопку НЕДЕЛЯ.
      // Потому что каждый раз при открытии статы с нуля, всегда отображается неделя по-умолчанию.
      $filters['forceDatePeriod'] = DatePeriod::PERIOD_LAST_WEEK;
    }

    $formModel->load(['FormModel' => $filters]);

    if (empty($formModel->groups)) {
      $formModel->groups = [Group::BY_DATES];
    }

    $this->switchToHoursIfNeed($formModel);

    /** @var BaseFetch $fetch */
    $fetch = Yii::$container->get(BaseFetch::class, [$formModel]);

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    $dataProvider = $fetch->getDataProvider([
      'sort' => [
        'attributes' => [
          'group' => [
            'default' => SORT_DESC
          ]
        ],
        'defaultOrder' => [
          'group' => SORT_DESC
        ]
      ],
      'pagination' => [
        'pageSize' => empty($_POST['exportFull_' . $exportWidgetId])
          ? 1000
          : Yii::$app->getModule('statistic')->getExportLimit()
      ]
    ]);

    $selectedTemplate = ColumnsTemplate::getSelected();
    $selectedTemplateId = $selectedTemplate ? $selectedTemplate->id : null;
    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'exportFileName' => $this->exportFileName($formModel->dateFrom, $formModel->dateTo),
      'exportGrid' => new Grid(['dataProvider' => $dataProvider, 'statisticModel' => $formModel, 'templateId' => $selectedTemplateId]),
      'formModel' => $formModel,
      'maxGroups' => 2,
      'filterDatePeriods' =>  $this->getFilterDatePeriods(false),
      'landingPayTypes' => $this->getLandingPayTypes(),
      'providers' => $this->getProviders(),
      'platforms' => $this->getPlatforms(),
      'countries' => $this->getCountriesRefactored(),
      'operatorIds' => StatFilter::getOperatorIdList(),
      'landingCategories' => $this->getLandingCategories(),
      'groups' => array_intersect_key(
        Group::getGroupByLabelsAvailable(),
        array_flip([
          Group::BY_DATES,
          Group::BY_MONTH_NUMBERS,
          Group::BY_WEEK_NUMBERS,
          Group::BY_LANDINGS,
          Group::BY_WEBMASTER_SOURCES,
          Group::BY_LINKS,
          Group::BY_STREAMS,
          Group::BY_PLATFORMS,
          Group::BY_OPERATORS,
          Group::BY_COUNTRIES,
          Group::BY_PROVIDERS,
          Group::BY_USERS,
          Group::BY_LANDING_PAY_TYPES,
          Group::BY_MANAGERS,
        ])
      ),
      'columnsTemplates' => ColumnsTemplate::getAllTemplates(),
      'selectedTemplateId' => $selectedTemplateId,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  /**
   * для дропдауна
   * @return array ['id' => 'name']
   */
  private function getLandingPayTypes()
  {
    return Yii::$app->getModule('promo')
      ->api('payTypes', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * для дропдауна
   * @return array ['id' => 'name']
   */
  private function getProviders()
  {
    return Yii::$app->getModule('promo')
      ->api('providers', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]]
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();
  }

  /**
   * для дропдауна
   * @return array ['id' => 'name']
   */
  private function getPlatforms()
  {
    return Yii::$app->getModule('promo')
      ->api('platforms', [
        'conditions' => [
          'id' => [],
        ],
        'statFilters' => true,
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;
  }

  /**
   * для дропдауна
   * @param string $currency
   * @return array ['id' => 'name']
   */
  private function getCountriesRefactored($currency = null)
  {
    return Yii::$app->getModule('promo')
      ->api('countries', [
        'conditions' => [
          'id' => [],
        ],
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        'statFilters' => true,
        'currency' => $currency
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
      ;

  }

  /**
   * Меняем группировку на часы
   * @param FormModel $formModel
   */
  private function switchToHoursIfNeed(FormModel $formModel)
  {
    if (!$formModel->getPermissionsChecker()->canGroupByHours()) {
      return;
    }

    if ($formModel->dateFrom !== $formModel->dateTo) {
      return;
    }

    if (empty($formModel->groups)) {
      return;
    }

    foreach ($formModel->groups as $key => $group) {
      if ($group === Group::BY_DATES) {
        $formModel->groups[$key] = Group::BY_HOURS;
      }
    }
  }

  /**
   * @return array вида [id => name]
   */
  private function getLandingCategories()
  {
    $result = [];
    $cachedLandingCategories = Yii::$app->getModule('promo')->api('cachedLandingCategories')->getResult();
    foreach ($cachedLandingCategories as $category) {
      $result[$category->id] = (string)$category->name;
    }

    return $result;
  }
}
