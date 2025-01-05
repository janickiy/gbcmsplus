<?php
namespace mcms\statistic\tests\unit\cron_handlers;

use mcms\common\codeception\TestCase;
use mcms\statistic\components\cron\CronParams;
use mcms\statistic\components\cron\handlers\BalanceByUserAndDate;
use Yii;
use yii\db\Query;

/**
 * Class BalanceByUserAndDateTest
 * @package mcms\statistic\tests\unit\cron_handlers
 */
class BalanceByUserAndDateTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'payments.user_payment_settings',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('DELETE FROM user_balances_grouped_by_day')->execute();
  }

  /* TODO тут проверяется баланс партнера на примере продажи ТБ. Надо сюда другую проверку сделать
  public function testHandler()
  {
    $userBalance = new BalanceByUserAndDate([
      'params' => new CronParams([
        'fromTime' => strtotime('2016-06-13'),
      ]),
    ]);
    // группируем стату
    $userBalance->run();

    // данные для сравнение сгруппированной статы
    $data = $this->getCompareDataUserBalancesGroupedByDay();

    $query = (new Query())
      ->from('user_balances_grouped_by_day')
      ->where([
        'type' => 4,
      ]);

    foreach ($data as $dataItem) {
      $q = clone $query;

      $exists = $q->andWhere([
        'profit_rub' => $dataItem['profit_rub'],
        'profit_eur' => $dataItem['profit_eur'],
        'profit_usd' => $dataItem['profit_usd'],
        'date' => $dataItem['date'],
        'is_hold' => $dataItem['is_hold'],
      ])->exists();

      $this->assertTrue($exists, "Record does not exists " . print_r($dataItem, 1));
    }
  }*/

  /**
   * эталонный массив сгруппированной статы
   * @return array
   */
  private function getCompareDataUserBalancesGroupedByDay()
  {
    return [
      [
        'date' => '2016-06-13',
        'user_id' => '4',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '51.82',
        'profit_eur' => '0.00',
        'profit_usd' => '0.00',
      ],
      [
        'date' => '2016-06-13',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '751.01',
        'profit_eur' => '0.14',
        'profit_usd' => '0.18',
      ],
      [
        'date' => '2016-06-13',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '367.39',
        'profit_eur' => '0.07',
        'profit_usd' => '0.09',
      ],
      [
        'date' => '2016-06-13',
        'user_id' => '6',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '342.50',
        'profit_eur' => '0.07',
        'profit_usd' => '0.08',
      ],
      [
        'date' => '2016-06-13',
        'user_id' => '6',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '188.21',
        'profit_eur' => '0.04',
        'profit_usd' => '0.04',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '4',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '107.55',
        'profit_eur' => '0.00',
        'profit_usd' => '0.00',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '4',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '14.08',
        'profit_eur' => '0.00',
        'profit_usd' => '0.00',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '1865.98',
        'profit_eur' => '0.38',
        'profit_usd' => '0.43',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '321.07',
        'profit_eur' => '0.06',
        'profit_usd' => '0.08',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '6',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '666.66',
        'profit_eur' => '0.13',
        'profit_usd' => '0.16',
      ],
      [
        'date' => '2016-06-14',
        'user_id' => '6',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '164.88',
        'profit_eur' => '0.03',
        'profit_usd' => '0.04',
      ],
      [
        'date' => '2016-06-15',
        'user_id' => '4',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '44.34',
        'profit_eur' => '0.00',
        'profit_usd' => '0.00',
      ],
      [
        'date' => '2016-06-15',
        'user_id' => '4',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '14.65',
        'profit_eur' => '0.00',
        'profit_usd' => '0.00',
      ],
      [
        'date' => '2016-06-15',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '1066.68',
        'profit_eur' => '0.21',
        'profit_usd' => '0.26',
      ],
      [
        'date' => '2016-06-15',
        'user_id' => '5',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '690.77',
        'profit_eur' => '0.15',
        'profit_usd' => '0.16',
      ],
      [
        'date' => '2016-06-15',
        'user_id' => '6',
        'type' => '4',
        'is_hold' => '0',
        'profit_rub' => '931.69',
        'profit_eur' => '0.18',
        'profit_usd' => '0.22',
      ],
      [
        'date' => '2016-07-13',
        'user_id' => '101',
        'type' => '4',
        'is_hold' => '1',
        'profit_rub' => '117.53',
        'profit_eur' => '0.02',
        'profit_usd' => '0.03',
      ],
    ];
  }

}