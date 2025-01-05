<?php

namespace mcms\holds\tests\unit\partner_country_unhold;

use mcms\common\codeception\TestCase;
use mcms\holds\components\PartnerCountryUnhold;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * проверяется работа класса @see PartnerCountryUnhold
 */
class PartnerCountryUnholdTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users',
      'promo.countries',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_program_rules')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hold_programs')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_payment_settings')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_balances_grouped_by_day')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE user_balance_invoices')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE rule_unhold_plan')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE partner_country_unhold')->execute();

    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
  }

  /**
   * по привязанной к руле стране все норм посчиталось
   */
  public function testCountryRule()
  {
    // создаем программу, привязываем её партнеру и одну рулю для страны№1 и на всякий случай глобальную рулю для всех стран
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testCountryRule.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals('2018-04-10', $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');
  }

  /**
   * по глобальной руле для стран всё норм посчиталось
   */
  public function testGlobalRule()
  {
    // создаем программу, привязываем её партнеру и одну глобальную рулю для всех стран
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testGlobalRule.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals('2018-04-10', $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');

  }

  /**
   * по дефолтной программе всё посчиталось корректно
   */
  public function testDefaultProgram()
  {
    // создаем дефолтную программу и не привязываем её партнеру и одну глобальную рулю для всех стран
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testDefaultProgram.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals('2018-04-10', $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');
  }

  /**
   * если партнер не лил на страну, то и расхолд для него не посчитаем вообще
   */
  public function testNoCountryProfits()
  {
    // создаем дефолтную программу и не привязываем её партнеру и одну глобальную рулю для всех стран
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testNoCountryProfits.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertFalse($lastUnhold, 'Нет расхолда, т.к. партнер не лил на эту страну');
  }

  /**
   * у программы нет нужной страны, значит расхолд сегодня
   * (ранее могло подставиться правило ALL из дефолтной программы)
   */
  public function testNoSuchCountryInProgram()
  {
    // создаем программу (не дефолт), привязываем её партнеру и одну рулю для страны№2
    // создаем дополнительно дефолтное правило и для него страну All
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21
    // после расчета хэндлера дата расхолда будет равна сегодня, т.к. нет правила для страны №2 в нужной программе

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testNoSuchCountryInProgram.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals(date('Y-m-d'), $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');
  }

  /**
   * если у партнера изменилась дата расхолда, то мы прописываем наибольшую из них.
   * То есть в меньшу сторону убавлять нельзя
   */
  public function testMaxDateNotReplaced()
  {
    // создаем программу, привязываем её партнеру и одну рулю для страны№1 и на всякий случай глобальную рулю для всех стран
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testMaxDateNotReplaced.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals('2018-05-01', $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');
  }

  /**
   * у партнера нет программы, значит расхолд сегодня
   */
  public function testUserHasNoProgram()
  {
    // создаем программу, привязываем её партнеру и одну рулю для страны№2
    // заинсертить график расхолда:
    // 2018-04-01-2018-04-10 (расхолд 2018-04-12)
    // 2018-04-11-2018-04-21 (расхолд будущая неделя)
    // после расчета хэндлера дата расхолда будет равна 2018-04-10, т.к. новая пачка (2018-04-11-2018-04-21) ещё не расхолдилась

    $sql = file_get_contents(__DIR__ . '/../../_data/partner_country_unhold/testUserHasNoProgram.sql');
    $this->getDbCommand($sql)->execute();

    (new PartnerCountryUnhold())->run();

    $lastUnhold = (new Query())
      ->select('last_unhold_date')
      ->from(PartnerCountryUnhold::tableName())
      ->andWhere(['user_id' => 101, 'country_id' => 1])
      ->scalar();

    $this->assertEquals(date('Y-m-d'), $lastUnhold, 'Неправильно расчитана дата последней пачки расхолда');
  }
}