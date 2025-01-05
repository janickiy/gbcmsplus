<?php

namespace mcms\statistic\components\widgets;

use mcms\statistic\models\resellerStatistic\ItemSearch;
use yii\base\Widget;


/**
 * Строка Totals в шапке статистики
 */
class Totals extends Widget
{

  public $viewPath = 'totals';

  /**
   * @inheritdoc
   */
  public function run()
  {
    // TRICKY Аналогичная логика есть в \mcms\payments\components\UserBalance::getResellerBalance
    $searchModel = (new ItemSearch());
    $models = $searchModel->search([])->getModels();
    $item = reset($models);
    return $this->render($this->viewPath, [
      'item' => $item,
      'searchModel' => $searchModel
    ]);
  }
}