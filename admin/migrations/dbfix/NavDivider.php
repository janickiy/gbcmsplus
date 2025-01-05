<?php

namespace admin\migrations\dbfix;
class NavDivider extends SettingsAbstract
{
    const NAV_DIVIDER_TYPE = 'navDivider';
    protected $type = self::NAV_DIVIDER_TYPE;

    public function __construct($name)
    {
        $this->setKey(uniqid(rand(), true));
        $this->setName($name);
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return [];
    }
}