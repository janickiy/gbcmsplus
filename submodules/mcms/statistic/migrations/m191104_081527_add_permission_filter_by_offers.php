<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m191104_081527_add_permission_filter_by_offers extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission(
      'StatisticFilterByOfferCategories',
      'Просмотр фильтра по категориям офферов',
      'StatisticFilter',
      ['root', 'admin', 'reseller']
    );
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('StatisticFilterByOfferCategories', ['root', 'admin', 'reseller']);
    $this->removePermission('StatisticFilterByOfferCategories');
  }
}
