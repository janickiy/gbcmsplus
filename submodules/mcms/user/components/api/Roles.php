<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\Module;
use yii\helpers\ArrayHelper;

class Roles extends ApiResult
{
    protected $withOwner = false;
    protected $removeGuest = false;

    function init($params = [])
    {
        $this->withOwner = ArrayHelper::getValue($params, 'withOwner', false);
        $this->removeGuest = in_array('removeGuest', $params);

        $dataProvider = (new \mdm\admin\models\searchs\AuthItem([
                'type' => \yii\rbac\Item::TYPE_ROLE]
        ))->search([]);

        $this->setDataProvider($dataProvider);
        $this->setResultTypeMap();
        $this->setMapParams(['name', 'name']);
    }

    public function getOwnRole()
    {
        return Module::OWNER_ROLE;
    }

    public function getMainRoles()
    {
        return [Module::PARTNER_ROLE, Module::RESELLER_ROLE];
    }

    public function getResult()
    {
        $roles = parent::getResult();
        if ($this->withOwner) {
            $roles = array_merge([Module::OWNER_ROLE => Module::OWNER_ROLE], $roles);
        }

        if ($this->removeGuest) {
            unset($roles[Module::GUEST_ROLE]);
        }

        return $roles;
    }
}