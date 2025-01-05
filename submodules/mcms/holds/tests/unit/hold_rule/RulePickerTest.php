<?php
namespace mcms\holds\tests\unit\hold_rule;

use mcms\common\codeception\TestCase;
use mcms\holds\components\RulePicker;
use Yii;

/**
 * тестируем получение подходящего правила расхолда для партнера
 */
class RulePickerTest extends TestCase
{

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_program_rules')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_programs')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_payment_settings')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
  }

  public function testNoRule()
  {
    $rule = (new RulePicker([
      'userId' => 101,
      'countryId' => 1,
    ]))->getRule();

    $this->assertNull($rule);
  }

  public function testDefaultRule()
  {
    // Сделать программу дефолтной
    // Партнеру не привязывать ничего
    // В правилах сделаем одну глобальную (по всем странам), одну к стране 1, и одну к стране 2.
    // Проверяем что досталась та которая к стране 1.
    Yii::$app->db->createCommand('INSERT INTO `hold_programs` (id, name, description, is_default, created_at, updated_at) VALUES
      (1, \'test\', \'test\', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())')->execute();

    Yii::$app->db->createCommand('INSERT INTO `hold_program_rules` 
      (id, hold_program_id, country_id, unhold_range, unhold_range_type, min_hold_range, min_hold_range_type, at_day, at_day_type, created_at, updated_at) VALUES
      (1, 1, NULL, 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (2, 1, 2, 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (3, 1, 1, 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
      ')->execute();

    $rule = (new RulePicker([
      'userId' => 101,
      'countryId' => 1,
    ]))->getRule();
    $this->assertEquals(3, $rule->id);
    $this->assertEquals(1, $rule->country_id);
    $this->assertEquals(1, $rule->unhold_range);
    $this->assertEquals(1, $rule->unhold_range_type);
    $this->assertEquals(1, $rule->min_hold_range);
    $this->assertEquals(1, $rule->min_hold_range_type);
    $this->assertEquals(1, $rule->at_day);
    $this->assertEquals(1, $rule->at_day_type);
  }

  public function testPartnerRule()
  {
    // Присвоить партнеру правило 2
    // Сделать правило 1 дефолтным
    Yii::$app->db->createCommand('INSERT INTO `hold_programs` (id, name, description, is_default, created_at, updated_at) VALUES
      (1, \'test\', \'test\', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (2, \'test\', \'test\', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
      ')->execute();

    Yii::$app->db->createCommand('INSERT INTO `hold_program_rules` 
      (id, hold_program_id, country_id, unhold_range, unhold_range_type, min_hold_range, min_hold_range_type, at_day, at_day_type, created_at, updated_at) VALUES
      (1, 1, 1, 2, 3, 2, 3, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (2, 2, 1, 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
      ')->execute();

    Yii::$app->db->createCommand('INSERT INTO user_payment_settings (user_id, hold_program_id, referral_percent, early_payment_percent, early_payment_percent_old) VALUES
      (101, 2, 3, 3, 3)
      ')->execute();

    $rule = (new RulePicker([
      'userId' => 101,
      'countryId' => 1,
    ]))->getRule();

    $this->assertEquals(2, $rule->id);
    $this->assertEquals(1, $rule->country_id);
    $this->assertEquals(1, $rule->unhold_range);
    $this->assertEquals(1, $rule->unhold_range_type);
    $this->assertEquals(1, $rule->min_hold_range);
    $this->assertEquals(1, $rule->min_hold_range_type);
    $this->assertEquals(1, $rule->at_day);
    $this->assertEquals(1, $rule->at_day_type);
  }

  public function testNoCountryRule()
  {
    // создаем программу (не дефолт), привязываем её партнеру и одну рулю для страны№2
    // создаем дополнительно дефолтное правило и для него страну All
    // (ранее могло подставиться правило ALL из дефолтной программы)
    Yii::$app->db->createCommand('INSERT INTO `hold_programs` (id, name, description, is_default, created_at, updated_at) VALUES
      (1, \'test\', \'test\', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (2, \'test\', \'test\', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
      ')->execute();

    Yii::$app->db->createCommand('INSERT INTO `hold_program_rules` 
      (id, hold_program_id, country_id, unhold_range, unhold_range_type, min_hold_range, min_hold_range_type, at_day, at_day_type, created_at, updated_at) VALUES
      (1, 1, NULL, 2, 3, 2, 3, 1, 2, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
      (2, 2, 2, 1, 1, 1, 1, 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
      ')->execute();

    Yii::$app->db->createCommand('INSERT INTO user_payment_settings (user_id, hold_program_id, referral_percent, early_payment_percent, early_payment_percent_old) VALUES
      (101, 2, 3, 3, 3)
      ')->execute();

    $rule = (new RulePicker([
      'userId' => 101,
      'countryId' => 1,
    ]))->getRule();

    $this->assertNull($rule);
  }

}