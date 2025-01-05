<?php

namespace mcms\user\components\storage;

interface UserInterface
{
    public function finByRoles(array $roles);
}