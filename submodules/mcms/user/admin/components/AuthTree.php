<?php

namespace mcms\user\admin\components;

use mcms\common\helpers\ArrayHelper;
use mcms\user\models\Role;
use yii\base\Object;
use yii\db\Expression;
use yii\db\Query;
use yii\rbac\Item;

/**
 * Class AuthTree
 * @package mcms\user\admin\components
 */
class AuthTree extends Object
{

  const IS_NOT_ASSIGNED = 0;
  const IS_INHERITED = 2;
  const IS_ASSIGNED = 1;
  const ICON_ROLE = 'fa fa-registered';

  private $_items;
  private $_assigns;
  private $_roles;
  private $_relatedRoles;

  /**
   * @return array
   */
  public function getTree()
  {
    $items = $this->getItems();
    return $this->buildTree($items);
  }


  /**
   * @return array
   */
  private function getItems()
  {
    if ($this->_items) return $this->_items;

    return $this->_items = (new Query())
      ->select([
        'i.name',
        'i.description',
        'parent' => new Expression('GROUP_CONCAT(p.name)'),
        'i.type'
      ])
      ->from(['i' => 'auth_item'])
      ->leftJoin(['ref' => 'auth_item_child'], 'ref.child = i.name')
      ->leftJoin(
        ['p' => 'auth_item'],
        'p.name = ref.parent AND p.type = :typePermission',
        ['typePermission' => Item::TYPE_PERMISSION]
      )
      ->where(['i.type' => [Item::TYPE_ROLE, Item::TYPE_PERMISSION]])
      ->groupBy('i.name')
      ->orderBy('i.type')
      ->all();
  }

  /**
   * @param array $elements
   * @param string $parentName
   * @param array $inherited
   * @return array
   */
  private function buildTree(array &$elements, $parentName = null, $inherited = [])
  {
    $branch = [];


    foreach ($elements as &$element) {

      if ($element['parent'] != $parentName) continue;

      $rolesHasPermission = $this->getRolesHasPermission($element);

      $allowedRolesPermissions = $this->mergeInherit($inherited, $rolesHasPermission);

      $children = $this->buildTree($elements, $element['name'], $allowedRolesPermissions);

      if ($children) {
        $element['children'] = $children;
      }

      // определяем какие пермишены унаследованы от родительских ролей (если у роли есть родитель)
      $inheritedFromRoles = $this->getInheritedFromRoles($allowedRolesPermissions);

      $branch[] = $this->formatElement($element, $rolesHasPermission, $this->mergeInherit($inherited, $inheritedFromRoles));

      unset($element);
    }
    return $branch;
  }

  /**
   * @param array $element
   * @param $rolesHasPermission
   * @param $inherited
   * @return array
   */
  private function formatElement(array $element, $rolesHasPermission, $inherited)
  {

    $el = [
      'title' => $element['description'] ?: $element['name'],
      'key' => $element['name'],
      'tooltip' => $element['name'],
      'icon' => $element['type'] == Item::TYPE_ROLE ? self::ICON_ROLE : null
    ];

    if (isset($element['children'])) $el['children'] = $element['children'];


    $el['assigns'] = $this->getElementAssigns($rolesHasPermission, $inherited);
    return $el;
  }

  /**
   * @return array
   */
  public function getRoles()
  {
    return $this->_roles = $this->_roles ?: Role::getDropdownListData();
  }


  /**
   * @return array
   */
  private function getAssigns()
  {
    if ($this->_assigns) return $this->_assigns;

    return $this->_assigns = (new Query())
      ->select([
        'child',
        'parent'
      ])
      ->from(['ref' => 'auth_item_child'])
      ->innerJoin(['p' => 'auth_item'], 'p.name = ref.parent')
      ->where(['p.type' => Item::TYPE_ROLE])
      ->indexBy(function ($row) {
        return $row['child'] . '.' . $row['parent'];
      })
      ->all();
  }

  /**
   * @param $element
   * @return array
   */
  private function getRolesHasPermission($element)
  {
    $assigns = [];
    foreach ($this->getRoles() as $role) {
      $assigns[$role] = isset($this->getAssigns()[$element['name'] . '.' . $role]);
    }
    return $assigns;
  }

  /**
   * @param array $array1
   * @param array $array2
   * @return array вернет смерженный массив
   */
  private function mergeInherit(array $array1, array $array2)
  {
    return array_merge(array_filter($array1), array_filter($array2));
  }




  /**
   * @param $rolesHasPermission
   * @param $inherited
   * @return array
   */
  private function getElementAssigns($rolesHasPermission, $inherited)
  {
    $assigns = [];
    foreach ($this->getRoles() as $role) {
      $isAssignInherited = ArrayHelper::getValue($inherited, $role, false);

      $isAssigned = ArrayHelper::getValue($rolesHasPermission, $role, false);

      if (!$isAssigned && !$isAssignInherited) {
        $assigns[$role] = self::IS_NOT_ASSIGNED;
        continue;
      }
      if ($isAssignInherited) {
        $assigns[$role] = self::IS_INHERITED;
        continue;
      }
      $assigns[$role] = self::IS_ASSIGNED;
    }

    return $assigns;
  }

  /**
   * @param array $allowedRolesPermissions
   * @return array
   */
  private function getInheritedFromRoles(array $allowedRolesPermissions)
  {
    $relatedRoles = $this->getRelatedRoles();

    if (empty($relatedRoles)) return [];

    $inherited = [];

    foreach ($relatedRoles as $role => $inheritRolesStr) {
      $inheritRoles = explode(',', $inheritRolesStr);

      // для каждой унаследованной роли проверяем включен ли пермишен
      foreach ($inheritRoles as $inheritRole) {
        if (!ArrayHelper::getValue($allowedRolesPermissions, $inheritRole)) continue;
        $inherited[$role] = true;
        break;
      }
    }

    return $inherited;
  }

  /**
   * @return array вида ['admin_rel' => 'admin,reseller,investor']
   */
  private function getRelatedRoles()
  {
    if ($this->_relatedRoles) return $this->_relatedRoles;

    return $this->_relatedRoles = (new Query())
      ->select([
        'child' => new Expression('GROUP_CONCAT(ref.child)'),
      ])
      ->from(['ref' => 'auth_item_child'])
      ->innerJoin(['parent' => 'auth_item'], 'parent.name = ref.parent')
      ->innerJoin(['child' => 'auth_item'], 'child.name = ref.child')
      ->where([
        'parent.type' => Item::TYPE_ROLE,
        'child.type' => Item::TYPE_ROLE,
      ])
      ->indexBy('parent')
      ->column();
  }
}