<?php

namespace mcms\common\controller;

use common\components\MaintenanceComponent;
use Yii;
use yii\web\Controller;

/**
 * Кастомный базовый контроллер
 */
abstract class BaseRgkController extends Controller
{
    /**
     * @param $action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        /** @var MaintenanceComponent $component */
        $component = Yii::$container->get(MaintenanceComponent::class);

        if ($component->isMaintenance()) {
            echo $this->renderPartial('@mcms/user/components/widgets/views/maintenance');
            die();
        }

        return parent::beforeAction($action);
    }
}