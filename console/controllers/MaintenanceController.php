<?php

namespace console\controllers;


use common\components\MaintenanceComponent;
use Yii;
use yii\console\Controller;

class MaintenanceController extends Controller
{

    public function actionEnable()
    {
        /** @var MaintenanceComponent $component */
        $component = Yii::$container->get(MaintenanceComponent::class);

        $component->setMaintenance();
    }

    public function actionDisable()
    {
        /** @var MaintenanceComponent $component */
        $component = Yii::$container->get(MaintenanceComponent::class);

        $component->setNotMaintenance();
    }
}