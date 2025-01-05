<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190124_134033_country_limits extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->alterColumn('subscription_limits', 'operator_id', 'mediumint(5) UNSIGNED');
    $this->addColumn('subscription_limits', 'country_id', 'mediumint(5) UNSIGNED AFTER id');
    $this->execute('UPDATE subscription_limits lim INNER JOIN operators o ON o.id = lim.operator_id
SEt lim.country_id = o.country_id;');
    $this->alterColumn('subscription_limits', 'country_id', 'mediumint(5) UNSIGNED NOT NULL');
  }

  /**
  */
  public function down()
  {
    $this->dropColumn('subscription_limits', 'country_id');
    $this->alterColumn('subscription_limits', 'operator_id', 'mediumint(5) UNSIGNED NOT NULL');
  }
}
