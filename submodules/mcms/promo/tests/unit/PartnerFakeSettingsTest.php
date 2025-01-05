<?php

namespace tests\unit;

use mcms\common\codeception\TestCase;
use mcms\promo\Module;
use Yii;

/**
 * Class PartnerFakeSettingsTest
 * @package tests\unit
 */
class PartnerFakeSettingsTest extends TestCase
{

  public function _fixtures()
  {
    return $this->convertFixtures([
      'users.users',
      'promo.user_promo_settings'
    ]);
  }

  protected function setUp()
  {
    parent::setUp();
    $this->saveSetting(Module::SETTINGS_INDIVIDUAL_FAKE_SETTINGS_ENABLE, true);

    $this->saveSetting(Module::SETTINGS_FAKE_ADD_AFTER_SUBSCRIPTIONS, 1);
    $this->saveSetting(Module::SETTINGS_FAKE_ADD_SUBSCRIPTION_PERCENT, 2);
    $this->saveSetting(Module::SETTINGS_FAKE_ADD_CPA_SUBSCRIPTION_PERCENT, 3);
  }

  public function testChangedSettings()
  {
    $settings = Yii::$app->getModule('promo')
      ->api('fakeRevshareSettings', ['partnerId' => 101])
      ->getResult();

    $this->assertEquals($settings['on_subscriptions_after_subscriptions_count'], 10, 'Не корректное значение add_fake_after_subscriptions у партнера');
    $this->assertEquals($settings['on_subscriptions_percent'], 11, 'Не корректное значение add_fake_subscription_percent у партнера');
    $this->assertEquals($settings['on_cpa_subscriptions_percent'], 12, 'Не корректное значение add_fake_cpa_subscription_percent у партнера');
  }

  public function testChangedZeroSettings()
  {
    $settings = Yii::$app->getModule('promo')
      ->api('fakeRevshareSettings', ['partnerId' => 102])
      ->getResult();

    $this->assertEquals($settings['on_subscriptions_after_subscriptions_count'], 0, 'Не корректное значение add_fake_after_subscriptions у партнера');
    $this->assertEquals($settings['on_subscriptions_percent'], 0, 'Не корректное значение add_fake_subscription_percent у партнера');
    $this->assertEquals($settings['on_cpa_subscriptions_percent'], 0, 'Не корректное значение add_fake_cpa_subscription_percent у партнера');
  }

  public function testMainSettings()
  {
    $settings = Yii::$app->getModule('promo')
      ->api('fakeRevshareSettings', ['partnerId' => 103])
      ->getResult();

    $this->assertEquals($settings['on_subscriptions_after_subscriptions_count'], 1, 'Не корректное значение add_fake_after_subscriptions в настройке модуля');
    $this->assertEquals($settings['on_subscriptions_percent'], 2, 'Не корректное значение add_fake_subscription_percent в настройке модуля');
    $this->assertEquals($settings['on_cpa_subscriptions_percent'], 3, 'Не корректное значение add_fake_cpa_subscription_percent в настройке модуля');
  }

  public function testDisabledSettings()
  {
    $this->saveSetting(Module::SETTINGS_INDIVIDUAL_FAKE_SETTINGS_ENABLE, false);

    $settings = Yii::$app->getModule('promo')
      ->api('fakeRevshareSettings', ['partnerId' => 101])
      ->getResult();

    $this->assertEquals($settings['on_subscriptions_after_subscriptions_count'], 1, 'Не корректное значение add_fake_after_subscriptions в настройке модуля');
    $this->assertEquals($settings['on_subscriptions_percent'], 2, 'Не корректное значение add_fake_subscription_percent в настройке модуля');
    $this->assertEquals($settings['on_cpa_subscriptions_percent'], 3, 'Не корректное значение add_fake_cpa_subscription_percent в настройке модуля');
  }

  private function saveSetting($settingName, $value)
  {
    $promoModule = Yii::$app->getModule('promo');
    $promoModule->settings->offsetSet($settingName, $value);
  }
}
