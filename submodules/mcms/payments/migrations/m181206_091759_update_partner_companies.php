<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m181206_091759_update_partner_companies extends Migration
{
  /**
   *
   */
  public function up()
  {
    $this->addForeignKey(
      'fk-user_payment_settings-partner_companies',
      'user_payment_settings',
      'partner_company_id',
      'partner_companies',
      'id',
      'SET NULL'
    );

    $this->addColumn('partner_companies', 'reseller_company_id', 'MEDIUMINT(5) UNSIGNED NULL AFTER id');
    $this->addColumn('partner_companies', 'due_date_days_amount', 'MEDIUMINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER currency');
    $this->addColumn('partner_companies', 'vat', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER due_date_days_amount');
    $this->addForeignKey(
      'fk-partner_companies-companies',
      'partner_companies',
      'reseller_company_id',
      'companies',
      'id',
      'SET NULL'
    );

    $this->addColumn('user_payments', 'generated_invoice_file_positive', $this->string());
    $this->addColumn('user_payments', 'generated_invoice_file_negative', $this->string());
  }

  /**
   *
   */
  public function down()
  {
    $this->dropColumn('user_payments', 'generated_invoice_file_negative');
    $this->dropColumn('user_payments', 'generated_invoice_file_positive');

    $this->dropForeignKey(
      'fk-partner_companies-companies',
      'partner_companies'
    );
    $this->dropColumn('partner_companies', 'vat');
    $this->dropColumn('partner_companies', 'due_date_days_amount');
    $this->dropColumn('partner_companies', 'reseller_company_id');

    $this->dropForeignKey(
      'fk-user_payment_settings-partner_companies',
      'user_payment_settings'
    );
  }
}
