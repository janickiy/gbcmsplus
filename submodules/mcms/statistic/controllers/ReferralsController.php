<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use mcms\statistic\models\mysql\PartnerReferrals;
use mcms\user\models\User;
use Yii;

/**
 * Class ReferralsController
 * @package mcms\statistic\controllers
 */
class ReferralsController extends AdminBaseController
{

  public $layout = '@app/views/layouts/main';


  /**
   * @return string
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('main.by_referrals');

    /** @var \mcms\statistic\components\api\ReferralsApi $api */
    $api = $this->module->api('referrals', ['requestData' => Yii::$app->request->get()]);

    list($model, $dataProvider) = $api->getResult();

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $dataProvider->setPagination(['pageSize' => Yii::$app->getModule('statistic')->getExportLimit()]);
    }

    return $this->render('index', [
      'dataProvider' => $dataProvider,
      'model' => $model,
      'filterDatePeriods' => $this->getFilterDatePeriods(),
      'exportFileName' => $this->exportFileName($model->start_date, $model->end_date),
      'exportWidgetId' => $exportWidgetId,
    ]);
  }

  /**
   * Статистика по реферралам определенного партнера
   * @param int $user_id Партнер
   * @param string $start_date Начальная дата
   * @param string $end_date Конечная дата
   * @return string
   */
  public function actionPartnerModal($user_id, $start_date, $end_date)
  {
    $filter = [
      'user_id' => $user_id,
      'start_date' => $start_date,
      'end_date' => $end_date,
    ];
    $user = User::findOne($user_id);
    $this->getView()->title = Yii::_t('main.referrals_by_partner', ['user' => $user->getViewLabel()]);
    /** @var \mcms\statistic\components\api\ReferralsApi $api */
    $api = $this->module->api('partnerReferrals', ['requestData' => [(new PartnerReferrals)->formName() => $filter]]);

    list($model, $dataProvider) = $api->getResult();

    return $this->renderAjax('partner', [
      'dataProvider' => $dataProvider,
      'model' => $model,
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

  private function exportFileName($startDate, $endDate)
  {
    return sprintf(
      "statistic_by_referrals_%s-%s",
      preg_replace('/\D/', '', $startDate),
      preg_replace('/\D/', '', $endDate)
    );
  }

}