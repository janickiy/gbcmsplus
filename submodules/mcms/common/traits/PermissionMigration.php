<?php

namespace mcms\common\traits;

use Exception;
use mcms\common\rbac\AuthItemsManager;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Rule;

/**
 * Управление набором разрешений.
 * Предоставляет API для работы с разрешениями.
 * Раннее можно было использовать свойство permissions и разрешения генерировались автоматически, но для более прозрачной
 * работы было решено отказаться от этой возможности.
 * Пишите up и down вручную используя методы createPermission и прочие...
 *
 * @deprecated Используйте rgk\utils\traits\PermissionTrait
 */
trait PermissionMigration
{
  /** @var \yii\rbac\ManagerInterface */
  public $authManager;
  /** @var string */
  public $moduleName;
  /** @var array[]
   * @deprecated
   */
  public $permissions;

  protected $roles;
  protected $rules;

  public function init()
  {
    parent::init();

    if (!$this->authManager) $this->authManager = \Yii::$app->authManager;
  }

  public function up()
  {
    if (!count($this->permissions)) return;
    foreach ($this->permissions as $controller => $rulesArray) {

      foreach ($rulesArray as $rulesArrayItem) {

        $action = ArrayHelper::getValue($rulesArrayItem, 0);
        $description = ArrayHelper::getValue($rulesArrayItem, 1);
        $roles = ArrayHelper::getValue($rulesArrayItem, 2, []);
        $rules = ArrayHelper::getValue($rulesArrayItem, 3, []);

        $permissionName = $this->getPermissionName($controller, $action);

        $permission = $this->createOrGetPermission($permissionName, $description);

        if (count($roles)) foreach ($roles as $roleName) {
          $role = $this->createOrGetRole($roleName);
          if (!$this->authManager->hasChild($role, $permission)) {
            $this->authManager->addChild($role, $permission);
          }
        }

        if (isset($rules) && count($rules) && is_array($rules)) foreach ($rules as $ruleName => $ruleClass) {
          $rule = $this->createOrGetRule($ruleClass, $ruleName);
          $rulePermission = $this->createOrGetPermission($ruleName, $rule->description, $ruleName);
          if (!$this->authManager->hasChild($permission, $rulePermission)) {
            $this->authManager->addChild($permission, $rulePermission);
          }
        }
      }
    }
  }

  public function down()
  {
    if (!count($this->permissions)) return;

    foreach ($this->permissions as $controller => $rulesArray) {
      foreach ($rulesArray as $rulesArrayItem) {

        $action = ArrayHelper::getValue($rulesArrayItem, 0, []);
//        $description = \yii\helpers\ArrayHelper::getValue($rulesArrayItem, 1);
//        $roles = \yii\helpers\ArrayHelper::getValue($rulesArrayItem, 2);
        $rules = ArrayHelper::getValue($rulesArrayItem, 3, []);

        $permissionName = $this->getPermissionName($controller, $action);
        if ($permission = $this->authManager->getPermission($permissionName)) {
          $this->authManager->remove($permission);
        }

        if (count($rules)) foreach ($rules as $ruleName) {
          if ($rule = $this->authManager->getRule($ruleName)) {
            $this->authManager->remove($rule);
          }
        }
      }
    }
  }

  /**
   * Получить роль.
   * Несуществующая роль будет создана автоматически
   * @param string $roleName
   * @return mixed|null|\yii\rbac\Role
   * @throws Exception
   */
  private function createOrGetRole($roleName)
  {
    if ($role = ArrayHelper::getValue($this->roles, $roleName)) {
      return $role;
    }

    if ($role = $this->authManager->getRole($roleName)) {
      $this->roles[$roleName] = $role;
      return $role;
    }

    throw new Exception('Invalid role name');
  }

  /**
   * Получить правило.
   * Несуществующее правило будет создано автоматически
   * @param string $ruleClass
   * @param string $ruleName
   * @return mixed|null|Rule
   * @throws Exception
   * @deprecated
   * @see createRule()
   */
  private function createOrGetRule($ruleClass, $ruleName)
  {
    if ($rule = ArrayHelper::getValue($this->rules, $ruleName)) {
      return $rule;
    }

    $ruleClass = new $ruleClass;
    $ruleName = $ruleClass->name;
    if ($rule = $this->authManager->getRule($ruleName)) {
      $this->rules[$ruleName] = $rule;
      return $rule;
    }

    if ($ruleClass instanceof Rule) {
      $this->authManager->add($ruleClass);
      $this->rules[$ruleName] = $ruleClass;

      return $ruleClass;
    }

    throw new Exception('Invalid rule name');
  }

