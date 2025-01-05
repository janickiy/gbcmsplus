<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\models\ResellerProfitStatistics;
use mcms\statistic\components\api\MainStatistic;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * Статистика по доходам реселлера
 */
class ResellerProfitStatisticsController extends AdminBaseController
{
  /**
   * @return string
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('statistic.main.reseller_income');

    $statisticParams = ['requestData' => Yii::$app->request->get(), 'type' => ResellerProfitStatistics::STATISTIC_NAME];
    $apiClass = new MainStatistic($statisticParams);
    list($model, $dataProvider) = $apiClass->getGroupStatistic();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }
    $countries = $model->getCountries();

    return $this->render('index', [
      'model' => $model,
      'dataProvider' => $dataProvider,
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'operatorsId' => StatFilter::getOperatorIdList(),
      'filterDatePeriods' => [
        DatePeriod::PERIOD_TODAY => DatePeriod::getPeriodDates(DatePeriod::PERIOD_TODAY),
        DatePeriod::PERIOD_YESTERDAY => DatePeriod::getPeriodDates(DatePeriod::PERIOD_YESTERDAY),
        DatePeriod::PERIOD_LAST_WEEK => DatePeriod::getPeriodDates(DatePeriod::PERIOD_LAST_WEEK),
        DatePeriod::PERIOD_LAST_MONTH => DatePeriod::getPeriodDates(DatePeriod::PERIOD_LAST_MONTH),
      ],
      'showFooter' => $dataProvider->getTotalCount() > 0,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }
}