<?php

use console\components\Migration;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190304_152947_add_email_server_name_setting extends Migration
{
  /**
   * @var SettingsBuilder $settingsBuilder
   */
  private $settingsBuilder;

  public function init()
  {
    parent::init();

    $this->settingsBuilder = Yii::$app->settingsBuilder;
  }

  public function up()
  {
    $this->settingsBuilder->createSetting(
      [
        'en' => 'Site url for emails',
        'ru' => 'Url сайта для отправки по email'
      ],
      [],
      'settings.email_server_name',
      ['EditModuleSettingsPartners'],
      Setting::TYPE_STRING,
      'app.common.group_project',
      '',
      [['string'], ['url']],
      3
    );

  }

  /**
   */
  public function down()
  {
    $this->settingsBuilder->removeSetting('settings.email_server_name');
  }
}
