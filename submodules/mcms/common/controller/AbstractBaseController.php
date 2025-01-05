<?php

namespace mcms\common\controller;

use mcms\common\actions\ErrorAction;
use mcms\common\helpers\StringEncoderDecoder;
use mcms\common\traits\Flash;
use mcms\common\web\AjaxResponse;
use mcms\notifications\models\BrowserNotification;
use mcms\notifications\Module;
use Yii;
use yii\base\InlineAction;
use yii\helpers\BaseInflector;

abstract class AbstractBaseController extends BaseRgkController
{

    use Flash;

    /** @var  \mcms\common\module\Module */
    public $module;
    protected $controllerTitle = null;
    public $defaultAction = 'list';

    public function init()
    {
        parent::init();

        Yii::$container->set('kartik\date\DatePicker', [
            'language' => Yii::$app->params['langCodes'][Yii::$app->language],
        ]);
        Yii::$container->set('kartik\datecontrol\DateControl', [
            'language' => Yii::$app->params['langCodes'][Yii::$app->language],
        ]);
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'error' => ErrorAction::class,
        ]);
    }

    public function beforeAction($action)
    {
        /** @var InlineAction $action */
        if (!parent::beforeAction($action)) return false;
        if ($action->id === 'error') return true;

        $this->setNotificationAsViewed(null, null, true);

        $reflectionClass = new \ReflectionClass($this);
        $className = str_replace('Controller', '', $reflectionClass->getShortName());
        $permission = BaseInflector::camelize($this->module->id . '_' . $className . '_' . $action->id);
        if (Yii::$app->user->can($permission)) return true;

        $errorMessage = 'app.common.access_denied';
        if (YII_DEBUG) {
            $tParams = ['permission' => $permission];
        } else {
            $tParams = ['permission' => ''];
        }

        if (Yii::$app->request->isAjax) {
            AjaxResponse::setStatusCode(403);
            Yii::$app->response->content = Yii::_t($errorMessage, $tParams);
            return false;
        }
//    if (!Yii::$app->user->isGuest) $this->flashFail($errorMessage, $tParams);
        $this->redirect(Yii::$app->getModule('users')->urlCabinet);
//    return false;
    }

    /**
     * Отмечаем уведомления прочитанными
     * tricky: Если хотим реализовать свою логику (например, отмечать только, если у модели определенный статус),
     * необходимо переопределить этот метод. @param string $event имя класса события
     * @param int $fn id браузерного уведомления
     * @param bool $onlyOwner
     * @see \mcms\promo\controllers\ArbitrarySourcesController::setNotificationAsViewed()
     */
    protected function setNotificationAsViewed($event = null, $fn = null, $onlyOwner = false)
    {
        $fn = $fn ?: Yii::$app->request->getQueryParam(Module::FN_QUERY_PARAM);
        if ($fn === null) return;

        if ($event) {
            $event = is_array($event) && count($event) ? $event : [$event];
        }

        Yii::$app->getModule('notifications')->api('setViewedByIdEvent', [
            'event' => $event,
            Module::FN_QUERY_PARAM => $fn,
            'onlyOwner' => $onlyOwner,
        ])->getResult();

    }

    /**
     * Получение module_id уведомления, для помечания его прочитанным
     * tricky: использовать в случае, когда необходимо реализовать свою логику (например, отмечать только,
     * если у модели определенный статус). @param string $event имя класса события
     * @param int $fn id браузерного уведомления
     * @return string|null
     * @see \mcms\promo\controllers\ArbitrarySourcesController::setNotificationAsViewed()
     */
    protected function getNotificationModuleId($event = null, $fn = null)
    {
        if (!$event) return null;
        $fn = $fn ?: (int)Yii::$app->request->getQueryParam(Module::FN_QUERY_PARAM);
        if (!$fn) return null;

        $notification = BrowserNotification::findOne(['id' => $fn]);

        if (!$notification) return null;

        $modelId = $notification->model_id ?: $notification->id;

        return StringEncoderDecoder::encode($modelId);
    }

}