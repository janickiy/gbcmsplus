<?php

use console\components\Migration;
use mcms\promo\models\PersonalProfit;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m190712_013515_personal_profit_add_provider extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $tableName = 'personal_profit';

    $this->addColumn($tableName, 'provider_id', 'MEDIUMINT(5) UNSIGNED NOT NULL after landing_Id');
    $this->dropIndex('personal_profit_user_operator_landing_country_index', $tableName);
    $this->dropPrimaryKey('PRIMARY', $tableName);

    $this->execute("ALTER TABLE $tableName ADD PRIMARY KEY(`user_id`, `operator_id`, `landing_id`, `provider_id`, `country_id`)");

    $this->createIndex(
      'personal_profit_user_operator_landing_provider_country_index',
      $tableName,
      ['user_id', 'operator_id', 'landing_id', 'provider_id', 'country_id'],
      true
    );
  }

  /**
   */
  public function down()
  {
    $tableName = 'personal_profit';
    $this->dropIndex('personal_profit_user_operator_landing_provider_country_index', $tableName);
    $this->dropPrimaryKey('PRIMARY', $tableName);
    $this->dropColumn($tableName, 'provider_id');
    $this->execute("ALTER TABLE $tableName ADD PRIMARY KEY(`user_id`, `operator_id`, `landing_id`, `country_id`)");
    $this->createIndex(
      'personal_profit_user_operator_landing_country_index',
      $tableName,
      ['user_id', 'operator_id', 'landing_id'],
      true
    );
  }
}
