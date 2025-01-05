<?php

namespace admin\modules\alerts;

use Yii;
use yii\console\Application as ConsoleApplication;

class Module extends \mcms\common\module\Module
{
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'admin\modules\alerts\commands';
        }
    }
}