<?php

namespace mcms\api;

use yii\console\Application as ConsoleApplication;
use Yii;

/**
 * Class Module
 * @package mcms\api
 */
class Module extends \mcms\common\module\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'mcms\api\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'mcms\api\commands';
        }
    }
}
