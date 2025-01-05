<?php

use console\components\Migration;
use mcms\payments\Module;
use rgk\settings\components\SettingsBuilder;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m220430_011343_early_payment_percent_old_default_value extends Migration
{

  /**
   */
  public function up()
  {
    $this->alterColumn('user_payment_settings', 'early_payment_percent_old', 'decimal(4,1) UNSIGNED NOT NULL DEFAULT 0');
  }

  /**
   */
  public function down()
  {
    $this->alterColumn('user_payment_settings', 'early_payment_percent_old', 'decimal(4,1) UNSIGNED NOT NULL');
  }
}
