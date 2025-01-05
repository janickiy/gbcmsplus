<?php

use console\components\Migration;
use rgk\settings\models\Setting;

/**
*/
class m181126_154409_statistic_ratio_setting extends Migration
{
  /** @var \rgk\settings\components\SettingsBuilder $settingsBuilder */
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
        'en' => 'Show ratio in statistic',
        'ru' => 'Показывать ратио в статистике'
      ],
      [ ],
      'settings.partners.show_ratio',
      ['EditModuleSettingsPartners'],
      Setting::TYPE_BOOLEAN,
      'app.common.group_partners',
      false,
      [['boolean']],
      7
    );

  }

  /**
  */
  public function down()
  {
    $this->settingsBuilder->removeSetting('settings.partners.show_ratio');
  }
}
