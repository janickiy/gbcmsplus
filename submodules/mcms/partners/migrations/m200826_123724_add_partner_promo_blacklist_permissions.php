<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m200826_123724_add_partner_promo_blacklist_permissions extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $this->createPermission('PartnersBlackListController', 'Контроллер BlackListController', 'PartnersModule');
    $this->createPermission('PartnersBlackListInfPay', 'Просмотр блеклиста доменов провайдера InformPay', 'PartnersBlackListController', ['partner']);
  }

  /**
  */
  public function down()
  {
    $this->revokeRolesPermission('PartnersBlackListInfPay', ['partner']);
    $this->removeChildPermission('PartnersBlackListController', 'PartnersBlackListInfPay');
    $this->removePermission('PartnersBlackListInfPay');
    $this->removeChildPermission('PartnersModule', 'PartnersBlackListController');
    $this->removePermission('PartnersBlackListController');
  }
}
