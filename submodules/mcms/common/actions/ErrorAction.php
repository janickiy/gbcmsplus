<?php

namespace mcms\common\actions;

use Yii;
use yii\base\Action;
use yii\web\HttpException;

class ErrorAction extends Action
{

    private function getCode()
    {
        $exception = Yii::$app->getErrorHandler()->exception;

        if (empty($exception)) {
            return 404;
        }
        if ($exception instanceof HttpException) {
            return $exception->statusCode;
        }

        return $exception->getCode();
    }

    public function run()
    {
        $this->controller->layout = '@mcms/common/views/layouts/error';
        $this->controller->view->title = Yii::_t('app.errors.error_title');

        $code = $this->getCode();

        switch ($code) {
            case 403:
                $message = Yii::_t('app.errors.error_403');
                break;
            case 404:
                $message = Yii::_t('app.errors.error_404');
                break;
            default:
                $message = Yii::_t('app.errors.error_general');
        }

        $url = Yii::$app->getModule('users')->urlCabinet;

        return $this->controller->render('@mcms/common/views/error', compact('code', 'message', 'url'));
    }

}
