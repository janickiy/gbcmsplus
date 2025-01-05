<?php

namespace mcms\statistic\controllers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\web\AjaxResponse;
use mcms\statistic\assets\CsvExportAsset;
use mcms\statistic\components\ExportMenuCsv;
use mcms\statistic\components\ReturnSell;
use mcms\statistic\models\mysql\DetailStatistic;
use mcms\statistic\models\mysql\DetailStatisticComplains;
use mcms\statistic\models\mysql\DetailStatisticHit;
use mcms\statistic\models\mysql\StatFilter;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class DetailController extends AbstractStatisticController
{

  public $layout = '@app/views/layouts/main';

  public function actionDownloadCsv()
  {
    $url = Yii::$app->request->get('url');
    $exportType = Yii::$app->request->get('export_type');
    $selectedAttrs = explode(',', Yii::$app->request->get('attrs'));

    $parsedUrl = parse_url($url);
    $requestData = [];
    if (isset($parsedUrl['query'])) {
      parse_str($parsedUrl['query'], $requestData);
    }

    // определяем по пути какую группировку используем
    if (!in_array($exportType, [DetailStatistic::GROUP_SUBSCRIPTIONS, DetailStatistic::GROUP_IK, DetailStatistic::GROUP_SELLS, DetailStatisticComplains::GROUP_NAME])) {
      throw new Exception('Unknown exportType');
    }
    $requestData['statistic']['group'] = $exportType;

    // безсмысленно только даты указывать, так как есть еще другие фильтры, поэтому в названии файла выводим просто дату выгрузки
//    $filename = 'detail_' . $exportType . '_' . ArrayHelper::getValue($requestData['statistic'], 'start_date', '...') . '_' . ArrayHelper::getValue($requestData['statistic'], 'end_date', '...') . '.csv';
    $filename = 'detail_' . $exportType . '_exported_at_' . date('Y-m-d') . '.csv';

    $dateFormat = function ($value) {
      if ($value) {
        return Yii::$app->formatter->asDatetime($value);
      }
    };
    $phoneFormat = function ($value) {
      return Yii::$app->user->can('StatisticViewFullPhone')
        ? $value
        : Yii::$app->getFormatter()->asProtectedPhone($value);
    };
    $formats = [
      'email' => function ($email, $row) {
        return '#' . ArrayHelper::getValue($row, 'user_id') . '. ' . $email;
      },
      'landing_name' => function ($landingName, $row) {
        return '#' . ArrayHelper::getValue($row, 'landing_id') . '. ' . $landingName;
      },
      'ip' => function ($value) {
        return long2ip($value);
      },
      'phone' => $phoneFormat,
      'phone_number' => $phoneFormat,
      'sold_at' => $dateFormat,
      'subscribed_at' => $dateFormat,
      'unsubscribed_at' => $dateFormat,
      'last_rebill_at' => $dateFormat,
      'time' => $dateFormat,
    ];
    $attrs = [];
    foreach ($selectedAttrs as $attr) {
      if (isset($formats[$attr])) {
        $attrs[$attr] = $formats[$attr];
      } else {
        $attrs[$attr] = null;
      }
    }

    ExportMenuCsv::export($filename, $requestData, 'detail', $attrs);
    exit;
  }

  public function actionSubscriptions()
  {
    CsvExportAsset::register($this->getView());
    $this->getView()->title = Yii::_t('main.detail-statistic-index');

    $filterParams = $this->adaptFilterParams(Yii::$app->request->get(), [
      [
        'param' => 'debitSumFrom',
        'analog' => 'rebillSumFrom',
      ],
      [
        'param' => 'debitSumTo',
        'analog' => 'rebillSumTo',
      ],
    ]);
    $statisticParams = ['requestData' => $filterParams];

    /** @var \mcms\statistic\components\api\DetailStatistic $api */
    $api = $this->module->api('detailStatistic', $statisticParams);

    list($model, $arrayDataProvider) = $api->getGroupStatistic();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $arrayDataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }

    $countries = $this->getCountries($model);

    return $this->render('subscription', [
      'dataProvider' => $arrayDataProvider,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'model' => $model,
      'currentGroup' => DetailStatistic::GROUP_SUBSCRIPTIONS,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  /**
   * @return array
   */
  public function actionFindUser()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return UsersHelper::select2Users(Yii::$app->request->get('q'));
  }

  /**
   * @return array
   */
  public function actionFindSource()
  {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return Yii::$app->getModule('promo')
      ->api('sources')
      ->setResultTypeMap()
      ->setMapParams(['id', 'name'])
      ->getResult();

    return UsersHelper::select2Users(Yii::$app->request->get('q'));
  }

  public function actionSubscriptionDetail($id)
  {
    $this->getView()->title = Yii::_t('main.detail-statistic-info');
    /** @var \mcms\statistic\components\api\DetailStatisticInfo $api */
    $api = $this->module->api('detailStatisticInfo', [
      'id' => $id,
      'statisticType' => DetailStatistic::GROUP_SUBSCRIPTIONS
    ]);

    $rebillsDataProvider = new ActiveDataProvider([
      'query' => (new Query())
        ->from('subscription_rebills')
        ->where(['hit_id' => $id])
    ]);

    list($model, $record) = $api->getResult();

    return $this->renderAjax('detail-subscription-info', [
      'model' => $model,
      'record' => $record,
      'rebillsDataProvider' => $rebillsDataProvider
    ]);
  }

  public function actionIk()
  {
    CsvExportAsset::register($this->getView());
    $this->getView()->title = Yii::_t('main.detail-statistic-ik');

    $filterParams = $this->adaptFilterParams(Yii::$app->request->get(), [
      [
        'param' => 'rebillSumFrom',
        'analog' => 'debitSumFrom',
      ],
      [
        'param' => 'rebillSumTo',
        'analog' => 'debitSumTo',
      ],
    ]);

    /** @var \mcms\statistic\components\api\DetailStatistic $api */
    $api = $this->module->api('detailStatistic', [
      'requestData' => ArrayHelper::merge(
        ['statistic' => ['group' => DetailStatistic::GROUP_IK]],
        $filterParams
      )
    ]);

    list($model, $arrayDataProvider) = $api->getGroupStatistic();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $arrayDataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }

    $countries = $this->getCountries($model);

    return $this->render('ik', [
      'dataProvider' => $arrayDataProvider,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'model' => $model,
      'currentGroup' => DetailStatistic::GROUP_IK,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  public function actionIkDetail($id)
  {
    $this->getView()->title = Yii::_t('main.detail-statistic-info');

    /** @var \mcms\statistic\components\api\DetailStatisticInfo $api */

    $api = $this->module->api('detailStatisticInfo', [
      'id' => $id,
      'statisticType' => DetailStatistic::GROUP_IK
    ]);

    list($model, $record) = $api->getResult();

    return $this->renderAjax('detail-ik-info', [
      'model' => $model,
      'record' => $record
    ]);
  }

  public function actionSells()
  {
    CsvExportAsset::register($this->getView());
    $this->getView()->title = Yii::_t('main.detail-statistic-sells');

    /** @var \mcms\statistic\components\api\DetailStatistic $api */
    $api = $this->module->api('detailStatistic', [
      'requestData' => ArrayHelper::merge(
        ['statistic' => ['group' => DetailStatistic::GROUP_SELLS]],
        Yii::$app->request->get()
      )
    ]);

    list($model, $arrayDataProvider) = $api->getGroupStatistic();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $arrayDataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }

    $countries = $this->getCountries($model);

    return $this->render('sells', [
      'dataProvider' => $arrayDataProvider,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'model' => $model,
      'currentGroup' => DetailStatistic::GROUP_SELLS,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  public function actionSellsDetail($id)
  {
    $this->getView()->title = Yii::_t('main.detail-statistic-info');

    /** @var \mcms\statistic\components\api\DetailStatisticInfo $api */
    $api = $this->module->api('detailStatisticInfo', [
      'id' => $id,
      'statisticType' => DetailStatistic::GROUP_SELLS
    ]);

    list($model, $record) = $api->getResult();

    return $this->renderAjax('detail-sells-info', [
      'model' => $model,
      'record' => $record
    ]);
  }

  public function actionSellReturn($id)
  {
    $sell = new ReturnSell(['hitId' => $id]);
    return AjaxResponse::set($sell->setVisibleToPartner());
  }

  public function actionComplains()
  {
    CsvExportAsset::register($this->getView());
    $this->getView()->title = Yii::_t('main.detail-statistic-complains');

    /** @var \mcms\statistic\components\api\DetailStatistic $api */
    $api = $this->module->api('detailStatistic', [
      'requestData' => ArrayHelper::merge(
        ['statistic' => ['group' => DetailStatisticComplains::GROUP_NAME]],
        Yii::$app->request->get()
      )
    ]);

    list($model, $arrayDataProvider) = $api->getGroupStatistic();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $arrayDataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }

    $countries = $this->getCountries($model);

    return $this->render('complains', [
      'dataProvider' => $arrayDataProvider,
      'operatorsId' => StatFilter::getOperatorIdList(),
      'countries' => $countries,
      'countriesId' => array_keys($countries),
      'model' => $model,
      'currentGroup' => DetailStatisticComplains::GROUP_NAME,
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  public function actionComplainDetail($id)
  {

    /** @var \mcms\statistic\components\api\DetailStatisticInfo $api */
    $api = $this->module->api('detailStatisticInfo', [
      'id' => $id,
      'statisticType' => DetailStatisticComplains::GROUP_NAME
    ]);

    list($model, $record) = $api->getResult();

    return $this->renderAjax('detail-complain-info', [
      'model' => $model,
      'record' => $record
    ]);
  }

  public function actionHit()
  {
    $requestData = Yii::$app->request->get((new DetailStatisticHit)->formName());
    $id = (int)ArrayHelper::getValue($requestData, 'id');

    $this->getView()->title = Yii::_t('main.detail-statistic-hit');

    $model = new DetailStatistic;
    $statisticModel = new DetailStatisticHit;
    $record = null;
    if ($id) {
      /** @var \mcms\statistic\components\api\DetailStatisticInfo $api */
      $api = $this->module->api('detailStatisticInfo', [
        'id' => $id,
        'statisticType' => DetailStatisticHit::GROUP_NAME,
      ]);

      list($statisticModel, $record) = $api->getResult();
      // Вместо $statisticModel->load()
      $statisticModel->id = $id;
    }

    $isHitBelongToManagersPartner = $record ? Yii::$app->user->identity->canViewUser($record['user_id']) : null;

    return $this->render('hit', [
      'model' => $model,
      'statisticModel' => $statisticModel,
      'record' => $record,
      'isHitBelongToManagersPartner' => $isHitBelongToManagersPartner,
    ]);
  }

  /**
   * Адаптировать параметры фильтрации предыдущего раздела детальной статистики под текущий.
   * Параметры фильтрации разных разделов детальной статистики очень похожи.
   * Что бы не вводить в каждом разделе одни и теже параметры было сделано так, что бы при переходе из одного раздела в другой
   * параметры фильтрации тоже переносились.
   * Что бы перенести параметры фильтрации из одной статы в другую, при нажатии на кнопку перехода в другую стату
   * JSом из формы собираются текщие параметры фильтрации и дополняются к URL ссылки, по которой был клик.
   * Есть одна проблема. Некоторые параметры фильтрации имеют разный код (в модели), но при этом выполняют одну и туже роль
   * в разных разделах статистики. Например сумма списаний в подписках называется debitSumFrom, а в ИК - rebillSumFrom.
   * Этот метод решает эту проблему. При переходе из ИК в подписки параметр rebillSumFrom будет переименован в debitSumFrom.
   *
   * @param array $request Параметры фильтрации. Например Yii::$app->request->get()
   * @param array $paramsMap Соответствие параметров текущего с другими разделами
   * Формат:
   * ```
   * [
   *    [
   *        'param' => 'ИМЯ_ПАРАМЕТРА_В_ТЕКУЩЕМ_РАЗДЕЛЕ',
   *        'analog' => ['АНАЛОГИ_ПАРАМЕТРА_В_ДРУГИХ_РАЗДЕЛАХ'] // может быть строкой или массивом строк
   *    ],
   * ]
   * ```
   * @return array
   */
  private function adaptFilterParams($request, $paramsMap)
  {
    if (!isset($request['statistic'])) {
      return $request;
    }

    $params = &$request['statistic'];

    // Перебор соответствий
    foreach ($paramsMap as $mapItem) {
      // Перебор аналогов
      foreach ((array)$mapItem['analog'] as $analog) {
        if (!isset($params[$analog])) continue;
        // Переименование аналога в соответствующее название для текущего раздела статистики
        $params[$mapItem['param']] = $params[$analog];
        unset($params[$analog]);
        break;
      }
    }

    return $request;
  }
}