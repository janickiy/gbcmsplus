<?php

use console\components\Migration;
use mcms\common\multilang\LangAttribute;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m191003_120019_create_offer_category extends Migration
{
  use PermissionTrait;

  /**
   */
  public function up()
  {
    $this->createTable('offer_categories', [
      'id' => $this->primaryKey(5)->unsigned(),
      'code' => $this->string(50),
      'name' => $this->string(150),
      'status' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ]);

    $this->insert('offer_categories', [
      'id' => 1,
      'code' => '1-click',
      'name' => serialize(['ru' => '1-Click', 'en' => '1-Click']),
      'status' => 1,
      'created_at' => time(),
      'updated_at' => time(),
    ]);

    $this->addColumn(
      'landings',
      'offer_category_id',
      $this->integer(5)->unsigned()->after('category_id')->defaultValue(1)
    );

    $this->addForeignKey(
      'fk-landings-offer_categories',
      'landings',
      'offer_category_id',
      'offer_categories',
      'id'
    );

    $this->createPermission('PromoOfferCategoriesController', 'Контроллер LandingOperator', 'PromoModule');
    $this->createPermission('PromoOfferCategoriesIndex', 'Удаление операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesViewModal', 'Удаление операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesCreateModal', 'Удаление операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesUpdateModal', 'Удаление операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesEnable', 'Включение операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesDisable', 'Отключение операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
    $this->createPermission('PromoOfferCategoriesDelete', 'Удаление операторов лендингов', 'PromoOfferCategoriesController', ['root', 'admin', 'reseller', 'manager']);
  }

  /**
   */
  public function down()
  {
    $this->removePermission('PromoOfferCategoriesDelete');
    $this->removePermission('PromoOfferCategoriesDisable');
    $this->removePermission('PromoOfferCategoriesEnable');
    $this->removePermission('PromoOfferCategoriesUpdateModal');
    $this->removePermission('PromoOfferCategoriesCreateModal');
    $this->removePermission('PromoOfferCategoriesViewModal');
    $this->removePermission('PromoOfferCategoriesIndex');
    $this->removePermission('PromoOfferCategoriesController');

    $this->dropForeignKey('fk-landings-offer_categories', 'landings');
    $this->dropColumn('landings', 'offer_category_id');
    $this->dropTable('offer_categories');
  }
}