  /**
   * Создать правило
   * @param string $ruleClass
   * @return Rule
   * @throws Exception
   */
  public function createRule($ruleClass)
  {
    $rule = new $ruleClass;
    if ($this->authManager->getRule($rule->name)) {
      throw new Exception('Rule ' . $rule->name . ' already exists');
    }

    if ($rule instanceof Rule) {
      if (!$this->authManager->add($rule)) {
        throw new Exception('Failed to add rule');
      }

      return $rule;
    }

    throw new Exception('Invalid rule name');
  }

  /**
   * Создать или получить разрешение.
   * TRICKY Несуществующее разрешение будет создано автоматически.
   * @param $permissionName
   * @param $permissionDescription
   * @param null $ruleName
   * @return null|Permission
   * @deprecated Метод больше не используется для более прозрачной работы с разрешениями @see createPermission() @see removePermission()
   */
  public function createOrGetPermission($permissionName, $permissionDescription, $ruleName = null)
  {
    $permission = $this->authManager->getPermission($permissionName);
    if (!$permission) {
      $permission = $this->authManager->createPermission($permissionName);
      $permission->description = $permissionDescription;
      $permission->ruleName = $ruleName;

      $this->authManager->add($permission);
    }

    return $permission;
  }

  /**
   * Создать разрешение
   * @param string $name Название разрешение
   * @param string $description Описание
   * @param string $parentName Название родительского разрешения @see addChildPermission()
   * @param array $roles Роли, к которым нужно привязать разрешение @see assignRolesPermission()
   * @param string $ruleName Название правила
   * @return Permission
   * @throws \yii\base\Exception
   */
  public function createPermission($name, $description = null, $parentName = null, array $roles = [], $ruleName = null)
  {
    if ($this->authManager->getPermission($name)) {
      throw new \yii\base\Exception('Permission ' . $name . ' already exists');
    }

    // Создание разрешения
    $permission = $this->authManager->createPermission($name);
    $permission->description = $description;
    $permission->ruleName = $ruleName;
    if (!$this->authManager->add($permission)) throw new \yii\base\Exception('Cant add permission ' . $name);

    // Указание родительского разрешения
    if ($parentName) {
      $this->addChildPermission($parentName, $name);
    }

    // Привязка к ролям
    if ($roles) {
      $this->assignRolesPermission($name, $roles);
    }

    return $permission;
  }

  /**
   * Добавить дочернее разрешение
   * @param string $parentName
   * @param string $childName
   * @throws Exception
   */
  public function addChildPermission($parentName, $childName)
  {
    $parent = $this->getPermission($parentName);
    $child = $this->getPermission($childName);

    if ((new Query())
      ->from(['child' => 'auth_item_child', 'item' => 'auth_item'])
      ->where(
        "child.child = :permission AND item.name = child.parent AND item.type = :type",
        [
          ':permission' => $childName,
          ':type' => Item::TYPE_PERMISSION,
        ])
      ->exists()
    ) {
      throw new Exception('Permission ' . $childName . ' already has parent (new parent ' . $parentName . ')');
    }

    if (!$this->authManager->hasChild($parent, $child) && !$this->authManager->addChild($parent, $child)) {
      throw new Exception('Cant add child permission ' . $parentName . ' -> ' . $childName);
    }
  }

  /**
   * Открепить дочернее право.
   * TRICKY Будет удалена только связь между правами, а не само дочернее право
   * @param string $parentName Родительское право
   * @param string $childName Дочернее право для открепления
   * @throws Exception
   */
  public function removeChildPermission($parentName, $childName)
  {
    $parent = $this->getPermission($parentName);
    $child = $this->getPermission($childName);

    if ($this->authManager->hasChild($parent, $child) && !$this->authManager->removeChild($parent, $child)) {
      throw new Exception('Cant remove child permission ' . $parentName . ' -> ' . $childName);
    }
  }

  /**
   * Удалить разрешение
   * @param string $name
   * @param bool $error
   * @throws Exception
   */
  public function removePermission($name, $error = false)
  {
    $permission = $this->authManager->getPermission($name);
    if (!$permission) {
      if ($error) throw new Exception('Permission is not exist: ' . $name . '. Cant delete it.');
      return;
    }

    if (!$this->authManager->remove($permission) && $error) throw new Exception('Permission ' . $name . ' delete fail');
  }

