<?php

use console\components\Migration;
use mcms\common\helpers\Console;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181024_112331_facts extends Migration
{
  use PermissionTrait;

  /**
  */
  public function up()
  {
    if (!Console::confirm('Для выполнения миграции необходим настроенный MariaDB ColumnStore. Продолжаем?', false)) {
      return true;
    }

    $this->db = 'dbCs';

    $this->execute('
    CREATE TABLE facts
      (
        id                    bigint(10) UNSIGNED                NOT NULL,
        type                  tinyint UNSIGNED                   NOT NULL,
        hit_id                bigint(10) UNSIGNED                NOT NULL,
        is_unique             tinyint UNSIGNED DEFAULT 0         NOT NULL,
        is_tb                 tinyint UNSIGNED DEFAULT 0         NOT NULL,
        time                  int UNSIGNED                       NOT NULL,
        date                  date                               NOT NULL,
        hour                  tinyint UNSIGNED                   NOT NULL,
        operator_id           smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        country_id            smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        landing_id            smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        provider_id           smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        source_id             smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        source_type           tinyint UNSIGNED DEFAULT 2         NOT NULL,
        user_id               smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        stream_id             smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        platform_id           smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        landing_pay_type_id   tinyint UNSIGNED DEFAULT 0         NOT NULL,
        traffic_type          tinyint UNSIGNED DEFAULT 0,
        ip                    bigint,
        referer               varchar(512),
        user_agent            varchar(512),
        subid1                varchar(512),
        subid2                varchar(512),
        subid1_hash           char(8),
        subid2_hash           char(8),
        description           varchar(512),
        trans_id              varchar(64),
        sub_is_fake           tinyint UNSIGNED,
        res_rub               decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        res_usd               decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        res_eur               decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        partner_rub           decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        partner_usd           decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        partner_eur           decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        cpa_price_rub         decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        cpa_price_usd         decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        cpa_price_eur         decimal(9, 5) UNSIGNED DEFAULT 0   NOT NULL,
        is_visible_to_partner tinyint UNSIGNED DEFAULT 1         NOT NULL,
        manager_id            smallint(6) UNSIGNED DEFAULT 0     NOT NULL,
        category_id           smallint(6) UNSIGNED DEFAULT 0     NOT NULL
      )
        ENGINE = columnstore
        DEFAULT CHARACTER SET = latin1;
    ');
  }

  /**
  */
  public function down()
  {
    $this->db = 'dbCs';
    $this->dropTable('facts');
  }
}
