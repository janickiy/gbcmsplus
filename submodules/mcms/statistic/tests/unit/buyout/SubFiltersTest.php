<?php

namespace mcms\statistic\tests\unit\buyout;

use mcms\common\codeception\TestCase;
use mcms\statistic\commands\BuyoutController;
use mcms\statistic\Module;
use Yii;

/**
 * Class SubFiltersTest
 * @package mcms\statistic\tests\unit\buyout
 */
class SubFiltersTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landing_operators',
      'promo.sources',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    $this->setModuleSetting(Module::SETTINGS_BUYOUT_AFTER_1ST_REBILL_ONLY, false);
  }

  public function testBuyoutMinutes()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/buyout_minutes.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $controller = new BuyoutController('buyout-controller', Yii::$app->getModule('statistic'));

    $controller->setMinutes(1);

    $query = $controller->getSubscriptionQuery();

    $result = $query->all();

    $shouldBeSell = [1001, 1003];

    $this->assertEquals(count($shouldBeSell), count($result));

    foreach ($result AS $sub) {
      $this->assertTrue(in_array($sub['hit_id'], $shouldBeSell));
    }
  }

  // Проверяем, что верно отрабатывает приоритет условий
  public function testBuyoutMinutesOrder()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/buyout_minutes_order.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $controller = new BuyoutController('buyout-controller', Yii::$app->getModule('statistic'));

    $controller->setMinutes(1);

    $query = $controller->getSubscriptionQuery();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (1)');
    }

    // удаляю первое правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 1')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (2)');
    }

    // задаю второму правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 2')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (3)');
    }

    // удаляю 2 правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 2')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (3)');
    }

    // задаю 3 правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 3')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (4)');
    }

    // удаляю 3 правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 3')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (4)');
    }

    // задаю 4 правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 4')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (5)');
    }

    // удаляю 4 правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 4')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (5)');
    }

    // задаю 5 правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 5')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (6)');
    }

    // удаляю 5 правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 5')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (6)');
    }

    // задаю 6 правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 6')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (7)');
    }

    // удаляю 6 правило, проверяю, что обе подписки теперь выкупаются
    Yii::$app->db->createCommand('DELETE FROM `buyout_conditions` WHERE id = 6')->execute();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertTrue(in_array($sub['hit_id'], $shouldBeSell), 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (7)');
    }

    // задаю 7 правилу 20 мин. Теперь снова выкупится только одна подписка
    Yii::$app->db->createCommand('UPDATE `buyout_conditions` SET buyout_minutes = 20 WHERE id = 7')->execute();

    $result = $query->all();

    $shouldBeSell = [1002];
    self::assertEquals(count($shouldBeSell), count($result), 'Неверное колличество выкупленных подписок');

    foreach ($result AS $sub) {
      self::assertEquals(1002, $sub['hit_id'], 'Выкупилась лишняя подписка. Неверный порядок условий выкупа (8)');
    }

  }

  public function test1stRebill()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/1st_rebill.sql');
    Yii::$app->db->createCommand($sql)->execute();

    $controller = new BuyoutController('buyout-controller', Yii::$app->getModule('statistic'));

    $controller->setMinutes(1);

    $query = $controller->getSubscriptionQuery();

    $result = $query->all();

    $shouldBeSell = [1001, 1002];

    $this->assertEquals(count($shouldBeSell), count($result));

    foreach ($result AS $sub) {
      $this->assertTrue(in_array($sub['hit_id'], $shouldBeSell));
    }
  }

  public function testUniquePhone()
  {
    $sql = file_get_contents(__DIR__ . '/../../_data/buyout/unique_phone.sql');
    Yii::$app->db->createCommand($sql)->execute();

    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex();
    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex(); // не удалять
    (new BuyoutController('buyout', Yii::$app->getModule('statistic')))->actionIndex(); // не удалять

    $shouldBeSell = [
      1000, // телефон 111111
      1002, 12332, // телефон 111122. Пдп 12332 типа уже изначально выкуплена, а 1002 с другого оператора
      1005, 1006 // телефон 55555555. Пдп 1005 типа уже изначально выкуплена, а 1006 выкупилась, т.к. 1005 уже старая
    ];
    sort($shouldBeSell);
    $result = Yii::$app->db->createCommand('SELECT hit_id FROM sold_subscriptions')->queryColumn();
    $this->assertEquals($shouldBeSell, $result);
  }

}