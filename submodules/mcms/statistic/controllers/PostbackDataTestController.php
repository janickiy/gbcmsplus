<?php

namespace mcms\statistic\controllers;

use mcms\common\controller\AdminBaseController;
use rgk\utils\actions\IndexAction;
use mcms\statistic\models\search\PostbackDataTestSearch;

/**
 * PostbackDataTestController implements the CRUD actions for PostbackDataTest model.
 */
class PostbackDataTestController extends AdminBaseController
{
  /**
   * @inheritdoc
   */
  public function actions()
  {
    return [
      'index' => [
        'class' => IndexAction::class,
        'modelClass' => PostbackDataTestSearch::class,
      ],
    ];
  }

}