  /**
   * Удалить правило
   * @param string $name Название правила
   * @throws Exception
   */
  public function removeRule($name)
  {
    $permission = $this->authManager->getRule($name);
    if (!$permission) throw new Exception('Rule is not exist: ' . $name . '. Cant delete it.');
    if (!$this->authManager->remove($permission)) throw new Exception('Rule ' . $name . ' delete fail');
  }

  /**
   * Изменить название разрешения
   * TRICKY При изменении названия разрешения нужно учитывать, что название нужно изменить не только в БД, а и в коде
   * @param string $oldName Старое название разрешения
   * @param string $newName Новое название разрешения
   * @throws \yii\base\Exception
   */
  public function renamePermission($oldName, $newName)
  {
    $this->getPermission($oldName);
    if (\Yii::$app->db->createCommand()->update('auth_item', ['name' => $newName], ['name' => $oldName])->execute() === false) {
      throw new \yii\base\Exception('Cant rename permission (' . $oldName . ' => ' . $newName . ')');
    }
  }

  /**
   * Изменить описание разрешения
   * @param string $name Разрешение, описание которого нужно изменить
   * @param string $description Новое описание
   * @throws \yii\base\Exception
   */
  public function updatePermissionDescription($name, $description)
  {
    $this->getPermission($name);
    if (\Yii::$app->db->createCommand()->update('auth_item', ['description' => $description], ['name' => $name])->execute() === false) {
      throw new \yii\base\Exception('Cant update permission description (permission ' . $name . ')');
    }
  }

  /**
   * Изменить родителя разрешения
   * @param string $name Название разрешения
   * @param string $oldParent Название текущего разрешения-родителя
   * @param string $newParent Название нового разрешения-родителя
   */
  public function movePermission($name, $oldParent, $newParent)
  {
    $this->removeChildPermission($oldParent, $name);
    $this->addChildPermission($newParent, $name);
  }

  /**
   * Получить название разрешения для экшена
   * @param string $controller Контроллер
   * @param string $action Экшен
   * @return string
   */
  private function getPermissionName($controller, $action)
  {
    return \yii\helpers\BaseInflector::camelize(sprintf('%s_%s_%s', $this->moduleName, $controller, $action));
  }

  /**
   * Назначает массиву ролей определенный пермишен
   *
   * @param string|Permission $permissionName
   * @param array $roleNames
   * @throws Exception
   */
  public function assignRolesPermission($permissionName, $roleNames)
  {
    if (!$permission = $this->authManager->getPermission($permissionName)) {
      throw new Exception('Invalid permission name ' . $permissionName);
    }

    if (is_string($roleNames)) $roleNames = [$roleNames];

    foreach ($roleNames as $roleName) {
      if (!$role = $this->authManager->getRole($roleName)) {
        throw new Exception('Invalid role name ' . $roleName);
      }

      if ($permission && !$this->authManager->hasChild($role, $permission)) {
        $this->authManager->addChild($role, $permission);
      }
    }
  }

  /**
   * Удалить у ролей определенное разрешение
   *
   * @param $permissionName
   * @param array $roleNames
   * @throws Exception
   */
  public function revokeRolesPermission($permissionName, $roleNames)
  {
    if (!$permission = $this->authManager->getPermission($permissionName)) {
      throw new Exception('Invalid permission name ' . $permissionName);
    }

    if (is_string($roleNames)) $roleNames = [$roleNames];

    foreach ($roleNames as $roleName) {
      if (!$role = $this->authManager->getRole($roleName)) {
        throw new Exception('Invalid role name ' . $roleName);
      }

      if ($permission && $this->authManager->hasChild($role, $permission)) {
        $this->authManager->removeChild($role, $permission);
      }
    }
  }

  /**
   * Получить разрешение по названию
   * @param string $name Название разрешения
   * @param bool $required Обязательное наличие разрешения
   * @return null|Permission
   * @throws Exception
   */
  public function getPermission($name, $required = true)
  {
    $permission = $this->authManager->getPermission($name);

    if ($required && !$permission) {
      throw new Exception('Permission ' . $name . ' not exists');
    }

    return $permission;
  }

  /**
   * Получить название разрешения для роли
   * @param string $role
   * @return string
   */
  public function getRolePermissionName($role)
  {
    return AuthItemsManager::ROLE_PERMISSION_PREFIX . ucfirst($role);
  }
}
