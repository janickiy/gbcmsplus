<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m210126_144302_create_sp_tables extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createTable('subscription_correct_conditions', [
      'id' => $this->primaryKey()->unsigned(),
      'name' => $this->string()->notNull(),
      'user_id' => 'mediumint(5) unsigned',
      'operator_id' => 'mediumint(5) unsigned',
      'landing_id' => 'mediumint(5) unsigned',
      'percent' => 'tinyint(1) unsigned NOT NULL DEFAULT 0',
      'is_active' => 'tinyint(1) unsigned NOT NULL DEFAULT 0',
      'created_by' => 'mediumint(5) unsigned NOT NULL',
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ]);

    $this->createIndex(
      'subscription_correct_unique',
      'subscription_correct_conditions',
      ['user_id', 'landing_id', 'operator_id'],
      true
    );

    $this->createPermission(
      'PromoSubscriptionCorrectConditionsController',
      'Контроллер условий коррекции подписок',
      'PromoModule',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoSubscriptionCorrectConditionsIndex',
      'Спиисок условий коррекции подписок',
      'PromoSubscriptionCorrectConditionsController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoSubscriptionCorrectConditionsCreate',
      'Создание условий коррекции подписок',
      'PromoSubscriptionCorrectConditionsController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoSubscriptionCorrectConditionsUpdateModal',
      'Редактирование условий коррекции подписок',
      'PromoSubscriptionCorrectConditionsController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoSubscriptionCorrectConditionsDelete',
      'Удаление условий коррекции подписок',
      'PromoSubscriptionCorrectConditionsController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $subscriptionsSql = <<<SQL
CREATE TABLE `sp_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hit_id` int(10) unsigned NOT NULL,
  `trans_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(1) unsigned NOT NULL,
  `landing_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `source_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `operator_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `platform_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `landing_pay_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `phone` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `is_cpa` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `currency_id` tinyint(1) unsigned NOT NULL COMMENT 'оригинальная валюта',
  `provider_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `is_fake` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sp_subscr_hit_id_uk` (`hit_id`),
  KEY `sp_subscriptions_source_id_fk` (`source_id`),
  KEY `sp_subscriptions_group_by_hour_index` (`date`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`hour`,`landing_pay_type_id`,`is_cpa`,`is_fake`),
  KEY `sp_subscriptions_ss_group_index` (`time`,`hit_id`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`landing_pay_type_id`,`is_cpa`,`currency_id`,`is_fake`),
  KEY `sp_subscriptions_phone_index` (`phone`),
  CONSTRAINT `sp_subscriptions_source_id_fk` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
    $this->db->createCommand($subscriptionsSql)->execute();

    $rebillsSql = <<<SQL
CREATE TABLE `sp_subscription_rebills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hit_id` int(10) unsigned NOT NULL,
  `trans_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(1) unsigned NOT NULL,
  `default_profit` decimal(9,5) unsigned NOT NULL,
  `default_profit_currency` tinyint(1) unsigned NOT NULL,
  `currency_id` tinyint(1) unsigned NOT NULL,
  `real_profit_rub` decimal(9,5) unsigned NOT NULL,
  `real_profit_eur` decimal(9,5) unsigned NOT NULL,
  `real_profit_usd` decimal(9,5) unsigned NOT NULL,
  `reseller_profit_rub` decimal(9,5) unsigned NOT NULL,
  `reseller_profit_eur` decimal(9,5) unsigned NOT NULL,
  `reseller_profit_usd` decimal(9,5) unsigned NOT NULL,
  `profit_rub` decimal(9,5) unsigned NOT NULL,
  `profit_eur` decimal(9,5) unsigned NOT NULL,
  `profit_usd` decimal(9,5) unsigned NOT NULL,
  `landing_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `source_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `old_source_id` mediumint(5) unsigned DEFAULT NULL,
  `operator_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `platform_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `landing_pay_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_cpa` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `provider_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sp_subscr_rebills_trans_id_provider_id_uk` (`trans_id`,`provider_id`),
  KEY `sp_subscription_rebills_source_id_fk` (`source_id`),
  KEY `sp_subscription_rebills_group_by_hour` (`date`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`hour`,`landing_pay_type_id`,`is_cpa`,`real_profit_rub`,`reseller_profit_rub`,`profit_rub`,`profit_eur`,`profit_usd`),
  KEY `sp_subscription_rebills_ss_group_profits` (`hit_id`,`time`,`real_profit_rub`,`real_profit_eur`,`real_profit_usd`,`reseller_profit_rub`,`reseller_profit_eur`,`reseller_profit_usd`,`profit_rub`,`profit_eur`,`profit_usd`),
  KEY `sp_subscription_rebills_ss_group` (`hit_id`,`time`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`landing_pay_type_id`,`is_cpa`,`currency_id`,`provider_id`),
  KEY `sp_sr_time_source_landing_operator` (`time`,`source_id`,`landing_id`,`operator_id`),
  KEY `sp_subscription_rebills_reseller_profit_statistics` (`date`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`landing_pay_type_id`),
  CONSTRAINT `sp_subscription_rebills_source_id_fk` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
    $this->db->createCommand($rebillsSql)->execute();

    $offsSql = <<<SQL
CREATE TABLE `sp_subscription_offs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hit_id` int(10) unsigned NOT NULL,
  `trans_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(1) unsigned NOT NULL,
  `landing_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `source_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `operator_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `platform_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `landing_pay_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_cpa` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `currency_id` tinyint(1) unsigned NOT NULL,
  `provider_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `is_fake` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sp_subscr_off_hit_id_uk` (`hit_id`),
  KEY `sp_subscription_offs_source_id_fk` (`source_id`),
  KEY `sp_subscription_offs_ss_group` (`time`,`hit_id`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`is_cpa`),
  KEY `sp_subscription_offs_group_by_hour_index` (`date`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`hour`,`landing_pay_type_id`,`is_cpa`,`is_fake`),
  CONSTRAINT `sp_subscription_offs_source_id_fk` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
    $this->db->createCommand($offsSql)->execute();

    $complaintsSql = <<<SQL
CREATE TABLE `sp_subscription_complaints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hit_id` int(10) unsigned NOT NULL,
  `trans_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(1) unsigned NOT NULL,
  `landing_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `source_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `operator_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `platform_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `landing_pay_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `provider_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `country_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `stream_id` mediumint(5) unsigned DEFAULT 0,
  `user_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `description` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label1` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label2` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT '1-текст,2-звонок',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sp_complains_hit_id_trans_id_uq` (`hit_id`,`trans_id`),
  KEY `sp_cmpl_group_by_day_user` (`date`,`user_id`),
  KEY `sp_complains_source_id_fk` (`source_id`),
  KEY `sp_complains_type_index` (`type`),
  CONSTRAINT `sp_complains_source_id_fk` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
    $this->db->createCommand($complaintsSql)->execute();

    $refundsSql = <<<SQL
CREATE TABLE `sp_subscription_refunds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `hit_id` bigint(20) unsigned NOT NULL,
  `trans_id` varchar(64) COLLATE utf8_bin NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `hour` tinyint(1) unsigned NOT NULL,
  `description` varchar(512) COLLATE utf8_bin DEFAULT NULL,
  `currency_id` tinyint(1) unsigned NOT NULL,
  `local_currency` char(3) CHARACTER SET latin1 NOT NULL,
  `local_sum` decimal(9,5) unsigned NOT NULL,
  `reseller_rub` decimal(9,5) unsigned NOT NULL,
  `reseller_usd` decimal(9,5) unsigned NOT NULL,
  `reseller_eur` decimal(9,5) unsigned NOT NULL,
  `landing_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `source_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `operator_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `platform_id` mediumint(5) unsigned NOT NULL DEFAULT 0,
  `landing_pay_type_id` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_cpa` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sp_refunds_hit_id_uq` (`hit_id`),
  KEY `sp_refunds_statistic` (`date`,`source_id`,`landing_id`,`operator_id`,`platform_id`,`hour`,`landing_pay_type_id`,`is_cpa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
SQL;
    $this->db->createCommand($refundsSql)->execute();
  }

  /**
  */
  public function down()
  {
    $this->dropTable('sp_subscriptions');
    $this->dropTable('sp_subscription_offs');
    $this->dropTable('sp_subscription_rebills');
    $this->dropTable('sp_subscription_complaints');
    $this->dropTable('sp_subscription_refunds');

    $this->revokeRolesPermission('PromoSubscriptionCorrectConditionsDelete', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('PromoSubscriptionCorrectConditionsUpdateModal', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('PromoSubscriptionCorrectConditionsCreate', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('PromoSubscriptionCorrectConditionsIndex', ['root', 'admin', 'reseller', 'manager']);
    $this->revokeRolesPermission('PromoSubscriptionCorrectConditionsController', ['root', 'admin', 'reseller', 'manager']);

    $this->removePermission('PromoSubscriptionCorrectConditionsDelete');
    $this->removePermission('PromoSubscriptionCorrectConditionsUpdateModal');
    $this->removePermission('PromoSubscriptionCorrectConditionsCreate');
    $this->removePermission('PromoSubscriptionCorrectConditionsIndex');
    $this->removePermission('PromoSubscriptionCorrectConditionsController');

    $this->dropTable('subscription_correct_conditions');
  }
}
