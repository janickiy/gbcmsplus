<?php
namespace mcms\statistic\controllers\apiv1;

use helpers\CacheHelper;
use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\api\MainStatistic;
use mcms\statistic\models\mysql\Statistic;
use Yii;
use mcms\common\controller\ApiController;
use yii\data\ArrayDataProvider;

/**
 * Class ApiController
 */
class StatisticController extends ApiController
{
  const CACHE_KEY_STATISTIC_TODAY = 'cache_key_statistic_today__';
  const CACHE_KEY_STATISTIC_WEEK = 'cache_key_statistic_week__';
  const CACHE_KEY_STATISTIC_TODAY_DURATION = 300;
  const CACHE_KEY_STATISTIC_WEEK_DURATION = 300;

  private function getConfig(Statistic $model)
  {
    return [
      [
        'label' => 'date',
        'value' => function ($model, $item) {
          return ArrayHelper::getValue($item, 'group');
        }
      ],
      [
        'attribute' => 'currency',
        'value' => function (Statistic $model, $item) {
          return $model->getUserCurrency();
        }
      ],
      /** TRAFFIC */
      [
        'attribute' => 'count_hits',
        'label' => 'traffic.hits',
        'format' => 'integer'
      ],
      [
        'attribute' => 'count_uniques',
        'format' => 'integer',
        'label' => 'traffic.uniques',
      ],
      [
        'attribute' => 'count_tb',
        'format' => 'integer',
        'label' => 'traffic.tb',
        'value' => function (Statistic $model, $item) {
          // Передаем фильтры в страницу со статистикой ТБ
          $fields = $model->getFilterFields();
          $urlParams = [];
          foreach ($fields AS $field) {
            if (!$model->$field) continue;
            $urlParams[$field] = $model->$field;
          }

          // Передаем фильтр для строки, по которой группируем
          switch ($model->group[0]) {
            case 'date':
              $urlParams['start_date'] = $urlParams['end_date'] = ArrayHelper::getValue($item, 'group');
              break;
            case 'hour':
              $urlParams['start_date'] = $urlParams['end_date'] = $model->end_date;
              break;
            default:
              $urlParams[$model->group[0]] = [ArrayHelper::getValue($item, $model->getMappedGroupField())];
              $urlParams['start_date'] = $model->start_date;
              $urlParams['end_date'] = $model->end_date;
          }

          return ArrayHelper::getValue($item, 'count_tb', 0);
        },

      ],
      [
        'label' => 'traffic.accepted',
        'format' => 'integer',
        'value' => function (Statistic $model, $item) {
          return $model->getAcceptedValue($item, $model->revshareOrCPA);
        },
      ],

      /** REVSHARE */
      [
        'attribute' => 'count_ons',
        'format' => 'integer',
        'label' => 'revshare.ons',

        'visible' => !$model->isCPA(),

      ],
      [
        'label' => 'revshare.ratio',
        'value' => function (Statistic $model, $item) {
          return $model->getRevshareRatio($item, '1:%s');
        },

        'visible' => !$model->isCPA(),

      ],
      [
        'attribute' => 'count_offs',
        'format' => 'integer',
        'label' => 'revshare.offs',

        'visible' => !$model->isCPA(),

      ],
      [
        'attribute' => 'count_longs',
        'format' => 'integer',
        'label' => 'revshare.rebills',

        'visible' => !$model->isCPA(),

      ],
      [
        'label' => 'revshare.sum',
        'attribute' => 'sum_profit_rub',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isCPA(),

      ],
      [
        'label' => 'revshare.sum',
        'attribute' => 'sum_profit_usd',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isCPA(),

      ],
      [
        'label' => 'revshare.sum',
        'attribute' => 'sum_profit_eur',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isCPA(),
      ],
      /** CPA */
      [
        'label' => 'cpa.count',
        'format' => 'integer',
        'value' => function (Statistic $model, $item) {
          return $model->getCPACount($item);
        },
        'visible' => !$model->isRevshare(),
      ],
      [
        'label' => 'cpa.ecpm',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getECPM($item, 'rub');
        },

      ],
      [
        'label' => 'cpa.ecpm',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getECPM($item, 'usd');
        },
      ],
      [
        'label' => 'cpa.ecpm',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getECPM($item, 'eur');
        },
      ],
      [
        'label' => 'cpa.ratio',
        'value' => function (Statistic $model, $item) {
          return $model->getCPARatio($item, '1:%s');
        },
        'visible' => !$model->isRevshare(),
      ],
      [
        'label' => 'cpa.sum',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getCPASum($item, 'rub');
        },
      ],
      [
        'label' => 'cpa.sum',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getCPASum($item, 'usd');
        },
      ],
      [
        'label' => 'cpa.sum',
        'format' => 'decimal',
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur') && !$model->isRevshare(),
        'value' => function (Statistic $model, $item) {
          return $model->getCPASum($item, 'eur');
        },
      ],
      [
        'label' => 'total_sum',
        'format' => 'decimal',
        'value' => function (Statistic $model, $item) {
          return $model->getTotalProfit($item, 'rub');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('rub'),
      ],
      [
        'label' => 'total_sum',
        'format' => 'decimal',
        'value' => function (Statistic $model, $item) {
          return $model->getTotalProfit($item, 'usd');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('usd'),
      ],
      [
        'label' => 'total_sum',
        'format' => 'decimal',
        'value' => function (Statistic $model, $item) {
          return $model->getTotalProfit($item, 'eur');
        },
        'visible' => $model->canViewPartnerProfit() && $model->canViewColumnByCurrency('eur'),
      ]
    ];
  }

  private function getStatistic($startDate, $endDate)
  {
    $apiClass = new MainStatistic(['requestData' => [
      'statistic' => [
        'group' => 'date',
        'start_date' => $startDate,
        'end_date' => $endDate,
        'revshareOrCPA' => 'all'
      ]
    ]]);
    /** @var $statisticArrayDataProvider ArrayDataProvider */
    /** @var $model Statistic */
    list($model, $statisticArrayDataProvider) = $apiClass->getGroupStatistic();
    return $this->handleResult($model, $statisticArrayDataProvider, $this->getConfig($model));
  }

  public function actionToday()
  {
    if (($data = Yii::$app->cache->get(self::CACHE_KEY_STATISTIC_TODAY . Yii::$app->user->id)) === false) {
      $data = current($this->getStatistic(Yii::$app->formatter->asGridDate(time()), Yii::$app->formatter->asGridDate(time())));
      Yii::$app->cache->set(
        self::CACHE_KEY_STATISTIC_TODAY . Yii::$app->user->id,
        $data,
        self::CACHE_KEY_STATISTIC_TODAY_DURATION
      );
    }
    return $data;
  }

  public function actionWeek()
  {
    if (($data = Yii::$app->cache->get(self::CACHE_KEY_STATISTIC_WEEK . Yii::$app->user->id)) === false) {
      $data = $this->getStatistic(
        Yii::$app->formatter->asGridDate(strtotime('-7 days')),
        Yii::$app->formatter->asGridDate(time())
      );
      Yii::$app->cache->set(
        self::CACHE_KEY_STATISTIC_WEEK . Yii::$app->user->id,
        $data,
        self::CACHE_KEY_STATISTIC_WEEK_DURATION
      );
    }
    return $data;
  }
}
