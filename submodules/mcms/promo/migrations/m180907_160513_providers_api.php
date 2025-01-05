<?php

use console\components\Migration;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m180907_160513_providers_api extends Migration
{
  use PermissionTrait;

  const CATEGORY = 'kp_providers_auto_creation';
  const SETTINGS_PROVIDERS_API_TOKEN = 'settings.providers_api_token';
  const SETTINGS_KP_USER_ID = 'settings.kp_user_id';
  const SETTINGS_KP_SECRET_KEY = 'settings.kp_secret_key';
  const SETTINGS_POSTBACK_URL = 'settings.postback_url';
  const SETTINGS_TRAFFICBACK_URL = 'settings.trafficback_url';
  const SETTINGS_COMPLAINS_URL = 'settings.complains_url';

  /** @var \rgk\settings\components\SettingsBuilder $settingsBuilder */
  private $settingsBuilder;
  /** @var \rgk\settings\components\SettingsManager $settingsManager */
  private $settingsManager;

  public function init()
  {
    parent::init();
    $this->settingsBuilder = Yii::$app->settingsBuilder;
    $this->settingsManager = Yii::$app->settingsManager;
  }

  /**
  */
  public function up()
  {
    $category = $this->settingsManager->getCategoryByKey('other');
    $this->settingsBuilder->createCategory(['ru' => 'Автоматическое создание провайдеров с КП', 'en'=>'Auto creation KP providers'], self::CATEGORY, $category->id);


    $title = ['ru' => 'Token для авторизации на SSO', 'en' => 'SSO authorization token'];
    $permissions = ['EditModuleSettingsPromo'];
    $validators = [["string"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_PROVIDERS_API_TOKEN, $permissions, Setting::TYPE_STRING, self::CATEGORY, '', $validators);

    $title = ['ru' => 'ID пользователя на КП', 'en' => 'KP user id'];
    $validators = [["integer"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_KP_USER_ID, $permissions, Setting::TYPE_INTEGER, self::CATEGORY, '', $validators);

    $this->settingsBuilder->createSetting(
      ['ru' => 'Email для авторизации в kp api', 'en' => 'Kp api auth email'],
      [],
      \mcms\promo\Module::SETTINGS_KP_API_USER_AUTH_EMAIL,
      $permissions,
      Setting::TYPE_STRING,
      self::CATEGORY,
      '',
      [['string']]
    );

    $this->settingsBuilder->createSetting(
      ['ru' => 'Hash для авторизации в kp api', 'en' => 'Kp api auth hash'],
      [],
      \mcms\promo\Module::SETTINGS_KP_API_USER_AUTH_HASH,
      $permissions,
      Setting::TYPE_STRING,
      self::CATEGORY,
      '',
      [['string']]
    );

    $title = ['ru' => 'Secret key на КП', 'en' => 'KP secret key'];
    $validators = [["string"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_KP_SECRET_KEY, $permissions, Setting::TYPE_STRING, self::CATEGORY, '', $validators);

    $title = ['ru' => 'Postback URL', 'en' => 'Postback URL'];
    $validators = [["string"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_POSTBACK_URL, $permissions, Setting::TYPE_STRING, self::CATEGORY, '', $validators);

    $title = ['ru' => 'Trafficback URL', 'en' => 'Trafficback URL'];
    $validators = [["string"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_TRAFFICBACK_URL, $permissions, Setting::TYPE_STRING, self::CATEGORY, '', $validators);

    $title = ['ru' => 'Complains URL', 'en' => 'Complains URL'];
    $validators = [["string"]];
    $this->settingsBuilder->createSetting($title, [], self::SETTINGS_COMPLAINS_URL, $permissions, Setting::TYPE_STRING, self::CATEGORY, '', $validators);

  }

  /**
  */
  public function down()
  {
    $this->settingsBuilder->removeSetting(self::SETTINGS_PROVIDERS_API_TOKEN);
    $this->settingsBuilder->removeSetting(self::SETTINGS_KP_USER_ID);
    $this->settingsBuilder->removeSetting(self::SETTINGS_KP_SECRET_KEY);
    $this->settingsBuilder->removeSetting(self::SETTINGS_POSTBACK_URL);
    $this->settingsBuilder->removeSetting(self::SETTINGS_TRAFFICBACK_URL);
    $this->settingsBuilder->removeSetting(self::SETTINGS_COMPLAINS_URL);


    $this->settingsBuilder->removeCategory(self::CATEGORY);
  }
}
