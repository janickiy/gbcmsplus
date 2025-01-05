<?php

namespace mcms\statistic\commands;

use mcms\common\controller\ConsoleController;
use mcms\statistic\components\cron\handlers\BalanceByUserAndDateCompare;
use Yii;

/**
 * Сравнение баллансов до рефакторинга и после
 */
class CompareBalancesController extends ConsoleController
{

  public function actionIndex()
  {
    BalanceByUserAndDateCompare::run();
  }

  /**
   * Удаление таблицы бекапа старых профитов
   */
  public function actionCheckBackupDelete()
  {
    Yii::$app->db->createCommand()->dropTable('temporary_old_user_balances_grouped_by_day')->execute();
  }

}
