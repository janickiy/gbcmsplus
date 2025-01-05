<?php

namespace mcms\user\components\storage;

/**
 * Interface RolesInterface
 * @package mcms\user\components\storage
 */
interface RolesInterface
{
    /**
     * @param bool $includeRoles
     * @return array
     * @deprecated
     */
    public function getRoles($includeRoles = true);

    /**
     * @return string
     * @deprecated
     */
    public function getOwnerRole();
}