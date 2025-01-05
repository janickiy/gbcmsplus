<?php

namespace mcms\statistic\commands;

use mcms\common\controller\ConsoleController;
use mcms\statistic\components\cron\CronParams;
use mcms\statistic\components\cron\handlers\DashboardLandings;
use mcms\statistic\components\cron\handlers\DashboardProfitsOns;
use Yii;
use yii\helpers\Console;

/**
 * Class DashboardController
 * @package mcms\statistic\commands
 */
class DashboardController extends ConsoleController
{
  /**
   * можно выключить профайлер запросов
   * @var bool
   */
  public $isProfilerEnabled = false;

  /**
   * Обновление таблиц дашборда
   * @param int $days За сколько последних дней обновлять данные
   */
  public function actionIndex($days = 1)
  {
    $fromTime = time() - 3600 * 24 * ((int)$days);
    $params = new CronParams(['fromTime' => $fromTime]);

    $this->stdout('Today: ' . Yii::$app->formatter->asDate(time()), Console::FG_GREEN);
    $this->stdout('From date: ' . Yii::$app->formatter->asDate($params->fromTime), Console::FG_GREEN);

    $this->stdout("   DashboardLandings...\n", Console::FG_GREEN);
    (new DashboardLandings(['params' => $params]))->run();

    $this->stdout("   DashboardProfitsOns...\n", Console::FG_GREEN);
    (new DashboardProfitsOns(['params' => $params]))->run();

    // профилирование запросов
    if ($this->isProfilerEnabled) {
      $db_log = Yii::getLogger()->getProfiling(['yii\db*']);
      foreach ($db_log as $query) {
        $this->stdout($query['info'] . "\n" . round($query['duration'], 5) . " sec", Console::FG_GREY);
      }
    }
  }
}