<?php
namespace mcms\statistic\tests\unit\postbacks;

use mcms\common\codeception\TestCase;
use mcms\statistic\components\postbacks\DbFetcher;
use mcms\statistic\components\postbacks\Sender;
use yii\db\Query;

/**
 *
 */
class ParamsTest extends TestCase
{
  public function testRebills()
  {
    $this->loadFixture('rebills');
    $this->runSender(Sender::TYPE_REBILL);
    $pb = $this->getPostback();
    $this->assertEquals('http://some.ru/?stream_id=123&link_id=1&link_name=name&link_hash=testhash&action_time=1531409046&action_date=2018-07-12+18%3A24%3A06&type=rebill&subscription_id=1&operator_id=1&landing_id=2&description=&sum_rub=18&sum_usd=20&sum_eur=19&rebill_id=999&msisdn={msisdn}', $pb['url']);
  }

  public function testSubscriptions()
  {
    $this->loadFixture('subscriptions');
    $this->runSender(Sender::TYPE_SUBSCRIPTION);
    $pb = $this->getPostback();
    $this->assertEquals('http://some.ru/?stream_id=123&link_id=1&link_name=name&link_hash=testhash&action_time=1531236243&action_date=2018-07-10+18%3A24%3A03&type=on&subscription_id=1&operator_id=1&landing_id=2&description=&sum_rub=&sum_usd=&sum_eur=&rebill_id=&msisdn={msisdn}', $pb['url']);
  }

  public function testSubscriptionOffs()
  {
    $this->loadFixture('subscription_offs');
    $this->runSender(Sender::TYPE_SUBSCRIPTION_OFF);
    $pb = $this->getPostback();
    $this->assertEquals('http://some.ru/?stream_id=123&link_id=1&link_name=name&link_hash=testhash&action_time=1531236243&action_date=2018-07-10+18%3A24%3A03&type=off&subscription_id=1&operator_id=1&landing_id=2&description=&sum_rub=&sum_usd=&sum_eur=&rebill_id=&msisdn={msisdn}', $pb['url']);
  }

  public function testOnetimeSubscriptions()
  {
    $this->loadFixture('onetime_subscriptions');
    $this->runSender(Sender::TYPE_ONETIME_SUBSCRIPTION);
    $pb = $this->getPostback();
    $this->assertEquals('http://some.ru/?stream_id=123&link_id=1&link_name=name&link_hash=testhash&action_time=1531236243&action_date=2018-07-10+18%3A24%3A03&type=sell&subscription_id=1&operator_id=1&landing_id=2&description=&sum_rub=4&sum_usd=6&sum_eur=5&rebill_id=&msisdn={msisdn}', $pb['url']);
  }

  public function testSoldSubscriptions()
  {
    $this->loadFixture('sold_subscriptions');
    $this->runSender(Sender::TYPE_SUBSCRIPTION_SELL);
    $pb = $this->getPostback();
    $this->assertEquals('http://some.ru/?stream_id=123&link_id=1&link_name=name&link_hash=testhash&action_time=1531236243&action_date=2018-07-10+18%3A24%3A03&type=sell&subscription_id=1&operator_id=1&landing_id=2&description=&sum_rub=9&sum_usd=5&sum_eur=4&rebill_id=&msisdn={msisdn}', $pb['url']);
  }

  /**
   * @param $type
   */
  private function runSender($type)
  {
    $timeFrom = 1531236243 - 24 * 60 * 60;
    $maxAttempts = 1;
    $fetcher = new DbFetcher([
      'type' => $type,
      'isDuplicatePostback' => false,
      'timeFrom' => $timeFrom,
      'timeTo' => time(),
      'maxAttempts' => $maxAttempts,
    ]);

    (new Sender([
      'type' => $type,
      'isDummyExec' => true,
      'timeFrom' => $timeFrom,
      'maxAttempts' => $maxAttempts,
      'fetcher' => $fetcher
    ]))->run();
  }

  /**
   * @return array|bool
   */
  private function getPostback()
  {
    return (new Query())->from('postbacks')->one();
  }

  /**
   * @param $type
   * @throws \yii\db\Exception
   */
  private function loadFixture($type)
  {
    $this->executeDb('SET FOREIGN_KEY_CHECKS = 0');
    $this->executeDb(file_get_contents(__DIR__ . "/../../_data/postbacks/$type.sql"));
    $this->executeDb('SET FOREIGN_KEY_CHECKS = 1');
  }

}