<?php

namespace console\controllers;


use common\components\traffic\Checker;
use yii\console\Controller;

/**
 * Class TrafficController
 * @package mcms\modmanager\commands
 */
class TrafficController extends Controller
{
    /**
     *
     */
    public function actionEnableChecker()
    {
        Checker::enable();

        $this->stdout("Traffic checker enabled\n");
    }

    /**
     *
     */
    public function actionCheck()
    {
        (new Checker())->run();
    }
}