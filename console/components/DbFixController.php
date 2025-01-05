<?php

namespace console\components;

use Yii;
use yii\console\Controller;

class DbFixController extends Controller
{

    public $sm;

    const MODULE_DBFIX_DIRNAME = 'dbfix';

    const MODULE_DBFIX_CLASS_TEMPLATE = 'mcms\%s\\' . self::MODULE_DBFIX_DIRNAME;

    /**
     * Пример:
     * php yii db-fix/run-command m160429_103612_fix_rebills_dupl --sm=statistic
     *
     * @param $className
     */
    public function actionRunCommand($className)
    {
        $class = sprintf(self::MODULE_DBFIX_CLASS_TEMPLATE, $this->sm) . '\\' . $className;
        (new $class)->up();
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            $actionID == 'run-command' ? ['sm'] : []
        );
    }


}