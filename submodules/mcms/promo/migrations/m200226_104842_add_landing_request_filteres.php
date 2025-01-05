<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m200226_104842_add_landing_request_filteres extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createTable('landing_request_filters', [
      'id' => $this->primaryKey(5)->unsigned(),
      'landing_id' => 'MEDIUMINT(5) UNSIGNED NOT NULL',
      'code' => $this->string()->notNull(),
      'is_active' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
      'created_at' => $this->integer(10)->notNull()->unsigned(),
      'updated_at' => $this->integer(10)->notNull()->unsigned(),
    ]);

    $this->addForeignKey(
      'fk-landing_request_filters-landings',
      'landing_request_filters',
      'landing_id',
      'landings',
      'id'
    );

    $this->createIndex(
      'idx-landing_id-code',
      'landing_request_filters',
      ['landing_id', 'code'],
      true
    );

    $this->createPermission(
      'PromoLandingRequestFiltersController',
      'Контроллер LandingRequestFilter',
      'PromoModule'
    );

    $this->createPermission(
      'PromoLandingRequestFiltersIndex',
      'Просмотр request фильтров лендингов',
      'PromoLandingRequestFiltersController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoLandingRequestFiltersCreateModal',
      'Создание request фильтров лендингов',
      'PromoLandingRequestFiltersController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoLandingRequestFiltersUpdateModal',
      'Редактирование request фильтров лендингов',
      'PromoLandingRequestFiltersController',
      ['root', 'admin', 'reseller', 'manager']
    );

    $this->createPermission(
      'PromoLandingRequestFiltersDelete',
      'Удаление request фильтров лендингов',
      'PromoLandingRequestFiltersController',
      ['root', 'admin', 'reseller', 'manager']
    );
  }

  /**
   */
  public function down()
  {
    $this->removePermission('PromoLandingRequestFiltersDelete');
    $this->removePermission('PromoLandingRequestFiltersUpdateModal');
    $this->removePermission('PromoLandingRequestFiltersCreateModal');
    $this->removePermission('PromoLandingRequestFiltersIndex');
    $this->removePermission('PromoLandingRequestFiltersController');

    $this->dropForeignKey('fk-landing_request_filters-landings', 'landing_request_filters');
    $this->dropTable('landing_request_filters');
  }
}
