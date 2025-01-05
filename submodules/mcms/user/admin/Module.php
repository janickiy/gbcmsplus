<?php

namespace mcms\user\admin;

use Yii;


class Module extends \mdm\admin\Module
{

    public $controllerNamespace = 'mdm\admin\controllers';

    public $controllerMap = [
        'role' => 'mcms\user\admin\controllers\RoleController',
        'tree' => [
          'class' => 'mcms\user\admin\controllers\TreeController',
          'viewPath' => '@mcms/user/admin/views/tree'
        ],
    ];

    public function init()
    {
        parent::init();

    }

}