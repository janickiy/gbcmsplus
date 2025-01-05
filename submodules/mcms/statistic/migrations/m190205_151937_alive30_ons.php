<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190205_151937_alive30_ons extends Migration
{
  use PermissionTrait;

  const TABLE = 'alive30_ons_day_group';
  /**
  */
  public function up()
  {
    $this->execute('create table '. self::TABLE . '
      (
        date                            date                                      not null,
        source_id                       mediumint(5) unsigned default \'0\'       not null,
        landing_id                      mediumint(5) unsigned default \'0\'       not null,
        operator_id                     mediumint(5) unsigned default \'0\'       not null,
        platform_id                     mediumint(5) unsigned default \'0\'       not null,
        landing_pay_type_id             tinyint(1) unsigned default \'0\'         not null,
        is_fake                         tinyint(1) unsigned default \'0\'         not null,
        currency_id                     tinyint(1) unsigned                       not null,
        user_id                         mediumint(5) unsigned default \'0\'       not null,
        stream_id                       mediumint(5) unsigned default \'0\'       not null,
        country_id                      mediumint(5) unsigned default \'0\'       not null,
        provider_id                     mediumint(5) unsigned default \'0\'       not null,
        revshare_alive30_ons            mediumint(5) unsigned default \'0\'       not null,
        to_buyout_alive30_ons           mediumint(5) unsigned default \'0\'       not null,
        primary key (date, source_id, landing_id, operator_id, platform_id, landing_pay_type_id, is_fake)
      );'
    );
  }

  /**
   */
  public function down()
  {
    $this->dropTable(self::TABLE);
  }
}
