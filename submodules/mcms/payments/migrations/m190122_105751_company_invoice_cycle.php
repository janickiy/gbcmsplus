<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190122_105751_company_invoice_cycle extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->addColumn('partner_companies', 'invoicing_cycle', 'TINYINT(1) UNSIGNED');

    $this->db->createCommand('
      UPDATE partner_companies pc
        INNER JOIN user_payment_settings ups ON ups.partner_company_id = pc.id
      SET pc.invoicing_cycle = ups.invoicing_cycle
      WHERE ups.invoicing_cycle IS NOT NULL;
    ')->execute();

    $this->dropColumn('user_payment_settings', 'invoicing_cycle');
  }

  /**
  */
  public function down()
  {
    $this->addColumn('user_payment_settings', 'invoicing_cycle', 'TINYINT(1) UNSIGNED');
    $this->dropColumn('partner_companies', 'invoicing_cycle');
  }
}
