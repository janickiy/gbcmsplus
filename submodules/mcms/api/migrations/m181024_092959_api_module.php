<?php

use console\components\Migration;

/**
 * Class m181024_092959_api_module
 */
class m181024_092959_api_module extends Migration
{
  const TABLE = 'modules';

  /**
   * @inheritdoc
   */
  public function up()
  {
    $time = time();

    $this->insert(self::TABLE, [
      'module_id' => 'api',
      'name' => 'api.main.module_name',
      'created_at' => $time,
      'updated_at' => $time,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function down()
  {
    $this->delete(self::TABLE, ['module_id' => 'api']);
  }
}
