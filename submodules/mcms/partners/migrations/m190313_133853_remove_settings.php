<?php

use console\components\Migration;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190313_133853_remove_settings extends Migration
{
  use PermissionTrait;

  /** @var \rgk\settings\components\SettingsBuilder $settingsBuilder */
  private $settingsBuilder;

  public function init()
  {
    parent::init();
    $this->settingsBuilder = Yii::$app->settingsBuilder;
  }

  /**
  */
  public function up()
  {
    $this->settingsBuilder->removeSetting('settings.google_analytics_script');
    $this->settingsBuilder->removeSetting('settings.yandex_metrika_script');
    $this->settingsBuilder->removeSetting('settings.robots_txt');
    // Настройку 'Config for static pages handler' не удаляю, а делаю недоступной
    $this->execute('UPDATE rgk_settings SET is_disabled = 1 WHERE `key` = :key', [':key' => 'settings.static_pages_config']);
  }

  /**
  */
  public function down()
  {
    $this->settingsBuilder->createSetting(
      [
        'en' => 'Google Analytics script',
        'ru' => 'Скрипт Google Analytics'
      ],
      [ ],
      'settings.google_analytics_script',
      ['EditModuleSettingsPartners'],
      Setting::TYPE_TEXT,
      'app.common.form_group_lp_parameters',
      '',
      [['safe']],
      4
    );

    $this->settingsBuilder->createSetting(
      [
        'en' => 'Yandex.Metrika script',
        'ru' => 'Скрипт Яндекс.Метрики'
      ],
      [ ],
      'settings.yandex_metrika_script',
      ['EditModuleSettingsPartners'],
      Setting::TYPE_TEXT,
      'app.common.form_group_lp_parameters',
      '',
      [['safe']],
      5
    );

    $this->settingsBuilder->createSetting(
      [
        'en' => 'Robots.txt',
        'ru' => 'Robots.txt'
      ],
      [ ],
      'settings.robots_txt',
      ['EditModuleSettingsPartners'],
      Setting::TYPE_TEXT,
      'app.common.form_group_lp_parameters',
      '',
      [['string']],
      3
    );

    $this->execute('UPDATE rgk_settings SET is_disabled = 0 WHERE `key` = :key', [':key' => 'settings.static_pages_config']);
  }
}
