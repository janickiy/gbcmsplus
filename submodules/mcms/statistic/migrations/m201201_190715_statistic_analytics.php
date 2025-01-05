<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 *
 */
class m201201_190715_statistic_analytics extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->dropTable('statistic_rebills_day_group');

    $this->createTable('statistic_analytics', [
      'count_ons_revshare' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_ons_sold' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_ons_rejected' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_offs_revshare' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_offs_sold' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'count_offs_rejected' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'cumulative_offs_revshare' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'cumulative_offs_sold' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'cumulative_offs_rejected' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
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
      'date_diff' => 'SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0',
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

    $this->createIndex(
      'idx-statistic_analytics-date-date_diff',
      'statistic_analytics',
      ['date', 'date_diff']
    );
    $this->createIndex(
      'idx-statistic_analytics-date_diff-end',
      'statistic_analytics',
      ['date_on', 'source_id', 'landing_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'is_fake', 'date_diff',]
    );
  }

  /**
   */
  public function down()
  {
    $this->dropIndex('idx-statistic_analytics-date-end', 'statistic_analytics');
    $this->dropTable('statistic_analytics');

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
}
