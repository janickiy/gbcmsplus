<?php

namespace mcms\statistic\components;

use mcms\statistic\models\resellerStatistic\Item;
use mcms\statistic\models\resellerStatistic\ItemSearchInterface;
use mcms\statistic\models\resellerStatistic\UnholdPlanSearch;
use yii\base\Object;

/**
 * Ссылка на модалку с планом расхолда захолденных средств
 */
class ResellerStatisticHoldLink extends Object
{
  /**
   * @param Item $item
   * @param $currency
   * @return array
   */
  public function getItemLink(Item $item, $currency)
  {
    $params = [
      'holdDateTo' => $item->group->getDateRightValue(),
    ];

    $params['holdDateFrom'] = $item->group->getDateLeftValue();
    $params['unholdDateFrom'] = \Yii::$app->formatter->asDate('tomorrow', 'php:Y-m-d');

    return [
        '/statistic/reseller-profit/unhold-plan',
        (new UnholdPlanSearch())->formName() => $params,
        'currency' => $currency

    ];
  }

  /**
   * @param ItemSearchInterface $searchModel
   * @param $currency
   * @return array
   */
  public function getFooterLink(ItemSearchInterface $searchModel, $currency)
  {
    $params = [
      'holdDateTo' => $searchModel->dateTo,
      'holdDateFrom' => $searchModel->dateFrom,
      'unholdDateFrom' => \Yii::$app->formatter->asDate('tomorrow', 'php:Y-m-d'),
    ];

    return [
        '/statistic/reseller-profit/unhold-plan',
        (new UnholdPlanSearch)->formName() => $params,
        'currency' => $currency
    ];
  }
}