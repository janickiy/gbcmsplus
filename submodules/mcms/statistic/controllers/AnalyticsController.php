<?php

namespace mcms\statistic\controllers;

use mcms\common\web\AjaxResponse;
use mcms\promo\models\Operator;
use mcms\statistic\models\mysql\Analytics;
use mcms\statistic\models\mysql\AnalyticsLtv;
use mcms\statistic\models\mysql\AnalyticsByDate;
use mcms\statistic\models\mysql\StatFilter;
use Yii;

/**
 * Class AnalyticsController
 * @package mcms\statistic\controllers
 *
 * @todo удалить класс https://rgkdev.atlassian.net/browse/MCMS-2614
 */
class AnalyticsController extends AbstractStatisticController
{

  public $layout = '@app/views/layouts/main';

  /**
   * @return string
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('main.analytics');

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    $statisticParams = ['requestData' => Yii::$app->request->get()];
    $statisticParams['type'] = Analytics::STATISTIC_NAME;
    /** @var \mcms\statistic\components\api\AnalyticsApi $api */
    $api =  $this->module->api('analytics', $statisticParams);

    /** @var \mcms\statistic\models\mysql\Analytics $model */
    list($model, $dataProvider) = $api->getResult();
    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => $this->module->getExportLimit()]);
    }

    $currency = Yii::$app->getModule('promo')->api('mainCurrenciesWidget')->getSelectedCurrency();
    $countries = $this->getCountries($model, $currency);
    $operatorsId = StatFilter::getOperatorIdList($currency);
    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'operatorsId' => $operatorsId ? $operatorsId : [0],
      'countriesId' => array_keys($countries),
      'countries' => $countries,
      'model' => $model,
      'filterDatePeriods' => $this->getFilterDatePeriods(),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  /**
   * @return string
   */
  public function actionByDate()
  {
    $this->getView()->title = Yii::_t('main.analytics-rebills');

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    $statisticParams = ['requestData' => Yii::$app->request->get()];
    $statisticParams['type'] = AnalyticsByDate::STATISTIC_NAME;
    /** @var \mcms\statistic\components\api\AnalyticsApi $api */
    $api = $this->module->api('analytics', $statisticParams);

    /** @var AnalyticsByDate $model */
    list($model, $dataProvider) = $api->getResult();
    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => $this->module->getExportLimit()]);
    }

    $currency = Yii::$app->getModule('promo')->api('mainCurrenciesWidget')->getSelectedCurrency();
    $countries = $this->getCountries($model, $currency);
    $operatorsId = StatFilter::getOperatorIdList($currency);
    return $this->render('by-date', [
      'dataProvider' => $dataProvider,
      'operatorsId' => $operatorsId ? $operatorsId : [0],
      'countriesId' => array_keys($countries),
      'countries' => $countries,
      'model' => $model,
      'filterDatePeriods' => $this->getFilterDatePeriods(),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  /**
   * @return string
   */
  public function actionLtv()
  {
    $this->getView()->title = Yii::_t('main.analytics-ltv');

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    $statisticParams = ['requestData' => Yii::$app->request->get()];
    $statisticParams['type'] = AnalyticsLtv::STATISTIC_NAME;
    /** @var \mcms\statistic\components\api\AnalyticsApi $api */
    $api = $this->module->api('analytics', $statisticParams);

    /** @var AnalyticsLtv $model */
    list($model, $dataProvider) = $api->getResult();
    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => $this->module->getExportLimit()]);
    }

    $currency = Yii::$app->getModule('promo')->api('mainCurrenciesWidget')->getSelectedCurrency();
    $countries = $this->getCountries($model, $currency);
    $operatorsId = StatFilter::getOperatorIdList($currency);
    return $this->render('ltv', [
      'dataProvider' => $dataProvider,
      'operatorsId' => $operatorsId ? $operatorsId : [0],
      'countriesId' => array_keys($countries),
      'countries' => $countries,
      'model' => $model,
      'filterDatePeriods' => $this->getFilterDatePeriods(),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }


  /**
   * @return array
   */
  protected function getFilterDatePeriods()
  {
    return [
      'today' => [
        'from' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
        'to' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
      ],
      'yesterday' => [
        'from' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:Y-m-d'),
        'to' => Yii::$app->formatter->asDate(strtotime('- 1 day'), 'php:Y-m-d'),
      ],
      'week' => [
        'from' => Yii::$app->formatter->asDate(strtotime('- 6 days'), 'php:Y-m-d'),
        'to' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
      ],
      'month' => [
        'from' => Yii::$app->formatter->asDate(strtotime('- 1 month'), 'php:Y-m-d'),
        'to' => Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
      ],
    ];
  }

  /**
   * Получение json массива с операторами и странами для фильтров при переключении валюты
   * @param string $currency
   * @return array
   */
  public function actionFilters($currency)
  {

    $countriesItems = Yii::$app->getModule('promo')
      ->api('countries', [
        'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        'statFilters' => false
      ])
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult()
    ;

    $operatorsId = StatFilter::getOperatorIdList($currency);
    $operatorItems = Operator::getOperatorsDropDown(array_keys($countriesItems), true, false, $operatorsId, false);

    return AjaxResponse::set(true, ['operatorItems' => $operatorItems, 'countriesItems' => $countriesItems]);
  }

}