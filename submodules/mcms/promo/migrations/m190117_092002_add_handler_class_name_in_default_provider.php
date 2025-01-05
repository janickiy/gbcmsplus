<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190117_092002_add_handler_class_name_in_default_provider extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->db->createCommand()->update('providers', [
      'handler_class_name' => 'Default'
    ], [
      'handler_class_name' => '',
    ])->execute();
  }

  /**
  */
  public function down()
  {
    $this->db->createCommand()->update('providers', [
      'handler_class_name' => ''
    ], [
      'handler_class_name' => 'Default',
    ])->execute();
  }
}
