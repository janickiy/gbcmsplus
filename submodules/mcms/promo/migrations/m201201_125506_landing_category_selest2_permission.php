<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m201201_125506_landing_category_selest2_permission extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PromoLandingCategoriesSelect2', 'Поиск по категориям лендинга', 'PromoLandingCategoriesController', ['admin', 'root']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoLandingCategoriesSelect2');
  }
}
