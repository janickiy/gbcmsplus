<?php

use console\components\Migration;

class m180208_133234_structure extends Migration
{
  use \rgk\utils\traits\PermissionTrait;

  const TABLE = 'loyalty_bonuses';

  public function up()
  {
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
      // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
      $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    }

    $this->createTable(self::TABLE, [
      'id' => $this->primaryKey(),
      'external_id' => $this->integer(10)->unsigned()->comment('ID записи на МП'),
      'external_invoice_id' => $this->integer(10)->unsigned()->comment('ID инвойса на MGMP (зачисленный бонус)'),
      'amount_usd' => $this->decimal(10, 2)->notNull()->unsigned()->comment('Сумма бонуса в USD'),
      'comment' => $this->text()->comment('Комментарий к бонусу (виден реселлеру)'),
      'type' => $this->string(32)->notNull()->defaultValue(''),
      'details_json' => $this->text()->notNull()->defaultValue(''),
      'decline_reason' => $this->text()->comment('Причина отклонения'),
      'status' => $this->smallInteger(1)->comment('Статус'),
      'created_at' => $this->integer(10)->unsigned()->notNull(),
      'updated_at' => $this->integer(10)->unsigned()->notNull(),
    ], $tableOptions);

    $this->createPermission('LoyaltyModule', 'Модуль программы лояльности для реселлера');
    $this->createPermission('LoyaltyBonusesController', 'Контроллер бонусов', 'LoyaltyModule', ['root', 'admin']);
    $this->createPermission('LoyaltyBonusesIndex', 'Список бонусов', 'LoyaltyBonusesController');
    $this->createPermission('LoyaltyBonusesViewModal', 'Детальный просмотр бонуса', 'LoyaltyBonusesController');
  }

  public function down()
  {
    $this->removePermission('LoyaltyBonusesViewModal');
    $this->removePermission('LoyaltyBonusesIndex');
    $this->removePermission('LoyaltyBonusesController');
    $this->removePermission('LoyaltyModule');
    $this->dropTable(self::TABLE);
  }
}
