<?php

namespace mcms\common\traits;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;

/**
 * @deprecated
 * @see PermissionMigration
 */
trait PermissionGroupMigration
{
  public $groupPermissionName;
  public $groupPermissionDescription;
  public $groupPermissionDefaultRole = [];
  /**
   * ['PagesCategoriesController' => [
   * 'description' => 'Pages Categories Controller',
   * 'roles' => ['admin', 'root'],
   * 'permissions' => [
   * ['PagesCategoriesCreate', ['admin', 'partner']],
   * ['PagesCategoriesDelete'],
   * ['PagesCategoriesIndex'],
   * ['PagesCategoriesPropDelete'],
   * ['PagesCategoriesPropEntityDelete'],
   * ['PagesCategoriesPropEntityModal'],
   * ['PagesCategoriesPropModal'],
   * ]
   * ],]
   * @var array
   */
  public $groupPermissionControllers = [];

  public function up()
  {
    foreach ($this->groupPermissionControllers as $controllerName => $controllerData) {
      $controllerPermission = $this->createOrGetPermission($controllerName, $controllerData['description']);

      if ($roles = ArrayHelper::getValue($controllerData, 'roles')) {
        if ($controllerPermission && count($roles)) foreach ($roles as $roleName) {
          $role = $this->getRole($roleName);
          if (!Yii::$app->authManager->hasChild($role, $controllerPermission)) {
            Yii::$app->authManager->addChild($role, $controllerPermission);
          }
        }
      }

      foreach ($controllerData['permissions'] as $childPermissionParams) {

        $permissionName = ArrayHelper::getValue($childPermissionParams, 0);
        if (!$permissionName) continue;
        $permissionDescription = ArrayHelper::getValue(
          $childPermissionParams,
          2,
          'Can user view ' . BaseInflector::camel2words($permissionName, false)
        );

        $roles = ArrayHelper::getValue($childPermissionParams, 1, $this->groupPermissionDefaultRole);

        $childPermission = $this->createOrGetPermission($permissionName, $permissionDescription);

        if ($childPermission && !Yii::$app->authManager->hasChild($controllerPermission, $childPermission)) {
          Yii::$app->authManager->addChild($controllerPermission, $childPermission);
        }

        if ($childPermission && count($roles)) foreach ($roles as $roleName) {
          $role = $this->getRole($roleName);
          if (!Yii::$app->authManager->hasChild($role, $childPermission)) {
            Yii::$app->authManager->addChild($role, $childPermission);
          }
        }
      }
      $groupPermission = $this->createOrGetPermission($this->groupPermissionName, $this->groupPermissionDescription);
      if (!Yii::$app->authManager->hasChild($groupPermission, $controllerPermission)) {
        Yii::$app->authManager->addChild($groupPermission, $controllerPermission);
      }
    }
  }

  public function down()
  {
//    if ($permission = Yii::$app->authManager->getPermission($this->groupPermissionName)) {
//      Yii::$app->authManager->remove($permission);
//    }

    foreach ($this->groupPermissionControllers as $controllerName => $controllerData) {
      if ($controllerPermission = Yii::$app->authManager->getPermission($controllerName)) {
        Yii::$app->authManager->remove($controllerPermission);
      }

      foreach ($controllerData['permissions'] as $childPermissionParams) {
        if ($permissionName = Yii::$app->authManager->getPermission(ArrayHelper::getValue($childPermissionParams, 0))) {
          Yii::$app->authManager->remove($permissionName);
        }
      }
    }
  }

  public function createOrGetPermission($permissionName, $permissionDescription)
  {
    $permission = Yii::$app->authManager->getPermission($permissionName);
    if (!$permission) {
      $permission = Yii::$app->authManager->createPermission($permissionName);
      $permission->description = $permissionDescription;
      Yii::$app->authManager->add($permission);
    }
    return $permission;
  }

  private function getRole($roleName)
  {
    return Yii::$app->authManager->getRole($roleName);
  }
}