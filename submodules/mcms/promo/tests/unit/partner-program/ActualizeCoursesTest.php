<?php

namespace mcms\promo\tests\unit\partnerProgram;

use mcms\common\codeception\TestCase;
use mcms\currency\components\PartnerCurrenciesProvider;
use mcms\promo\components\PersonalProfitsActualizeCourses;
use mcms\promo\models\PartnerProgramItem;
use mcms\promo\models\PersonalProfit;
use Yii;
use yii\db\Query;

/**
 * проверяем актуализатор поля фикс.цпа согласно новым курсам валюты
 */
class ActualizeCoursesTest extends TestCase
{
  public function _before()
  {
    $this->executeDb('set foreign_key_checks=0');
    $this->executeDb('TRUNCATE TABLE currencies');
    $this->executeDb('INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `to_rub`, `to_usd`, `to_eur`, `partner_percent_rub`, `partner_percent_usd`, `partner_percent_eur`, `created_at`, `updated_at`) VALUES (1, \'a:2:{s:2:"ru";s:10:"Рубли";s:2:"en";s:6:"Rubles";}\', \'rub\', \'Р\', 
1.000000000, 0.015970837, 0.013039028, 0.00, 0.00, 0.00, 1512045685, 1532519257);
INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `to_rub`, `to_usd`, `to_eur`, `partner_percent_rub`, `partner_percent_usd`, `partner_percent_eur`, `created_at`, `updated_at`) VALUES (2, \'a:2:{s:2:"ru";s:14:"Доллары";s:2:"en";s:7:"Dollars";}\', \'usd\', \'$\', 
56.650875000, 1.000000000, 0.792352216, 0, 0, 0, 1512045685, 1532519262);
INSERT INTO `currencies` (`id`, `name`, `code`, `symbol`, `to_rub`, `to_usd`, `to_eur`, `partner_percent_rub`, `partner_percent_usd`, `partner_percent_eur`, `created_at`, `updated_at`) VALUES (3, \'a:2:{s:2:"ru";s:8:"Евро";s:2:"en";s:4:"Euro";}\', \'eur\', \'€\', 
66.842380000, 1.120525000, 1.000000000, 0, 0, 0.00, 1512045685, 1532519261);
');
    $this->executeDb('set foreign_key_checks=1');

    Yii::$app->cache->flush(); // иначе откуда-то берет курсы валют из кэша
  }

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.countries',
      'promo.operators',
    ]);
  }

  public function testNoOperator()
  {
    $this->loadFixture('actualize-courses_no-operator');

    $this->runHandler();

    foreach ([PersonalProfit::tableName(), PartnerProgramItem::tableName()] as $tableName) {
      $row = $this->getRowFromDb($tableName);
      $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
      $this->assertEquals(888, $row['cpa_profit_usd'], "$tableName В usd не должна измениться сумма");
      $this->assertEquals(777, $row['cpa_profit_eur'], "$tableName В eur не должна измениться сумма");
    }
  }

  public function testPositiveActualize()
  {
    $this->loadFixture('actualize-courses_positive');

    $this->runHandler();

    foreach ([PersonalProfit::tableName(), PartnerProgramItem::tableName()] as $tableName) {
      $row = $this->getRowFromDb($tableName);
      $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
      $this->assertEquals(15.955, $row['cpa_profit_usd'], "$tableName В usd должна измениться сумма по новому курсу");
      $this->assertEquals(13.026, $row['cpa_profit_eur'], "$tableName В eur должна измениться сумма по новому курсу");
    }
  }

  public function testEmptyOneFixCpa()
  {
    $this->loadFixture('actualize-courses_empty_one');

    $this->runHandler();

    foreach ([PersonalProfit::tableName(), PartnerProgramItem::tableName()] as $tableName) {
      $row = $this->getRowFromDb($tableName);
      $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
      $this->assertEquals(15.955, $row['cpa_profit_usd'], "$tableName В usd должна измениться сумма по новому курсу");
      $this->assertNull($row['cpa_profit_eur'], "$tableName В eur не должна измениться сумма");
    }
  }

  public function testEmptyAllFixCpa()
  {
    $this->loadFixture('actualize-courses_empty_all');

    $this->runHandler();

    foreach ([PersonalProfit::tableName(), PartnerProgramItem::tableName()] as $tableName) {
      $row = $this->getRowFromDb($tableName);
      $this->assertNull($row['cpa_profit_rub'], "$tableName В rub должен остаться null");
      $this->assertNull($row['cpa_profit_usd'], "$tableName В usd должен остаться null");
      $this->assertNull($row['cpa_profit_eur'], "$tableName В eur должен остаться null");
    }
  }

  public function testCountryHasNoCurrency()
  {
    $this->loadFixture('actualize-courses_positive');
    $this->executeDb('UPDATE countries SET currency = NULL,local_currency = NULL');

    $this->runHandler();

    foreach ([PersonalProfit::tableName(), PartnerProgramItem::tableName()] as $tableName) {
      $row = $this->getRowFromDb($tableName);
      $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
      $this->assertEquals(888, $row['cpa_profit_usd'], "$tableName В usd не должна измениться сумма");
      $this->assertEquals(777, $row['cpa_profit_eur'], "$tableName В eur не должна измениться сумма");
    }
  }

  public function testOnlySelectedPartnerProgramPositive()
  {
    $this->loadFixture('actualize-courses_only_selected_program');

    $this->runHandler(1);

    $tableName = PartnerProgramItem::tableName();
    $row = $this->getRowFromDb($tableName);
    $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
    $this->assertEquals(15.955, $row['cpa_profit_usd'], "$tableName В usd должна измениться сумма по новому курсу");
    $this->assertEquals(13.026, $row['cpa_profit_eur'], "$tableName В eur должна измениться сумма по новому курсу");
  }

  public function testOnlySelectedPartnerProgramNegative()
  {
    $this->loadFixture('actualize-courses_only_selected_program');

    $this->runHandler(2);
    $tableName = PartnerProgramItem::tableName();

    $row = $this->getRowFromDb($tableName);
    $this->assertEquals(999, $row['cpa_profit_rub'], "$tableName В rub не должна измениться сумма");
    $this->assertEquals(888, $row['cpa_profit_usd'], "$tableName В usd не должна измениться сумма");
    $this->assertEquals(777, $row['cpa_profit_eur'], "$tableName В eur не должна измениться сумма");
  }

  /**
   * загрузить sql фикстуры из мускуль-файла
   * @param $name
   */
  private function loadFixture($name)
  {
    $this->executeDb('SET FOREIGN_KEY_CHECKS=0;');
    $this->executeDb(
      file_get_contents(__DIR__ . "/../../_data/partner-program/{$name}.sql")
    );
    $this->executeDb('SET FOREIGN_KEY_CHECKS=1;');
  }

  /**
   * в рамках тесткейсов в фикстурах подразумевается что есть всего одна строка профитов
   * @param $tableName
   * @return array|bool
   */
  private function getRowFromDb($tableName)
  {
    return (new Query())->from($tableName)->one();
  }

  /**
   * @param int $programId
   */
  private function runHandler($programId = null)
  {
    $handler = new PersonalProfitsActualizeCourses();
    if ($programId) {
      $handler->partnerProgramId = $programId;
    }
    $handler->run();
  }
}
