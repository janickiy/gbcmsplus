#!/bin/bash
set -e

clickhouse client -n <<-EOSQL
    DROP DATABASE IF EXISTS mcms;
    CREATE DATABASE IF NOT EXISTS mcms;
    CREATE TABLE IF NOT EXISTS mcms.hits (
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
      subid1 String,
      subid1_id FixedString(32),
      subid2 String,
      subid2_id FixedString(32)
    ) ENGINE = MergeTree() PARTITION BY toYear(timestamp) ORDER BY (hit_id) SETTINGS index_granularity=1024;

    CREATE TABLE IF NOT EXISTS mcms.rebills (
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

    CREATE TABLE IF NOT EXISTS mcms.sold_subscriptions (
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

    CREATE TABLE IF NOT EXISTS mcms.subscriptions (
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

    DROP TABLE IF EXISTS mcms.stat_filters;
    CREATE TABLE IF NOT EXISTS mcms.stat_filters (
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

    DROP TABLE IF EXISTS mcms.action_logs;
    CREATE TABLE mcms.action_logs (
        EventTime DateTime('Europe/Moscow'),
        EventClass String,
        EventType Enum8('INSERT' = 1, 'UPDATE' = 2, 'DELETE' = 3),
        PK String,
        Old String,
        New String,
        UserID UInt32
    )
    ENGINE = MergeTree()
    PARTITION BY toYYYYMM(EventTime)
    ORDER BY (EventTime, EventClass, PK, UserID)
    TTL EventTime + toIntervalMonth(12)
    SETTINGS index_granularity = 8192;

    DROP TABLE IF EXISTS mcms.system_logs;
    CREATE TABLE mcms.system_logs (
        EventTime DateTime('Europe/Moscow'),
        EventLabel String,
        EventData String
    )
    ENGINE = MergeTree()
    PARTITION BY toYYYYMM(EventTime)
    ORDER BY (EventTime, EventLabel)
    TTL EventTime + toIntervalMonth(3)
    SETTINGS index_granularity = 8192;

EOSQL