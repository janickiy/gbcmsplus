<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190401_085626_postback_data_test extends Migration
{
  const TABLE = 'postback_data_test';

  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createTable(self::TABLE, [
      'id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
      'provider_id' => 'MEDIUMINT(5) UNSIGNED',
      'requestData' => 'MEDIUMTEXT',
      'responseData' => 'MEDIUMTEXT',
      'time' => 'INT UNSIGNED NOT NULL',
      'status' => 'tinyint UNSIGNED NOT NULL',
    ]);

    $this->createPermission('StatisticPostbackDataTestController', 'Контроллер PostbackDataTest', 'StatisticModule');
    $this->createPermission('StatisticPostbackDataTestIndex', 'Просмотр тестов постбеков', 'StatisticPostbackDataTestController', ['admin', 'reseller', 'root']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('StatisticPostbackDataTestIndex');
    $this->removePermission('StatisticPostbackDataTestController');
    $this->dropTable(self::TABLE);
  }
}
