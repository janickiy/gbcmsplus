<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;
use yii\db\Query;
use yii\rbac\Item;

/**
*/
class m181123_140708_actionautopay extends Migration
{
  use PermissionTrait;
  /**
  */
  public function up()
  {
    $permission = $this->getPermission('PaymentsAutoPayoutRule');

    $query = (new Query)
      ->from($this->authManager->itemTable)
      ->where(['type' => Item::TYPE_ROLE]);

    $roles = [];
    foreach ($query->all($this->db) as $row) {
      $role = $this->authManager->getRole($row['name']);
      if ($this->authManager->hasChild($role, $permission)) {
        $roles[] = $row['name'];
      }
    }

    $this->createPermission('PaymentsPaymentsProcessAuto', 'Выполнение автовыплаты', 'PaymentsPaymentsController', $roles);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PaymentsPaymentsProcessAuto');
  }
}
