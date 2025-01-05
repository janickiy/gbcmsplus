<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m181214_091814_subid_partner extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->removePermission('PartnersStatisticLabelTmp');
    $this->createPermission('PartnersStatisticSubid', 'Статистика по subid', 'PartnersStatisticController', ['partner']);
  }

  /**
  */
  public function down()
  {
    $this->createPermission('PartnersStatisticLabelTmp', 'временное решение', 'PartnersStatisticController', ['partner']);
    $this->removePermission('PartnersStatisticSubid');
  }
}
