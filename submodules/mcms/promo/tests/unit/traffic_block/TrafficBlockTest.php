<?php

namespace tests\unit\traffic_block;

use mcms\common\codeception\TestCase;
use mcms\promo\components\AvailableOperators;
use mcms\promo\components\TrafficBlockChecker;
use Yii;

class TrafficBlockTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users',
      'promo.operators',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();

    $this->executeDb('SET FOREIGN_KEY_CHECKS = 0');
    $this->executeDb(file_get_contents(__DIR__ . '/../../_data/traffic_block/data.sql'));
    $this->executeDb('SET FOREIGN_KEY_CHECKS = 1');
    Yii::$app->cache->flush(); // на случай если закэшился user_promo_settings
  }

  public function testChecker()
  {
    // 101 юзер с вкл блэклистом
    $this->assertTrue((new TrafficBlockChecker(101, 1))->isTrafficBlocked(), 'user=101 с вкл блэклистом, траф на опер=1 должен быть заблокирован');
    $this->assertFalse((new TrafficBlockChecker(101, 2))->isTrafficBlocked(), 'user=101 с вкл блэклистом, траф на опер=2 должен быть включен');
    $this->assertFalse((new TrafficBlockChecker(101, 3))->isTrafficBlocked(), 'user=101 с вкл блэклистом, траф на опер=3 должен быть включен');

    // 103 юзер без строки user_promo_settings значит по-умолчанию is_blacklist включен
    $this->assertTrue((new TrafficBlockChecker(103, 1))->isTrafficBlocked(), 'user=103 с вкл блэклистом, траф на опер=1 должен быть заблокирован');
    $this->assertFalse((new TrafficBlockChecker(103, 2))->isTrafficBlocked(), 'user=103 с вкл блэклистом, траф на опер=2 должен быть включен');
    $this->assertFalse((new TrafficBlockChecker(103, 3))->isTrafficBlocked(), 'user=103 с вкл блэклистом, траф на опер=3 должен быть включен');

    // 102 юзер с выключенным блэклистом
    $this->assertTrue((new TrafficBlockChecker(102, 1))->isTrafficBlocked(), 'user=102 с выкл блэклистом, траф на опер=1 должен быть заблокирован');
    $this->assertFalse((new TrafficBlockChecker(102, 2))->isTrafficBlocked(), 'user=102 с выкл блэклистом, траф на опер=2 должен быть включен');
    $this->assertTrue((new TrafficBlockChecker(102, 3))->isTrafficBlocked(), 'user=102 с выкл блэклистом, траф на опер=3 должен быть заблокирован');
  }

  public function testAvailableOperators()
  {
    // Операторы, которые не в блек-листе партнера 101 (2, 3, 4)
    $notBlackListOperators = AvailableOperators::getInstance(101)->getIds();
    // Операторы, которые в вайт-листе партнера 102 (2)
    $whiteListOperators = AvailableOperators::getInstance(102)->getIds();
    // Операторы, которые не в блек-листе партнера 103 (2, 3, 4). Записи в UserPromoSettings нет, по умолчанию возвращает тру
    $notBlackListOperatorsWithoutUserPromoSettings = AvailableOperators::getInstance(103)->getIds();

    $this->assertEquals([2, 3, 4], array_values($notBlackListOperators), 'Ошибка в операторах, которые не в блек-листе партнера 101');
    $this->assertEquals([2], array_values($whiteListOperators), 'Ошибка в операторах, которые в вайт-листе партнера 102');
    $this->assertEquals([2, 3, 4], array_values($notBlackListOperatorsWithoutUserPromoSettings), 'Ошибка в операторах, которые не в блек-листе партнера 103');

  }
}
