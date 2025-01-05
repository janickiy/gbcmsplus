<?php

use console\components\Migration;
use mcms\common\helpers\ArrayHelper;
use rgk\utils\traits\PermissionTrait;
use mcms\common\helpers\Console;

/**
*/
class m181108_114023_rebill_correct_conditions_refactoring extends Migration
{
  const CONDITIONS = 'rebill_correct_conditions';
  const REBILLS = 'subscription_rebills';

  const CORRECTED_REBILLS = 'subscription_rebills_corrected';

  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->dropForeignKey(self::CONDITIONS . '_to_source_id_fk', self::CONDITIONS);
    $this->dropColumn(self::CONDITIONS, 'to_source_id');

    $this->createTable(self::CORRECTED_REBILLS, [
      'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
      'hit_id' => 'INT(10) UNSIGNED NOT NULL',
      'trans_id' => 'VARCHAR(64) NOT NULL',
      'time' => 'INT(10) UNSIGNED NOT NULL',
      'date' => 'DATE NOT NULL',
      'hour' => 'TINYINT(1) UNSIGNED NOT NULL',
      'default_profit' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'default_profit_currency' => 'TINYINT(1) UNSIGNED NOT NULL',
      'currency_id' => 'TINYINT(1) UNSIGNED NOT NULL',
      'real_profit_rub' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'real_profit_usd' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'real_profit_eur' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'reseller_profit_rub' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'reseller_profit_usd' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'reseller_profit_eur' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'profit_rub' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'profit_eur' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'profit_usd' => 'DECIMAL(9,5) UNSIGNED NOT NULL',
      'landing_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'source_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'operator_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'platform_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
      'landing_pay_type_id' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
      'is_cpa' => 'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0',
    ]);

    $this->createIndex('src_time_source_landing_operator', self::CORRECTED_REBILLS, ['time', 'source_id','landing_id','operator_id']);

    $this->createIndex('src_group_by_hour', self::CORRECTED_REBILLS
      , ['date', 'source_id', 'landing_id', 'operator_id', 'platform_id', 'hour', 'landing_pay_type_id', 'is_cpa', 'real_profit_rub', 'reseller_profit_rub', 'profit_rub', 'profit_eur', 'profit_usd']);

    $this->createIndex('src_reseller_profit_statistics', self::CORRECTED_REBILLS, ['date', 'source_id', 'landing_id', 'operator_id', 'platform_id', 'landing_pay_type_id']);

    $this->addForeignKey('src_source_id_fk', self::CORRECTED_REBILLS, 'source_id', 'sources', 'id');

    $this->createIndex('src_ss_group', self::CORRECTED_REBILLS
      , ['hit_id', 'time', 'source_id', 'landing_id', 'operator_id', 'platform_id', 'landing_pay_type_id', 'is_cpa', 'currency_id', 'provider_id']);

    $this->createIndex('src_ss_group_profits', self::CORRECTED_REBILLS, [
      'hit_id',
      'time',
      'real_profit_rub',
      'real_profit_eur',
      'real_profit_usd',
      'reseller_profit_rub',
      'reseller_profit_eur',
      'reseller_profit_usd',
      'profit_rub',
      'profit_eur',
      'profit_usd'
    ]);
  }

  /**
  */
  public function down()
  {
    $this->addColumn(self::CONDITIONS, 'to_source_id', 'mediumint(5) unsigned default 0 not null');

    $this->dropIndex('src_time_source_landing_operator', self::CORRECTED_REBILLS);
    $this->dropIndex('src_group_by_hour', self::CORRECTED_REBILLS);
    $this->dropIndex('src_reseller_profit_statistics', self::CORRECTED_REBILLS);
    $this->dropForeignKey('src_source_id_fk', self::CORRECTED_REBILLS);
    $this->dropIndex('src_ss_group', self::CORRECTED_REBILLS);
    $this->dropIndex('src_ss_group_profits', self::CORRECTED_REBILLS);

    $this->dropTable(self::CORRECTED_REBILLS);
  }
}
