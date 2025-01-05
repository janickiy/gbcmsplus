<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181126_131429_partner_company_agreement extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->addColumn('partner_companies', 'agreement', $this->string(255));
    $this->createPermission('PaymentsPartnerCompaniesGetAgreement',
      'Получить файл соглашения', 'PaymentsPartnerCompaniesController', ['reseller']);

    $this->createPermission('PartnersPaymentsGetAgreement',
      'Получить файл соглашения', 'PartnersPaymentsController', ['partner']);
  }

  /**
  */
  public function down()
  {
    $this->dropColumn('partner_companies','agreement');
    $this->removePermission('PaymentsPartnerCompaniesGetAgreement');
    $this->removePermission('PartnersPaymentsGetAgreement');
  }
}
