<?php

namespace tests\unit;

use mcms\common\codeception\TestCase;
use mcms\promo\models\Landing;
use Yii;

/**
 * Class ChangeLandingStatusTest
 * @package tests\unit
 */
class ChangeLandingStatusTest extends TestCase
{
  const TABLE = 'landings';
  const LANDING_ID = 1;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.landings',
    ]);
  }

  //отключили руками - при синке не включится
  public function testOffManuallyOnSync()
  {
    $this->off();

    $this->assertEquals($this->getValue(), 0, 'Лендинг не отключился вручную при флаге allow_sync_status=1');

    $this->on(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 0, 'Лендинг включился при синке после отключения вручную');
  }

  //отключили руками - руками включится
  public function testOffManuallyOnManually()
  {
    $this->off();

    $this->assertEquals($this->getValue(), 0, 'Лендинг не отключился вручную при флаге allow_sync_status=1');

    $this->on();

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился вручную после отключения вручную');
  }

  //включили руками - при синке выключится
  public function testOnManuallyOffSync()
  {
    $allowSyncStatus = 0;

    $this->offSql($allowSyncStatus);

    $this->on();

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился вручную при флаге allow_sync_status=0');

    $this->off(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 0, 'Лендинг не выключился при синке после включения вручную');
  }

  //включили руками - руками выключится
  public function testOnManuallyOffManually()
  {
    $allowSyncStatus = 0;

    $this->offSql($allowSyncStatus);

    $this->on();

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился вручную при флаге allow_sync_status=0');

    $this->off();

    $this->assertEquals($this->getValue(), 0, 'Лендинг не выключился вручную после включения вручную');
  }

  //отключили при синке - при синке включится
  public function testOffSyncOnSync()
  {
    $this->off(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 0, 'Лендинг не отключился при синке при флаге allow_sync_status=1');

    $this->on(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился при синке после отключения при синке');
  }

  //отключили при синке - руками не включится
  public function testOffSyncOnManually()
  {
    $this->off(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 0, 'Лендинг не отключился при синке при флаге allow_sync_status=1');

    $this->on();

    $this->assertEquals($this->getValue(), 0, 'Лендинг включился вручную после отключения при синке');
  }

  //включили при синке - при синке выключится
  public function testOnSyncOffSync()
  {
    $allowSyncStatus = 1;

    $this->offSql($allowSyncStatus);

    $this->on(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился при синке при флаге allow_sync_status=1');

    $this->off(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 0, 'Лендинг не выключился при синке после включения при синке');
  }

  //включили при синке - руками выключится
  public function testOnSyncOffManually()
  {
    $allowSyncStatus = 1;

    $this->offSql($allowSyncStatus);

    $this->on(Landing::SCENARIO_SYNC);

    $this->assertEquals($this->getValue(), 1, 'Лендинг не включился при синке при флаге allow_sync_status=1');

    $this->off();

    $this->assertEquals($this->getValue(), 0, 'Лендинг не выключился вручную после включения при синке');
  }

  private function on($scenario = null)
  {
    $landing = Landing::findOne(self::LANDING_ID);

    if ($scenario !== null) {
      $landing->setScenario($scenario);
    }

    $landing->status = Landing::STATUS_ACTIVE;
    $landing->save();
  }

  private function off($scenario = null)
  {
    $landing = Landing::findOne(self::LANDING_ID);

    if ($scenario !== null) {
      $landing->setScenario($scenario);
    }

    $landing->status = Landing::STATUS_INACTIVE;
    $landing->save();
  }

  // Отключаем лендинг запросом (без валидаций и учета сценариев)
  private function offSql($allowSyncStatus)
  {
    Yii::$app->db->createCommand()->update(self::TABLE, ['status' => Landing::STATUS_INACTIVE, 'allow_sync_status' => $allowSyncStatus], ['id' => self::LANDING_ID])->execute();
  }

  // Вывести текущее значение поля status
  private function getValue()
  {
    return Yii::$app->db->createCommand('select status from landings where id = :landing_id', [':landing_id' => self::LANDING_ID])->queryScalar();
  }


}