<?php
namespace mcms\promo\tests\unit\sources;

use mcms\common\codeception\TestCase;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use mcms\promo\models\Operator;
use Yii;

/**
 * Class LandSetLandsUpdaterTest
 * @package mcms\promo\tests\unit\sources
 */
class LandSetLandsUpdaterTest extends TestCase
{

  /** @var  LandingSet */
  public $set;

  public function _fixtures()
  {
    return $this->convertFixtures([
      'promo.sources',
      'promo.landings',
      'promo.operators',
      'promo.landing_operators',
    ]);
  }

  protected function setUp()
  {
    parent::setUp();

    $this->loginAsRoot();

    Yii::$app->db->createCommand("
      INSERT INTO landing_sets (id, name, category_id, autoupdate, created_by, updated_by, created_at, updated_at) VALUES (1, 'test_set', NULL, 0, 1, 1, 1477641426, 1478009709) ON DUPLICATE KEY UPDATE id=id;
    ")->execute();

    Yii::$app->db->createCommand("TRUNCATE TABLE landing_set_items")->execute();

    $this->set = LandingSet::findOne(1);
  }

  /**
   * Добавление лендов к набору с синком
   */
  public function testAddLandingSyncTrue()
  {
    $this->setAutoUpdateTrue();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertEquals(2, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Добавление лендов к набору синк выключен
   */
  public function testAddLandingSyncFalse()
  {
    $this->setAutoUpdateFalse();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(0, count($itemLandingIds));
  }

  /**
   * Удаление ленд-оператора если синк включен
   */
  public function testDeleteLandOperatorSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->deleteLandOperator();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(2, count($itemLandingIds));
    self::assertEquals(1, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Удаление ленд-оператора если синк выключен
   */
  public function testDeleteLandOperatorSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->deleteLandOperator();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(2, count($itemLandingIds));
    self::assertEquals(1, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Выключение ленда, синк включен
   */
  public function testLandDisableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableLand();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(1, count($itemLandingIds));
    self::assertTrue(in_array(4, $itemLandingIds));
  }

  /**
   * Выключение оператора, синк включен
   */
  public function testOperatorDisableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableOperator();

    $items = $this->getItems();
    $itemLandingIds = ArrayHelper::getColumn($items, 'landing_id');

    self::assertEquals(1, count($itemLandingIds));

    $item = reset($items);

    self::assertEquals(3, $item['landing_id']);
    self::assertEquals(4, $item['operator_id']);
  }

  /**
   * Выключение страны, синк включен
   */
  public function testCountryDisableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableCountry();

    $items = $this->getItems();
    $itemLandingIds = ArrayHelper::getColumn($items, 'landing_id');

    self::assertEquals(1, count($itemLandingIds));

    $item = reset($items);

    self::assertEquals(3, $item['landing_id']);
    self::assertEquals(4, $item['operator_id']);
  }

  /**
   * Выключение доступа ленда, синк включен
   */
  public function testLandDisableAccessSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableLandAccess();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertTrue(in_array(4, $itemLandingIds));
  }


  /**
   * Выключение ленда, синк выключен
   */
  public function testLandDisableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableLand();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      if ($item->landing_id == 3) {
        self::assertEquals(1, $item->is_disabled);
        continue;
      }
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Выключение оператора, синк выключен
   */
  public function testOperatorDisableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableOperator();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      if ($item->operator_id == 1) {
        self::assertEquals(1, $item->is_disabled);
        continue;
      }
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Выключение страны, синк выключен
   */
  public function testCountryDisableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableCountry();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      if ($item->operator_id == 1) {
        self::assertEquals(1, $item->is_disabled);
        continue;
      }
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Выключение доступа ленда, синк выключен
   */
  public function testLandDisableAccessSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableLandAccess();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      // tricky: в задаче MCMS-1499 изменили логику и теперь скрытые ленды не отключаются в наборе когда ленду ставишь access_type=0
      self::assertEquals(0, $item->is_disabled);
    }
  }


  /**
   * Включение ленда, синк включен
   */
  public function testLandEnableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableLand();
    $this->enableLand();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertEquals(2, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Включение оператора, синк включен
   */
  public function testOperatorEnableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableOperator();
    $this->enableOperator();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertEquals(2, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Включение страны, синк включен
   */
  public function testCountryEnableSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableCountry();
    $this->enableCountry();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertEquals(2, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }

  /**
   * Включение доступа ленда, синк включен
   */
  public function testLandEnableAccessSyncTrue()
  {
    $this->setAutoUpdateTrue();
    $this->disableLandAccess();
    $this->enableLandAccess();

    $itemLandingIds = ArrayHelper::getColumn($this->getItems(), 'landing_id');

    self::assertEquals(3, count($itemLandingIds));
    self::assertEquals(2, count(array_keys($itemLandingIds, 3)));
    self::assertEquals(1, count(array_keys($itemLandingIds, 4)));
  }


  /**
   * Включение ленда, синк выключен
   */
  public function testLandEnableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableLand();
    $this->enableLand();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Включение оператора, синк выключен
   */
  public function testOperatorEnableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableOperator();
    $this->enableOperator();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Включение страны, синк выключен
   */
  public function testCountryEnableSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableCountry();
    $this->enableCountry();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      self::assertEquals(0, $item->is_disabled);
    }
  }

  /**
   * Включение доступа ленда, синк выключен
   */
  public function testLandEnableAccessSyncFalse()
  {
    $this->setAutoUpdateTrue();
    $this->setAutoUpdateFalse();
    $this->disableLandAccess();
    $this->enableLandAccess();

    $items = $this->getItems();

    self::assertEquals(3, count($items));

    foreach ($items as $item) {
      self::assertEquals(0, $item->is_disabled);
    }
  }


  protected function setAutoUpdateTrue()
  {
    $this->set->autoupdate = 1;
    $this->set->category_id = 6;
    $this->set->save();
  }

  protected function setAutoUpdateFalse()
  {
    $this->set->autoupdate = 0;
    $this->set->category_id = 6;
    $this->set->save();
  }

  protected function deleteLandOperator()
  {
    Yii::$app->db
      ->createCommand()
      ->delete('landing_operators', ['landing_id' => 3, 'operator_id' => 1])
      ->execute();

    $landing = Landing::findOne(3);
    $landing->afterSave(false, []);
  }

  /**
   * @return LandingSetItem[]
   */
  protected function getItems()
  {
    return $this->set->getItems()->all();
  }

  protected function disableLand()
  {
    $landing = Landing::findOne(3);
    $landing->status = 0;
    $landing->save();
  }

  protected function enableLand()
  {
    $landing = Landing::findOne(3);
    $landing->status = 1;
    $landing->save();
  }

  protected function disableOperator()
  {
    $operator = Operator::findOne(1);
    $operator->status = 0;
    $operator->save();
  }

  protected function enableOperator()
  {
    $operator = Operator::findOne(1);
    $operator->status = 1;
    $operator->save();
  }

  protected function disableCountry()
  {
    $country = Country::findOne(1);
    $country->status = 0;
    $country->save();
  }

  protected function enableCountry()
  {
    $country = Country::findOne(1);
    $country->status = 1;
    $country->save();
  }

  protected function disableLandAccess()
  {
    $landing = Landing::findOne(3);
    $landing->access_type = 0;
    $landing->save();
  }

  protected function enableLandAccess()
  {
    $landing = Landing::findOne(3);
    $landing->access_type = Landing::ACCESS_TYPE_NORMAL;
    $landing->save();
  }
}