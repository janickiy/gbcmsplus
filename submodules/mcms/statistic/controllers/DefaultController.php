<?php

namespace mcms\statistic\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\components\api\MainStatistic;
use mcms\statistic\models\mysql\StatFilter;
use mcms\statistic\Module;
use mcms\statistic\models\mysql\Statistic;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Response;
use yii\data\ArrayDataProvider;
use yii\web\Cookie;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;

class DefaultController extends MainController
{

  const COOKIE_REVSHARE_OR_CPA = 'revshareOrCPA';
  const COOKIE_DURATION = 864000;

  public $layout = '@app/views/layouts/main';

  public function beforeAction($action)
  {
    $this->getView()->title = Yii::_t('main.statistic');
    return parent::beforeAction($action);
  }

  /**
   * Статистика в виде таблицы, по датам, часам, лендингам и др. группировки
   * @return string
   */
  public function actionIndex()
  {
    $startDate = Yii::$app->request->get('statistic')['start_date'];
    $endDate = Yii::$app->request->get('statistic')['end_date'];

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    $statisticParams = ['requestData' => Yii::$app->request->get()];
    if (empty($startDate) && empty($endDate)) {
      // TRICKY. Если не выбрана никакая дата в фильтре, то надо показать включенной кнопку НЕДЕЛЯ.
      // Потому что каждый раз при открытии статы с нуля, всегда отображается неделя по-умолчанию.
      $statisticParams['requestData']['statistic']['period'] = DatePeriod::PERIOD_LAST_WEEK;
    }

    $apiClass = new MainStatistic($statisticParams);
    $model = $apiClass->getModel();

    if (($startDate || $endDate) && ($startDate === $endDate) && $model->isGroupingByDate() && Yii::$app->user->can('StatisticGroupByHours')) {
      $model->updateGroup(Module::STATISTIC_MAIN_DATE, Module::STATISTIC_MAIN_HOUR);
    }

    $this->loadRevshareOrCPA($model);

    list($model, $statisticArrayDataProvider) = $apiClass->getGroupStatistic();

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $statisticArrayDataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }


    $this->saveRevshareOrCPA($model);

    $countries = $this->getCountries($model);


    return $this->render('index', [
      'dataProvider' => $statisticArrayDataProvider,
      'model' => $model,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'filterDatePeriods' => $this->getFilterDatePeriods(false),
      'shouldHideGrouping' => false,
      'exportFileName' => $this->exportFileName($model->start_date, $model->end_date),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  public function actionGraphical()
  {
    $statisticParams = ['requestData' => Yii::$app->request->get()];
    $forceChangeGroup = false;

    if (empty($statisticParams['requestData']['statistic']['start_date'])
      && empty($statisticParams['requestData']['statistic']['end_date'])) {
      // TRICKY. Если не выбрана никакая дата в фильтре, то надо показать включенной кнопку НЕДЕЛЯ.
      // Потому что каждый раз при открытии статы с нуля, всегда отображается неделя по-умолчанию.
      $statisticParams['requestData']['statistic']['period'] = DatePeriod::PERIOD_LAST_WEEK;
    }

    $apiClass = new MainStatistic($statisticParams);
    $model = $apiClass->getModel();

    // Если диапазон дат больше 3 дней, принудительно ставим группировку по дням
    if ((strtotime($model->end_date) - strtotime($model->start_date)) > Statistic::MAX_DATE_HOUR_SPAN && !$model->isGroupingBy('date')) {
      $forceChangeGroup = true;
      $model->setGroup('date');
    }

    $this->loadRevshareOrCPA($model);
    $statisticArrayDataProvider = $apiClass->getResult();

    $this->saveRevshareOrCPA($model);

    $data = (new \mcms\statistic\components\grid\StatisticGrid([
      'dataProvider' => $statisticArrayDataProvider,
      'statisticModel' => $model,
    ]))->getGraphicalStatisticData();

    return $this->render('graphical', [
      'data' => $data,
      'model' => $model,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $this->getCountries($model),
      'countriesId' => array_keys($this->getCountries($model)),
      'landingIdList' => StatFilter::getLandingsId(),
      'filterDatePeriods' => $this->getFilterDatePeriods(false),
      'shouldHideGrouping' => true,
      'dayHourGrouping' => true,
      'forceChangeGroup' => $forceChangeGroup,
    ]);

  }

  public function actionHour()
  {
    throw new HttpException(404, 'Sorry. This page was disabled');
    $apiClass = new MainStatistic(['requestData' => Yii::$app->request->post()]);
    $model = $apiClass->getModel();
    $this->loadRevshareOrCPA($model);
    $model->setGroup(Module::STATISTIC_MAIN_HOUR);

    list($model, $statisticArrayDataProvider) = $apiClass->getGroupStatistic();

    $this->saveRevshareOrCPA($model);

    return $this->render('index', [
      'dataProvider' => $statisticArrayDataProvider,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $this->getCountries($model),
      'countriesId' => array_keys($this->getCountries($model)),
      'landingIdList' => StatFilter::getLandingsId(),
      'model' => $model,
      'filterDatePeriods' => $this->getFilterDatePeriods(true),
      'shouldHideGrouping' => true,
      'exportFileName' => 'statistic_' . $model->end_date,
    ]);

  }

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

  /**
   * Загрузка revshareOrCPA из кук
   * @param Statistic $model
   */
  protected function loadRevshareOrCPA(Statistic $model)
  {
    // Если уже задано, выходим
    if ($model->revshareOrCPA) return;
    // Если Pjax, выходим
    if (Yii::$app->getRequest()->isPjax) return;
    // Если кука пустая, выходим
    if (Yii::$app->request->cookies->get('revshareOrCPA') === null) return;

    $cookie = Yii::$app->request->cookies->get('revshareOrCPA');
    $model->revshareOrCPA = $cookie->value;
  }

  protected function saveRevshareOrCPA(Statistic $model)
  {
    Yii::$app->response->cookies->add(new Cookie([
      'name' => self::COOKIE_REVSHARE_OR_CPA,
      'value' => $model->revshareOrCPA,
      'expire' => time() + self::COOKIE_DURATION
    ]));
  }

  private function exportFileName($startDate, $endDate)
  {
    return sprintf(
      "statistic_%s-%s",
      preg_replace('/\D/', '', $startDate),
      preg_replace('/\D/', '', $endDate)
    );
  }
}
