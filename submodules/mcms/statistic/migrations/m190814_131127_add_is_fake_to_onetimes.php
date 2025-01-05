<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190814_131127_add_is_fake_to_onetimes extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->addColumn('onetime_subscriptions', 'is_fake', $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0));
  }

  /**
  */
  public function down()
  {
    $this->dropColumn('onetime_subscriptions', 'is_fake');
  }
}
