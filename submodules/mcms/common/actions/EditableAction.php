<?php

namespace mcms\common\actions;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Response;


class EditableAction extends Action
{
    public $modelClass;
    public $successMessage;
    public $failMessage;
    /**
     * Функция должна вернуть массив
     * ['success' => true | false, 'message' => 'Текст сообщения если есть ошибка']
     * @var string
     */
    public $callback;

    const HAS_EDITABLE = 'hasEditable';

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $requestData = array_merge(Yii::$app->request->post(), Yii::$app->request->getQueryParams());

        if (!ArrayHelper::keyExists(self::HAS_EDITABLE, $requestData)) {
            throw new BadRequestHttpException;
        }

        if (!method_exists($this->controller, $this->callback)) {
            throw new BadRequestHttpException;
        }

        $result = call_user_func([$this->controller, $this->callback], $requestData);

        $resultStatus = ArrayHelper::getValue($result, 'success', false);
        $message = ArrayHelper::getValue($result, 'message', '');

        return $resultStatus
            ? $this->success($message)
            : $this->fail($message);
    }

    private function response($output = '', $message = '')
    {
        return [
            'output' => $output,
            'message' => $message,
        ];
    }

    private function success($message)
    {
        return $this->response($message);
    }

    private function fail($message)
    {
        return $this->response('', $message);
    }
}