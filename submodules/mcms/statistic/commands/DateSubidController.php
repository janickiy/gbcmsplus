<?php

namespace mcms\statistic\commands;

use mcms\statistic\Module;
use yii\console\Controller;

class DateSubidController extends Controller
{
  public function actionIndex($fromDate)
  {
    $fromDateTime = strtotime($fromDate);
    for (;;) {
      $nextDate = $fromDateTime + 86400;
      $handler = new SubidController('SubidController', Module::getInstance(), []);
      $handler->dateFrom = date('Y-m-d', $fromDateTime);
      $handler->dateTo = date('Y-m-d', $nextDate);

      echo "SubidController dateFrom: {$handler->dateFrom} dateTo: {$handler->dateTo}" . PHP_EOL;

      $handler->actionIndex();

      if ($nextDate > time()) break;

      $fromDateTime = $nextDate;
      echo 'Done' . PHP_EOL;
    }
  }
}
