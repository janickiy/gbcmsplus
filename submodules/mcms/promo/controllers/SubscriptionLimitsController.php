<?php

namespace mcms\promo\controllers;

use mcms\promo\models\search\SubscriptionLimitsSearch;
use mcms\promo\models\SubscriptionsLimit;
use mcms\statistic\controllers\AbstractStatisticController;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\DeleteAjaxAction;
use rgk\utils\actions\IndexAction;
use rgk\utils\actions\UpdateModalAction;

/**
 *
 */
class SubscriptionLimitsController extends AbstractStatisticController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => SubscriptionLimitsSearch::class,
      ],
      'create-modal' => [
        'class' => CreateModalAction::class,
        'modelClass' => SubscriptionsLimit::class,
      ],
      'update-modal' => [
        'class' => UpdateModalAction::class,
        'modelClass' => SubscriptionsLimit::class,
      ],
      'delete' => [
        'class' => DeleteAjaxAction::class,
        'modelClass' => SubscriptionsLimit::class,
      ]
    ];
  }
}
