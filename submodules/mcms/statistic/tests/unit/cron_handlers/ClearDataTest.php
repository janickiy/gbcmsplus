<?php
namespace mcms\statistic\tests\unit\statistic;

use DateTime;
use mcms\common\codeception\TestCase;
use mcms\common\output\FakeOutput;
use mcms\statistic\components\clear\HitsIntermediate;
use mcms\statistic\components\clear\HitsRename;
use Yii;

class ClearDataTest extends TestCase
{

  protected function setUp()
  {
    parent::setUp();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hits')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hit_params')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE logs')->execute();

    // удаляем бэкапы таблиц чтоб сильно не мусорить
    $bakTables = Yii::$app->db->createCommand('SELECT table_name
      FROM information_schema.tables
      where table_schema = DATABASE()
      AND (table_name like \'%hits_bak_%\' OR table_name like \'%hit_params_bak_%\') 
      ;')->queryColumn();
    foreach ($bakTables as $bakTable) {
      Yii::$app->db->createCommand("DROP TABLE $bakTable")->execute();
    }

    Yii::$app->db->createCommand('TRUNCATE TABLE search_subscriptions')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE onetime_subscriptions')->execute();
    Yii::$app->db->createCommand('DROP TABLE IF EXISTS hits_intermediate')->execute();
    Yii::$app->db->createCommand('DROP TABLE IF EXISTS hit_params_intermediate')->execute();
    Yii::$app->db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
    $this->loginAsReseller();
  }

  // Хитпарапмс по ТБ должна хараниться 2 недели
  public function testTbHitParams()
  {
    $timeTb = $this->getTimestamp('-14 days');
    $timeHits = $this->getTimestamp('-30 days');

    // Записали хиты и хит парамс
    $from = time();
    $this->setHits();
    // Очищаем
    (new HitsIntermediate())->setLogger(new FakeOutput())->run();
    (new HitsRename(['updateFrom' => $from]))->setLogger(new FakeOutput())->run();

    // Все оставшиеся хиты должны быть младше 2-х недель, если это ТБ и младше 30 дней, если не ТБ
    $hitsTb = Yii::$app->db->createCommand('SELECT h.* FROM hits h INNER JOIN hit_params hp ON h.id = hp.hit_id WHERE h.is_tb > 0')->queryAll();
    foreach ($hitsTb as $hit) {
      $this->assertGreaterThan($timeTb, $hit['time'], "Хит #{$hit['id']} старше 14 дней");
    }
    $this->assertEquals(1, count($hitsTb), 'Удалились лишние хиты  ТБ');

    $hits = Yii::$app->db->createCommand('SELECT h.* FROM hits h INNER JOIN hit_params hp ON h.id = hp.hit_id')->queryAll();
    foreach ($hits as $hit) {
      $this->assertGreaterThan($timeHits, $hit['time'], "Хит #{$hit['id']} старше 30 дней");
    }
    $this->assertEquals(2, count($hits), 'Удалились лишние хиты');
  }

  /**
   * Перевод строковой даты в timestamp
   * @param $time
   * @return int
   */
  private function getTimestamp($time)
  {
    $dateFrom = new DateTime($time);
    return $dateFrom->getTimestamp();
  }


  /**
   * Установка хитов
   */
  private function setHits()
  {
    Yii::$app->db->createCommand('TRUNCATE TABLE hits')->execute();
    Yii::$app->db->createCommand('TRUNCATE TABLE hit_params')->execute();
    /** @var array $hits */
    /** @var array $hitParams */
    require_once __DIR__ . '/../../_data/clear_data/hits.php';
    require_once __DIR__ . '/../../_data/clear_data/hit_params.php';

    Yii::$app->db->createCommand()->batchInsert('hits', [
      'id',
      'is_unique',
      'is_tb',
      'time',
      'date',
      'hour',
      'operator_id',
      'landing_id',
      'source_id',
      'platform_id',
      'landing_pay_type_id',
      'is_cpa',
    ], $hits)->execute();

    Yii::$app->db->createCommand()->batchInsert('hit_params', [
      'hit_id',
    ], $hitParams)->execute();
  }

}