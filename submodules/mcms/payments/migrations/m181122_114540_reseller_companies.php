<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181122_114540_reseller_companies extends Migration
{
  use PermissionTrait;

  /**
   * @throws \yii\base\Exception
   */
  public function up()
  {
    $this->createTable('companies', [
      'id' => 'MEDIUMINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
      'name' => $this->string(255),
      'address' => $this->string(255),
      'country' => $this->string(255),
      'tax_code' => $this->string(50),
      'logo' => $this->string(255),
      'created_at' => 'INT(10) UNSIGNED NOT NULL',
      'updated_at' => 'INT(10) UNSIGNED NOT NULL',
    ]);

    $this->createPermission('PaymentsCompaniesController', 'Контроллер компаний', 'PaymentsModule', ['root', 'admin']);
    $this->createPermission('PaymentsCompaniesIndex', 'Список компаний', 'PaymentsCompaniesController', ['reseller']);
    $this->createPermission('PaymentsCompaniesUpdateModal', 'Редактировать компанию', 'PaymentsCompaniesController', ['reseller']);
    $this->createPermission('PaymentsCompaniesViewModal', 'Просмотр компании', 'PaymentsCompaniesController', ['reseller']);
    $this->createPermission('PaymentsCompaniesCreate', 'Добавить компанию', 'PaymentsCompaniesController', ['reseller']);
    $this->createPermission('PaymentsCompaniesDelete', 'Удалить компанию', 'PaymentsCompaniesController', ['reseller']);
    $this->createPermission('PaymentsCompaniesGetLogo', 'Просмотр лого', 'PaymentsCompaniesController', ['reseller']);
  }

  /**
   * @throws Exception
   */
  public function down()
  {
    $this->dropTable('companies');

    $this->removePermission('PaymentsCompaniesController');
    $this->removePermission('PaymentsCompaniesIndex');
    $this->removePermission('PaymentsCompaniesUpdateModal');
    $this->removePermission('PaymentsCompaniesViewModal');
    $this->removePermission('PaymentsCompaniesCreate');
    $this->removePermission('PaymentsCompaniesDelete');
    $this->removePermission('PaymentsCompaniesGetLogo');
  }
}
