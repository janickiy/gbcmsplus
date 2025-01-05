<?php
// TODO Удалить
namespace mcms\statistic\commands;

use mcms\promo\models\Country;
use Yii;
use yii\console\Controller;
use yii\db\Connection;
use yii\db\Expression;
use yii\helpers\Console;

// TODO Если statCount < batchSize, данные никогда не создадутся
// TODO Если statCount всегда кратен batchSize, потому что последняя порция данных не сохранится
// TODO UQ ключ заполняется рандомно и может дублироваться, просто на МГМП из-за reseller_id это может быть не заметно
// TODO В таблице пустые скобки

/**
 * пример
 * php yii statistic/reseller-profit-demo-data
 * или
 * php yii statistic/reseller-profit-demo-data --statCount=1000000 --statDays=120
 *
 * Class DemoDataController
 * @package common\commands
 */
class ResellerProfitDemoDataController extends Controller
{
  /** @var int */
  public $statCount = 100000;

  /** @var int */
  public $statDays = 180;


  /** @var bool  */
  protected $truncate = false;

  /** @var Connection */
  protected $db;

  /**
   * @inheritdoc
   */
  public function options($actionID)
  {
    return array_merge(
      parent::options($actionID),
      ['statCount', 'statDays']
    );
  }

  /**
   * @inheritdoc
   */
  public function beforeAction($action)
  {
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return null;
    }

    $this->truncate = $this->confirm('Сделать Truncate таблиц?');
    $this->db = Yii::$app->db;

    return parent::beforeAction($action);
  }


  public function actionIndex()
  {
    $this->stdout("Стартуем генератор демо-данных");

    $this->createProfits();
  }

  /**
   * @param string $message
   * @param bool $breakAfter
   * @param bool $breakBefore
   * @return bool|int|void
   */
  public function stdout($message, $breakAfter = true, $breakBefore = false)
  {
    parent::stdout(($breakBefore ? PHP_EOL : '') . $message . ($breakAfter ? PHP_EOL : ''), Console::FG_GREEN);
  }

  /**
   * @param $tableName
   */
  protected function truncateTable($tableName)
  {
    if (!$this->truncate) return;

    // На всякий случай тут тоже проверка, как в beforeAction
    if (defined('YII_ENV') && YII_ENV === 'prod') {
      $this->stdout('Запрещен запуск на продакшене' . "!\n");
      return;
    }

    $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
    $this->db->createCommand("TRUNCATE TABLE $tableName")->execute();
    $this->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    $this->stdout("$tableName TRUNCATED");
  }

  protected function createProfits()
  {
    $this->truncateTable('reseller_profits');

    $countries = Country::find()->all();
    $investorIds = [
      'rub' => 5,
      'usd' => 6,
      'eur' => 7
    ];
    $currencies = ['rub', 'usd', 'eur'];
    $minProfit = 10;
    $maxProfit = 100000;
    $maxBatchSize = 100;
    $batch = [];
    $countriesHolds = [];


    for ($i = 0; $i <= $this->statCount; $i++) {
      $country = $countries[array_rand($countries)];


      $profitRub = mt_rand($minProfit * 10, $maxProfit * 10) / 10;
      $profitUsd = mt_rand($minProfit * 10, $maxProfit * 10) / 10;
      $profitEur = mt_rand($minProfit * 10, $maxProfit * 10) / 10;
      $time = rand(time() - 3600 * 24 * $this->statDays, time());

      $countriesHolds[$country->id] = isset($countriesHolds[$country->id]) ? $countriesHolds[$country->id] : rand(1, 30);

      $batch[] = [
        $country->id, // 'country_id'
        date('Y-m-d', $time), // 'date'
        new Expression('date - INTERVAL WEEKDAY(date) DAY'), // 'week_start'
        new Expression('DATE_FORMAT(date, \'%Y-%m-01\')'), // 'month_start'
        $profitRub, // 'profit_rub'
        $profitUsd, // 'profit_usd'
        $profitEur, // 'profit_eur'
        date('Y-m-d', $time + $countriesHolds[$country->id] * 24 * 60 * 60), // 'unhold_date'
        new Expression('unhold_date - INTERVAL WEEKDAY(unhold_date) DAY'), // 'unhold_week_start'
        new Expression("DATE_FORMAT(unhold_date, '%Y-%m-01')"), // 'unhold_month_start'
        $time, // 'created_at'
        $time, // 'updated_at'
      ];

      if (count($batch) === $maxBatchSize) {
        $this->db->createCommand()->batchInsert('reseller_profits', [
          'country_id',
          'date',
          'week_start',
          'month_start',
          'profit_rub',
          'profit_usd',
          'profit_eur',
          'unhold_date',
          'unhold_week_start',
          'unhold_month_start',
          'created_at',
          'updated_at',
        ], $batch)->execute();
        $batch = [];
        $this->stdout('+', false);
      }
    }
    $this->stdout("{$this->statCount} Reseller Profits created OK", true, true);
  }
}
