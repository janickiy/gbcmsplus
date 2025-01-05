<?php

namespace mcms\payments\tests\unit;

use DateTime;
use mcms\common\codeception\TestCase;
use mcms\payments\components\UserBalance;
use mcms\payments\models\CurrencyLog;
use mcms\payments\models\UserPayment;
use mcms\payments\models\wallet\Wallet;
use mcms\payments\tests\traits\PaymentsTrait;
use mcms\statistic\components\cron\CronParams;
use mcms\statistic\components\cron\handlers\BalanceByUserAndDate;
use mcms\statistic\components\cron\handlers\BalanceByUserDateCountry;
use mcms\statistic\components\cron\handlers\Statistic;
use mcms\statistic\components\cron\handlers\SubscriptionsByDate;
use mcms\statistic\components\cron\handlers\SubscriptionsByHours;
use Yii;
use yii\db\Query;

/**
 * Тест для проверки правильности расчета баланса при сменах валюты партнера
 */
class UserBallanceTest extends TestCase
{
  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users', // 101-104
      'promo.sources', // 1-4
      'promo.landings', // 1-4
      'promo.operators', // 1-4
      'promo.landing_operators',
    ]);
  }

  public function setUp()
  {
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();

    Yii::$app->db->createCommand()->truncateTable('onetime_subscriptions')->execute();
    Yii::$app->db->createCommand()->truncateTable('sold_subscriptions')->execute();
    Yii::$app->db->createCommand()->truncateTable('user_payment_settings')->execute();
    Yii::$app->db->createCommand()->truncateTable('subscription_rebills')->execute();
    Yii::$app->db->createCommand()->truncateTable('statistic')->execute();
    Yii::$app->db->createCommand()->truncateTable('subscription_offs')->execute();
    Yii::$app->db->createCommand()->truncateTable('user_balances_grouped_by_day')->execute();
    Yii::$app->db->createCommand()->truncateTable('currency_log')->execute();
    Yii::$app->db->createCommand()->truncateTable('partner_country_unhold')->execute();
    Yii::$app->db->createCommand()->truncateTable('user_balance_invoices')->execute();

    Yii::$app->db->createCommand("INSERT INTO `user_payment_settings` (`user_id`, `referral_percent`, `early_payment_percent_old`, `currency`) VALUES (101, 0, 0, 'rub');")->execute();

    // ребилы
    Yii::$app->db->createCommand("INSERT INTO `subscription_rebills`
    (`hit_id`, `trans_id`, `landing_id`, `platform_id`, `landing_pay_type_id`, `source_id`, `hour`, `date`, `time`, `default_profit`, `default_profit_currency`, `currency_id`, `real_profit_rub`, `real_profit_usd`, `real_profit_eur`, `reseller_profit_rub`, `reseller_profit_usd`, `reseller_profit_eur`, `profit_rub`, `profit_usd`, `profit_eur`, `operator_id`)
      VALUES
      -- Проданные ребиллы
    (1, 1, 1, 1, 1, 3, 4, '2017-10-01', 1506819600, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3, 1),
    (2, 2, 1, 1, 1, 3, 5, '2017-10-01', 1506823200, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 250, 3.7, 3, 1),
    (3, 3, 1, 1, 1, 3, 6, '2017-10-01', 1506826800, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 260, 3.8, 3, 1),
    (4, 4, 1, 1, 1, 3, 7, '2017-10-01', 1506830400, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 270, 3.9, 3, 1),
    (5, 5, 1, 1, 1, 3, 3, '2017-09-01', 1504224000, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3, 1),
      -- Непроданные ребиллы
    (6, 12, 1, 1, 1, 3, 4, '2017-10-01', 1506819600, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3, 1),
    (7, 22, 1, 1, 1, 3, 5, '2017-10-01', 1506823200, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 250, 3.7, 3, 1),
    (8, 32, 1, 1, 1, 3, 6, '2017-10-01', 1506826800, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 260, 3.8, 3, 1),
    (9, 42, 1, 1, 1, 3, 7, '2017-10-01', 1506830400, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 270, 3.9, 3, 1),
    (10, 52, 1, 1, 1, 3, 3, '2017-09-01', 1504224000, 80, 1, 1, 80, 1.2, 1, 160, 2.4, 2, 240, 3.6, 3, 1);
    ")->execute();

    // Вантайм
    Yii::$app->db->createCommand("INSERT INTO onetime_subscriptions
    (`hit_id`, `trans_id`, `time`, `date`, `hour`, `default_profit`, `default_profit_currency`, `currency_id`, `profit_rub`, `profit_usd`, `profit_eur`, `phone`, `source_id`, `operator_id`, `landing_id`, `user_id`, `country_id`)
      VALUES
    (1, 1, 1506819600, '2017-10-01', 4, 241, 1, 1, 241, 4.6, 3, '111', 3, 1, 1, 101, 2),
    (2, 2, 1506823200, '2017-10-01', 5, 251, 1, 1, 251, 4.7, 3, '222', 3, 1, 1, 101, 1),
    (3, 3, 1506826800, '2017-10-01', 6, 261, 1, 1, 261, 4.8, 3, '333', 3, 1, 1, 101, 1),
    (4, 4, 1506830400, '2017-10-01', 7, 271, 1, 1, 271, 4.9, 3, '444', 3, 1, 1, 101, 1),
    (5, 5, 1504224000, '2017-09-01', 7, 271, 1, 1, 271, 4.9, 3, '555', 3, 1, 1, 101, 2);
    ")->execute();

    // Выкуп
    Yii::$app->db->createCommand("INSERT INTO sold_subscriptions
    (`hit_id`, `time`, `date`, `hour`, `currency_id`, `profit_rub`, `profit_usd`, `profit_eur`, `source_id`, `operator_id`, `landing_id`, `user_id`, `real_price_rub`, `real_price_usd`, `real_price_eur`, `reseller_price_rub`, `reseller_price_usd`, `reseller_price_eur`, `price_rub`, `price_usd`, `price_eur`, `country_id`)
      VALUES
    (1, 1506819600, '2017-10-01', 4, 1, 243, 6.6, 3, 3, 1, 1, 101, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
    (2, 1506819600, '2017-10-01', 5, 1, 253, 6.7, 3, 3, 1, 1, 101, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2),
    (3, 1506819600, '2017-10-01', 6, 1, 263, 6.8, 3, 3, 1, 1, 101, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2),
    (4, 1506819600, '2017-10-01', 7, 1, 273, 6.9, 3, 3, 1, 1, 101, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
    (5, 1504224000, '2017-09-01', 7, 1, 273, 6.9, 3, 3, 1, 1, 101, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);
      ")->execute();

    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();

    (new Statistic($this->getParams()))->run();
    (new Statistic($this->getParams()))->run();
    parent::setUp();
  }

  // Проверка правильности расчета баланса, если партнер менял валюту
  public function testBalanceWithCurrencyChange()
  {
    // первые 2 ребила должен получить в рубле, вторые в долларе
    Yii::$app->db->createCommand()->insert('currency_log', ['user_id' => 101, 'currency' => 'rub', 'created_at' => 0])->execute();
    Yii::$app->db->createCommand()->insert('currency_log', ['user_id' => 101, 'currency' => 'usd', 'created_at' => 1506823200])->execute(); // по мск в 2017-10-01 5ч
    (new BalanceByUserAndDate($this->getParams()))->run();
    (new BalanceByUserDateCountry($this->getParams()))->run();

    $balances = $this->getBalances('2017-09-01');
    $this->assertEquals(240, $balances['rub']['rebill'], 'Баланс по ребилам в рублях с изменениями валют не сошелся');
    $this->assertEquals(0, $balances['usd']['rebill'], 'Баланс по ребилам в долларах с изменениями валют не сошелся');

    $this->assertEquals(271, $balances['rub']['onetime'], 'Баланс по вантаймам в рублях с изменениями валют не сошелся');
    $this->assertEquals(0, $balances['usd']['onetime'], 'Баланс по вантаймам в долларах с изменениями валют не сошелся');

    $this->assertEquals(273, $balances['rub']['sold'], 'Баланс по солдам в рублях с изменениями валют не сошелся');
    $this->assertEquals(0, $balances['usd']['sold'], 'Баланс по солдам в долларах с изменениями валют не сошелся');

    $balances = $this->getBalances('2017-10-01');
    $this->assertEquals(490, $balances['rub']['rebill'], 'Баланс по ребилам в рублях с изменениями валют не сошелся');
    $this->assertEquals(7.70, $balances['usd']['rebill'], 'Баланс по ребилам в долларах с изменениями валют не сошелся');

    $this->assertEquals(492, $balances['rub']['onetime'], 'Баланс по вантаймам в рублях с изменениями валют не сошелся');
    $this->assertEquals(9.70, $balances['usd']['onetime'], 'Баланс по вантаймам в долларах с изменениями валют не сошелся');

    $this->assertEquals(496, $balances['rub']['sold'], 'Баланс по солдам в рублях с изменениями валют не сошелся');
    $this->assertEquals(13.70, $balances['usd']['sold'], 'Баланс по солдам в долларах с изменениями валют не сошелся');
  }

  // Проверка правильности расчета баланса, если партнер не менял валюту
  public function testBalanceWithoutCurrencyChange()
  {
    (new BalanceByUserAndDate($this->getParams()))->run();
    (new BalanceByUserDateCountry($this->getParams()))->run();

    $balances = $this->getBalances('2017-10-01');

    // Проверяем профиты

    $this->assertEquals(1020, $balances['rub']['rebill'], 'Баланс по ребилам без изменения валют не сошелся');
    $this->assertEquals(1024, $balances['rub']['onetime'], 'Баланс по вантаймам без изменения валют не сошелся');
    $this->assertEquals(1032, $balances['rub']['sold'], 'Баланс по солдам без изменения валют не сошелся');
  }

  private function getParams()
  {
    return [
      'params' => new CronParams(['fromTime' => 0])
    ];
  }

  private function getBalances($date)
  {
    $balanceRubRebill = (new Query())->select('SUM(profit_rub)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'rub',
        'type' => 0,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();
    $balanceUsdRebill = (new Query())->select('SUM(profit_usd)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'usd',
        'type' => 0,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();

    $balanceRubOnetime = (new Query())->select('SUM(profit_rub)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'rub',
        'type' => 1,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();
    $balanceUsdOnetime = (new Query())->select('SUM(profit_usd)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'usd',
        'type' => 1,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();

    $balanceRubSold = (new Query())->select('SUM(profit_rub)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'rub',
        'type' => 2,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();
    $balanceUsdSold = (new Query())->select('SUM(profit_usd)')->from('user_balances_grouped_by_day')
      ->andWhere([
        'user_currency' => 'usd',
        'type' => 2,
        'date' => $date,
        'user_id' => 101,
      ])
      ->scalar();

    return [
      'rub' => [
        'rebill' => $balanceRubRebill,
        'onetime' => $balanceRubOnetime,
        'sold' => $balanceRubSold
      ],
      'usd' => [
        'rebill' => $balanceUsdRebill,
        'onetime' => $balanceUsdOnetime,
        'sold' => $balanceUsdSold
      ],
    ];
  }

  // Проверка балансов в холде/расхолде без смены валюты партнера
  public function testUnholdBalanceWithoutCurrencyChange()
  {
    Yii::$app->cache->flush();
    (new BalanceByUserAndDate($this->getParams()))->run();
    (new BalanceByUserDateCountry($this->getParams()))->run();

    // Правила расхолда
    Yii::$app->db->createCommand("INSERT INTO `partner_country_unhold` ( `user_id`, `country_id`, `last_unhold_date`) VALUES (101, 1, '2017-10-2');")->execute();
    Yii::$app->db->createCommand("INSERT INTO `partner_country_unhold` ( `user_id`, `country_id`, `last_unhold_date`) VALUES (101, 2, '2017-09-30');")->execute();

    // Инвойс в холде
    Yii::$app->db->createCommand("INSERT INTO `user_balance_invoices` (`user_id`, `country_id`, `currency`, `amount`, `created_at`, `date`, `created_by`, `type`) VALUES (101, 2, 'rub', 155, UNIX_TIMESTAMP(), '2017-10-01', 1, 1);")->execute();
    // Инвойс не в холде
    Yii::$app->db->createCommand("INSERT INTO `user_balance_invoices` (`user_id`, `country_id`, `currency`, `amount`, `created_at`, `date`, `created_by`, `type`) VALUES (101, 1, 'rub', 133, UNIX_TIMESTAMP(), '2017-10-01', 1, 1);")->execute();


    $userBalance = new UserBalance(['userId' => 101, 'currency' => 'rub']);

    $this->assertEquals(4148, $userBalance->getBalance(), 'Общий балланс не сошелся');
    $this->assertEquals(912, $userBalance->getHold(), 'Балланс холдов не сошелся');
    $this->assertEquals(3236, $userBalance->getMain(), 'Расхолженый балланс не сошелся');
  }

  // Проверка балансов в холде/расхолде со сменой валюты партнера
  public function testUnholdBalanceWithCurrencyChange()
  {
    // до 2017-10-01 5ч по МСК был рубль, потом доллар
    Yii::$app->db->createCommand()->insert('currency_log', ['user_id' => 101, 'currency' => 'rub', 'created_at' => 0])->execute();
    Yii::$app->db->createCommand()->insert('currency_log', ['user_id' => 101, 'currency' => 'usd', 'created_at' => 1506823200])->execute();

    Yii::$app->cache->flush();
    (new BalanceByUserAndDate($this->getParams()))->run();
    (new BalanceByUserDateCountry($this->getParams()))->run();

    // Правила расхолда
    Yii::$app->db->createCommand("INSERT INTO `partner_country_unhold` ( `user_id`, `country_id`, `last_unhold_date`) VALUES (101, 1, '2017-10-2');")->execute();
    Yii::$app->db->createCommand("INSERT INTO `partner_country_unhold` ( `user_id`, `country_id`, `last_unhold_date`) VALUES (101, 2, '2017-09-30');")->execute();

    $userBalanceRub = new UserBalance(['userId' => 101, 'currency' => 'rub']);
    $userBalanceUsd = new UserBalance(['userId' => 101, 'currency' => 'usd']);

    $this->assertEquals(494, $userBalanceRub->getHold(), 'Балланс холдов в рублях со сменой валюты не сошелся');
    $this->assertEquals(6.8, $userBalanceUsd->getHold(), 'Балланс холдов в долларах со сменой валюты не сошелся');

    $this->assertEquals(1768, $userBalanceRub->getMain(), 'Расхолженый балланс в рублях со сменой валюты не сошелся');
    $this->assertEquals(24.3, $userBalanceUsd->getMain(), 'Расхолженый балланс в долларах со сменой валюты не сошелся');
  }
}