<?php

namespace mcms\statistic\commands;

use mcms\common\controller\ConsoleController;
use mcms\statistic\components\ResellerStatisticExporter;
use Yii;
use yii\helpers\Console;

/**
 * Консольная команда для подготовки файлов статистики
 *
 * Class ExportController
 * @package mcms\statistic\commands
 *
 * todo Удалить этот класс
 */
class ExportController extends ConsoleController
{

  /** @var bool можно запустить этот крон с флагом --isMonthly
   * php yii statistic/export/reseller-prepare 2017-12 --isMonthly=true
   * чтобы сделать выгрузку за месяц
   */
  public $isMonthly = false;

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(
      parent::options($actionID),
      ['isMonthly']
    );
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    parent::beforeAction($action);

    $this->stdout('Service not working anymore');
    return false;
  }


  /**
   * Выгрузка статистики для реселлера. Результат кладется в виде файла xls.
   * @param null $date
   */
  public function actionResellerPrepare($date = null)
  {
    if ($this->isMonthly) {
      $dateFrom = $date
        ? Yii::$app->formatter->asDate(strtotime('first day of this month', strtotime($date)), 'php:Y-m-d')
        : Yii::$app->formatter->asDate('first day of previous month', 'php:Y-m-d');
      $dateTo = $date
        ? Yii::$app->formatter->asDate(strtotime('last day of this month', strtotime($date)), 'php:Y-m-d')
        : Yii::$app->formatter->asDate('last day of previous month', 'php:Y-m-d');

    } else {
      $dateTo = $date ?: Yii::$app->formatter->asDate('-1 day', 'php:Y-m-d');
      // выгружаем запрошенный день и один предыдущий. Это из-за триала билайна. Иначе для билайна не будет видно кол-во ребиллов за дату
      $dateFrom = Yii::$app->formatter->asDate($dateTo . ' -1 day', 'php:Y-m-d');
    }
    $this->stdout('STATISTIC BY DATE = ' . ($this->isMonthly ? $dateFrom . ' - ' . $dateTo : $dateTo), Console::FG_GREEN);
    (new ResellerStatisticExporter([
      'dateFrom' => $dateFrom,
      'dateTo' => $dateTo,
      'isMonthly' => $this->isMonthly,
    ]))->export();

    $this->stdout('DONE', Console::FG_GREEN);
  }

  /**
   * Выгрузка статистики по ТБ в csv.
   * @param null $date
   */
  public function actionTbPrepare($date = null)
  {
    $date = $date ?: Yii::$app->formatter->asDate('-1 day', 'php:Y-m-d');

    $this->stdout('TB STATISTIC BY DATE = ' . $date, Console::FG_GREEN);

    (new ResellerStatisticExporter([
      'dateFrom' => $date,
      'dateTo' => $date,
    ]))->exportTb();

    $this->stdout('DONE', Console::FG_GREEN);
  }

  /**
   * @inheritdoc
   */
  public function stdout($string)
  {
    $args = func_get_args();
    array_shift($args);
    $this->log(date('H:i:s') . ': ' . $string . PHP_EOL, $args);
  }
}