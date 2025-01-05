<?php

use console\components\Migration;

/**
*/
class m201021_140042_schema extends Migration
{

  public $db = 'clickhouse';

  /**
  */
  public function up()
  {
    $this->getDb()->createCommand("
      CREATE TABLE IF NOT EXISTS hits (
        hit_id UInt64,
        timestamp DateTime('Europe/Moscow'),
        is_tb UInt32,
        is_unique UInt8,
        traffic_type UInt8,
        date Date,
        hour UInt8,
        source_id UInt32,
        landing_id UInt32,
        operator_id UInt32,
        platform_id UInt32,
        landing_pay_type_id UInt32,
        subid1_id FixedString(128),
        subid2_id FixedString(128)
      ) ENGINE = MergeTree() PARTITION BY toYear(timestamp) ORDER BY (hit_id) SETTINGS index_granularity=1024;
    ")->execute();

    $this->getDb()->createCommand("
      CREATE TABLE IF NOT EXISTS subscription_rebills (
        id UInt64,
        hit_id UInt64,
        trans_id String,
        timestamp DateTime('Europe/Moscow'),
        date Date,
        hour UInt8,
        default_profit Decimal(9,5),
        default_profit_currency UInt8,
        currency_id UInt8,
        real_profit_rub Decimal(9,5),
        real_profit_eur Decimal(9,5),
        real_profit_usd Decimal(9,5),
        reseller_profit_rub Decimal(9,5),
        reseller_profit_eur Decimal(9,5),
        reseller_profit_usd Decimal(9,5),
        profit_rub Decimal(9,5),
        profit_eur Decimal(9,5),
        profit_usd Decimal(9,5),
        landing_id UInt32,
        source_id UInt32,
        old_source_id UInt32,
        operator_id UInt32,
        platform_id UInt32,
        landing_pay_type_id UInt32,
        is_cpa Bool,
        provider_id UInt32
      ) ENGINE = MergeTree() PARTITION BY toYear(timestamp) ORDER BY (id) SETTINGS index_granularity=1024;
    ")->execute();

    $this->getDb()->createCommand("
      CREATE TABLE IF NOT EXISTS sold_subscriptions (
        id UInt64,
        hit_id UInt64,
        currency_id UInt8,
        real_price_rub Decimal(9,5),
        real_price_eur Decimal(9,5),
        real_price_usd Decimal(9,5),
        reseller_price_rub Decimal(9,5),
        reseller_price_eur Decimal(9,5),
        reseller_price_usd Decimal(9,5),
        price_rub Decimal(9,5),
        price_eur Decimal(9,5),
        price_usd Decimal(9,5),
        profit_rub Decimal(9,5),
        profit_eur Decimal(9,5),
        profit_usd Decimal(9,5),
        timestamp DateTime('Europe/Moscow'),
        date Date,
        stream_id UInt32,
        source_id UInt32,
        user_id UInt32,
        to_stream_id UInt32,
        to_source_id UInt32,
        to_user_id UInt32,
        landing_id UInt32,
        operator_id UInt32,
        platform_id UInt32,
        landing_pay_type_id UInt32,
        provider_id UInt32,
        country_id UInt32,
        is_visible_to_partner Bool
      ) ENGINE = MergeTree() PARTITION BY toYear(timestamp) ORDER BY (id) SETTINGS index_granularity=1024;
    ")->execute();

    $this->getDb()->createCommand("
      CREATE TABLE IF NOT EXISTS subscriptions (
        id UInt64,
        hit_id UInt64,
        trans_id String,
        timestamp DateTime('Europe/Moscow'),
        date Date,
        hour UInt8,
        landing_id UInt32,
        source_id UInt32,
        operator_id UInt32,
        platform_id UInt32,
        landing_pay_type_id UInt32,
        phone String,
        is_cpa Bool,
        currency_id UInt32,
        provider_id UInt32,
        is_fake Bool
      ) ENGINE = MergeTree() PARTITION BY toYear(timestamp) ORDER BY (id) SETTINGS index_granularity=1024;
    ")->execute();
  }

  /**
  */
  public function down()
  {
    $this->getDb()->createCommand("DROP TABLE IS EXISTS subscriptions;")->execute();
    $this->getDb()->createCommand("DROP TABLE IS EXISTS hits;")->execute();
    $this->getDb()->createCommand("DROP TABLE IS EXISTS subscription_rebills;")->execute();
    $this->getDb()->createCommand("DROP TABLE IS EXISTS sold_subscriptions;")->execute();
  }
}
