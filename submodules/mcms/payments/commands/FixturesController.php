<?php

namespace mcms\payments\commands;

use mcms\payments\models\UserBalancesGroupedByDay as GroupedByDay;
use mcms\user\models\User;
use yii\console\Controller;
use yii\helpers\Console;
use Yii;

/**
 * Тестовые данные выплат
 * @package mcms\payments\commands
 */
class FixturesController extends Controller
{
  const EUR = 0.014;
  const RUB = 1;
  const USD = 0.015;


  /**
   * Добавить доход пользователя
   *
   * @param string $username
   * @param string $fromDate
   * @param string $toDate
   * @return int
   */
  public function actionAddProfit($username, $fromDate, $toDate = null)
  {
    if (!$user = User::findOne(['username' => $username])) {
      $this->stdout("User not found\n", Console::FG_RED);
      return Controller::EXIT_CODE_NORMAL;
    }

    $fromDate = strtotime($fromDate);
    $toDate = $toDate ? strtotime($toDate) : time();

    $rows = [];
    while($fromDate <= $toDate) {
      $profit = rand(10, 20);
      foreach ([GroupedByDay::TYPE_REBILL, GroupedByDay::TYPE_REFERRAL, GroupedByDay::TYPE_BUYOUT] as $type) {
        $rows[] = [
          'date' => Yii::$app->formatter->asDate($fromDate, 'php:Y-m-d'),
          'user_id' => $user->id,
          'type' => $type,
          'profit_rub' => $profit * self::RUB,
          'profit_eur' => $profit * self::EUR,
          'profit_usd' => $profit * self::USD,
          'country_id' => 1,
          'user_currency' => '',
        ];
      }

      $fromDate += 86400;
    }

    if (!$rows) {
      $this->stdout("No write data\n", Console::FG_YELLOW);
      return Controller::EXIT_CODE_NORMAL;
    }

    if (!$this->save(GroupedByDay::tableName(), (new GroupedByDay())->attributes(), $rows)) {
      $this->stdout("Error write data\n", Console::FG_YELLOW);
    }

    $this->stdout(sprintf("%d rows added\n", count($rows)), Console::FG_YELLOW);
    return Controller::EXIT_CODE_NORMAL;
  }

  private function save($table, $attributes, $rows)
  {
    return Yii::$app->db->createCommand()
      ->batchInsert($table, $attributes, $rows)
      ->execute();
  }
}