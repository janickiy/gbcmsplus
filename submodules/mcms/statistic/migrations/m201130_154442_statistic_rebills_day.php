<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 *
 */
class m201130_154442_statistic_rebills_day extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->createTable('statistic_rebills_day_group', [
      'count_rebills_revshare' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_rebills_sold' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_rebills_rejected' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_rub_revshare' => 'DECIMAL(12,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_usd_revshare' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_eur_revshare' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_rub_sold' => 'DECIMAL(12,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_usd_sold' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_eur_sold' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_rub_rejected' => 'DECIMAL(12,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_usd_rejected' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'profit_eur_rejected' => 'DECIMAL(9,5) UNSIGNED NOT NULL DEFAULT 0',
      'date_on' => 'DATE NOT NULL',
      'date' => 'DATE NOT NULL',
      'source_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'landing_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'operator_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'platform_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'landing_pay_type_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'is_fake' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'currency_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'user_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'stream_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'country_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',

      'PRIMARY KEY (date_on, date, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake)'
    ]);

  }

  /**
   */
  public function down()
  {
    $this->dropTable('statistic_rebills_day_group');
  }
}
