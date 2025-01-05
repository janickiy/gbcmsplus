<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181121_111340_companies extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {

    $this->createTable('partner_companies', [
      'id' => 'MEDIUMINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
      'name' => $this->string(255),
      'address' => $this->string(255),
      'country' => $this->string(255),
      'tax_code' => $this->string(50),
      'bank_entity' => $this->string(255),
      'bank_account' => $this->text(),
      'swift_code' => $this->string(50),
      'currency' => $this->string(50),
      'created_at' => 'INT(10) UNSIGNED NOT NULL',
      'updated_at' => 'INT(10) UNSIGNED NOT NULL',
    ]);

    $this->addColumn('user_payment_settings', 'partner_company_id', 'MEDIUMINT(5) UNSIGNED NULL');

    $this->createPermission('PartnersPaymentsCompany',
      'Просмотр компании', 'PartnersPaymentsController', ['partner']);

    $this->createPermission('PaymentsPartnerCompaniesController',
      'Контроллер компаний партнеров', 'PaymentsModule', ['root', 'admin']);

    $this->createPermission('PaymentsPartnerCompaniesIndex',
      'Список компаний партнеров', 'PaymentsPartnerCompaniesController', ['reseller']);
    $this->createPermission('PaymentsPartnerCompaniesUpdateModal',
      'Редактировать компанию партнера', 'PaymentsPartnerCompaniesController', ['reseller']);
    $this->createPermission('PaymentsPartnerCompaniesViewModal',
      'Посмотреть компанию партнера', 'PaymentsPartnerCompaniesController', ['reseller']);
    $this->createPermission('PaymentsPartnerCompaniesCreate',
      'Добавить компанию партнера', 'PaymentsPartnerCompaniesController', ['reseller']);
    $this->createPermission('PaymentsPartnerCompaniesDelete', 'Удалить компанию партнера', 'PaymentsPartnerCompaniesController', ['reseller']);

  }

  /**
  */
  public function down()
  {
    $this->dropTable('partner_companies');
    $this->dropColumn('user_payment_settings', 'partner_company_id');

    $this->removePermission('PartnersPaymentsCompany');
    $this->removePermission('PaymentsPartnerCompaniesController');
    $this->removePermission('PaymentsPartnerCompaniesIndex');
    $this->removePermission('PaymentsPartnerCompaniesUpdateModal');
    $this->removePermission('PaymentsPartnerCompaniesViewModal');
    $this->removePermission('PaymentsPartnerCompaniesCreate');
    $this->removePermission('PaymentsPartnerCompaniesDelete');
  }
}
