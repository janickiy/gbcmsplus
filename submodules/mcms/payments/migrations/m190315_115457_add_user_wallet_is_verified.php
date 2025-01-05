<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190315_115457_add_user_wallet_is_verified extends Migration
{
  use PermissionTrait;

  /**
  */
  public function up()
  {
    $this->addColumn(
      'user_wallets',
      'is_verified',
      $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0)
    );
    $this->addColumn(
      'user_wallets',
      'created_at',
      $this->integer(10)->unsigned()
    );
    $this->addColumn(
      'user_wallets',
      'updated_at',
      $this->integer(10)->unsigned()
    );

    $this->createPermission(
      'PaymentsUsersVerifyWallet',
      'Верифицировать кошельки партнеров',
      'PaymentsUsersController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PaymentsUsersVerifyWallet');

    $this->dropColumn('user_wallets', 'updated_at');
    $this->dropColumn('user_wallets', 'created_at');
    $this->dropColumn('user_wallets', 'is_verified');
  }
}
