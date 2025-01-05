<?php

namespace mcms\common\rbac;

use Yii;
use mcms\common\event\RbacAssignRevoke;

class DbManager extends \yii\rbac\DbManager
{
    const EVENT_ASSIGN = 'rbacAssign';
    const EVENT_REVOKE = 'rbacRevoke';
    const EVENT_ADD_CHILD = 'rbacAddChild';
    const EVENT_REMOVE_CHILD = 'rbacRemoveChild';
    const EVENT_REMOVE_CHILDREN = 'rbacRemoveChildren';
    const EVENT_REVOKE_ALL = 'rbacRevokeAll';
    const EVENT_REMOVE_ALL = 'rbacRemoveAll';
    const EVENT_REMOVE_ALL_PERMISSIONS = 'rbacRemoveAllPermissions';
    const EVENT_REMOVE_ALL_ROLES = 'rbacRemoveAllRoles';
    const EVENT_REMOVE_ALL_ITEMS = 'rbacRemoveAllItems';
    const EVENT_REMOVE_ALL_RULES = 'rbacRemoveAllRules';
    const EVENT_REMOVE_ALL_ASSIGNMENTS = 'rbacRemoveAllAssignments';
    const CACHE_KEY_ASSIGNMENTS = 'assignments.';
    const CACHE_KEY_ROLES_BY_USER = 'roles_by_user.';
    const CACHE_KEY_PERMISSIONS_BY_USER = 'permissions_by_user.';
    const CACHE_KEY_PERMISSIONS_BY_ROLE = 'permissions_by_role.';

    private $_cache = [];

    /**
     * @inheritdoc
     */
    public function assign($role, $userId)
    {
        $result = parent::assign($role, $userId);
        $this->triggerRbacEvent(self::EVENT_ASSIGN, $userId, $role);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function revoke($role, $userId)
    {
        $result = parent::revoke($role, $userId);
        $this->triggerRbacEvent(self::EVENT_REVOKE, $userId, $role);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function addChild($parent, $child)
    {
        $result = parent::addChild($parent, $child);
        $this->triggerRbacEvent(self::EVENT_ADD_CHILD, $parent, $child);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeChild($parent, $child)
    {
        $result = parent::removeChild($parent, $child);
        $this->triggerRbacEvent(self::EVENT_REMOVE_CHILD, $parent, $child);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeChildren($parent)
    {
        $result = parent::removeChildren($parent);
        $this->triggerRbacEvent(self::EVENT_REMOVE_CHILDREN, $parent);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function revokeAll($userId)
    {
        $result = parent::revokeAll($userId);
        $this->triggerRbacEvent(self::EVENT_REVOKE_ALL, $userId);
        return $result;
    }

    public function removeAll()
    {
        $result = parent::removeAll();
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL);
        return $result;
    }

    public function removeAllPermissions()
    {
        $result = parent::removeAllPermissions();
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL_PERMISSIONS);
        return $result;
    }

    public function removeAllRoles()
    {
        $result = parent::removeAllRoles();
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL_ROLES);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeAllItems($type)
    {
        $result = parent::removeAllItems($type);
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL_ROLES, $type);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeAllRules()
    {
        $result = parent::removeAllRules();
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL_RULES);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function removeAllAssignments()
    {
        $result = parent::removeAllAssignments();
        $this->triggerRbacEvent(self::EVENT_REMOVE_ALL_ASSIGNMENTS);
        return $result;
    }

    /**
     * Триггер события изменения пермишенов
     * @param $event
     * @param mixed|null $parent
     * @param mixed|null $child
     */
    private function triggerRbacEvent($event, $parent = null, $child = null)
    {
        (new RbacAssignRevoke($event, $parent, $child))->trigger();
    }

    static function handleAssignRevoke(RbacAssignRevoke $event)
    {
        // TODO убрать подключение через апи
        Yii::$app->getModule('users')->api('auth')->renewAuthTokenById($event->userId);
    }

    /**
     * @inheritdoc
     */
    public function invalidateCache()
    {
        parent::invalidateCache();

        $this->_cache = [];
    }

    private function readFromCache($key)
    {
        if ($this->cache === null) return null;

        return array_key_exists($key, $this->_cache) ? $this->_cache[$key] : null;
    }

    private function writeToCache($key, $value)
    {
        if ($this->cache !== null) {
            $this->_cache[$key] = $value;
        }
    }

    private function cachedCall($key, $method, $arguments)
    {
    }

    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if ($this->cache === null) return parent::getAssignments($userId);

        $key = self::CACHE_KEY_ASSIGNMENTS . $userId;
        if (($result = $this->readFromCache($key)) === null) {
            $result = parent::getAssignments($userId);
            $this->writeToCache($key, $result);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getRolesByUser($userId)
    {
        if ($this->cache === null) return parent::getRolesByUser($userId);

        $key = self::CACHE_KEY_ROLES_BY_USER . $userId;
        if (($result = $this->readFromCache($key)) === null) {
            $result = parent::getRolesByUser($userId);
            $this->writeToCache($key, $result);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByUser($userId)
    {
        if ($this->cache === null) return parent::getPermissionsByUser($userId);

        $key = self::CACHE_KEY_PERMISSIONS_BY_USER . $userId;
        if (($result = $this->readFromCache($key)) === null) {
            $result = parent::getPermissionsByUser($userId);
            $this->writeToCache($key, $result);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPermissionsByRole($roleName)
    {
        if ($this->cache === null) return parent::getPermissionsByRole($roleName);

        $key = self::CACHE_KEY_PERMISSIONS_BY_ROLE . $roleName;
        if (($result = $this->readFromCache($key)) === null) {
            $result = parent::getPermissionsByRole($roleName);
            $this->writeToCache($key, $result);
        }
        return $result;
    }
}