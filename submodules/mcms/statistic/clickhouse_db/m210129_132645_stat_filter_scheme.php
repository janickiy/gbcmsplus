<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m210129_132645_stat_filter_scheme extends Migration
{
  public $db = 'clickhouse';

  public function up()
  {
    $this->getDb()->createCommand("
      CREATE TABLE IF NOT EXISTS stat_filters (
      user_id UInt32,
      landing_id UInt32,
      operator_id UInt32,
      country_id UInt32,
      platform_id UInt32,
      landing_pay_type_id UInt32,
      provider_id UInt32,
      source_id UInt32,
      stream_id UInt32
    ) ENGINE = ReplacingMergeTree() ORDER BY (user_id, landing_id, operator_id, country_id, platform_id, landing_pay_type_id, provider_id, source_id, stream_id) SETTINGS index_granularity=8192;
    ")->execute();
  }

  public function down()
  {
    echo "m210129_132645_stat_filter_scheme cannot be reverted.\n";
  }
}
