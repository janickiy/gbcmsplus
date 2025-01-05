<?php


namespace admin\modules\alerts\commands;

use admin\modules\alerts\components\EventHandler;
use yii\console\Controller;

/**
 * Class CronController
 * @package admin\modules\alerts\commands
 */
class CronController extends Controller
{
    public function actionIndex()
    {
        (new EventHandler())->run();
        $this->stdout("\n");
    }

}
