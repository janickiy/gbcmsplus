<?php

namespace console\controllers;

use common\components\geo_service_sync\handlers\CountriesSyncHandler;
use common\components\geo_service_sync\handlers\OperatorsSyncHandler;
use common\components\geo_service_sync\SyncRunner;
use mcms\common\output\OutputInterface;
use Yii;
use yii\console\Controller;

class GeoServiceSyncController extends Controller
{
    public function actionRun($printLog = 0)
    {
        $printLog && $this->stdout('Start Sync the data from the Geo Service' . PHP_EOL);

        if (!$printLog) {
            Yii::$container->set(OutputInterface::class, new class {
                public function log($string, $params = [])
                {
                }
            });
        }

        $runner = new SyncRunner(['syncHandlers' => [
            CountriesSyncHandler::class,
            OperatorsSyncHandler::class,
        ]]);

        $runner->run();

        $printLog && $this->stdout('Sync completed' . PHP_EOL);
    }
}
