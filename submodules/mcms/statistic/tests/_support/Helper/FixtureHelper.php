<?php

namespace mcms\statistic\tests\Helper;

use Codeception\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\test\FixtureTrait;
use yii\test\InitDbFixture;

/**
 * This helper is used to populate the database with needed fixtures before any tests are run.
 * In this example, the database is populated with the demo login user, which is used in acceptance
 * and functional tests.  All fixtures will be loaded before the suite is started and unloaded after it
 * completes.
 */
class FixtureHelper extends Module
{

  /**
   * Redeclare visibility because codeception includes all public methods that do not start with "_"
   * and are not excluded by module settings, in actor class.
   */
  use FixtureTrait {
    loadFixtures as public;
    fixtures as public;
    globalFixtures as public;
    createFixtures as public;
    unloadFixtures as protected;
    getFixtures as protected;
    getFixture as protected;
  }

  /**
   * Method called before any suite tests run. Loads User fixture login user
   * to use in acceptance and functional tests.
   * @param array $settings
   */
  public function _beforeSuite($settings = [])
  {
    include __DIR__.'/../../api/_bootstrap.php';
    $this->loadFixtures();
  }

  /**
   * Method is called after all suite tests run
   */
  public function _afterSuite()
  {
    $this->unloadFixtures();
  }

  /**
   * @inheritdoc
   */
  public function globalFixtures()
  {
    return [
      InitDbFixture::class,
    ];
  }


  /**
   * @param array $fixtures
   * @return array
   */
  public function convertFixtures(array $fixtures)
  {
    $out = [];
    foreach ($fixtures as $fixture) {
      $out[$fixture] = $this->convertFixture($fixture);
    }
    return $out;
  }

  /**
   * @param $fixture
   * @return mixed
   */
  public function convertFixture($fixture)
  {
    list($module, $fixtureCode) = StringHelper::explode($fixture, '.');

    return ArrayHelper::getValue(Yii::$app->getModule($module)->fixtures, $fixtureCode);
  }

  /**
   * @param $moduleId
   * @param $setting
   * @param $value
   * @return bool
   */
  public function setModuleSetting($moduleId, $setting, $value)
  {
    $module = Yii::$app->getModule($moduleId)->settings;
    $module->offsetSet($setting, $value);
  }

  /**
   * @inheritdoc
   */
  public function fixtures()
  {
    return $this->convertFixtures([
      'users.users',
      'statistic.hits_day_group'
    ]);
  }


}