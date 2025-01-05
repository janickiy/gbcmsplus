<?php

namespace mcms\common\controller;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\data\BaseDataProvider;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\web\Response;

/**
 * Class ApiController
 * @package mcms\common\controller
 */
class ApiController extends \yii\rest\Controller
{
    public $enableCsrfValidation = false;

    public function init()
    {
        parent::init();

        Yii::$app->request->parsers = ['application/json' => 'yii\web\JsonParser'];

        Yii::$app->response->on(
            'beforeSend',
            function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $user = Yii::$app->getUser();
        $user->enableSession = false;

        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formatParam' => 'format',
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'authenticator' => [
                'class' => QueryParamAuth::class,
                'user' => $user,
                'except' => ['auth']
            ],
        ];
    }

    /**
     * @param $model
     * @param BaseDataProvider $dataProvider
     * @param $config
     * @return array
     */
    protected function handleResult($model, $dataProvider, $config)
    {
        $result = [];
        foreach ($dataProvider->getModels() as $item) {
            $result[] = $this->handleRow($model, $item, $config);
        }

        return $result;
    }

    /**
     * @param $model
     * @param $item
     * @param array $config
     * @return array
     */
    private function handleRow($model, $item, array $config)
    {
        $result = [];
        foreach ($config as $field) {
            if (!ArrayHelper::getValue($field, 'visible', true)) {
                continue;
            }

            $attr = ArrayHelper::getValue($field, 'attribute');
            $value = $attr ? ArrayHelper::getValue($item, $attr) : null;
            if (($valueCallback = ArrayHelper::getValue($field, 'value')) && is_callable($valueCallback)) {
                $value = call_user_func($valueCallback, $model, $item);
            }

            if ($format = ArrayHelper::getValue($field, 'format')) {
                switch ($format) {
                    default:
                        $value = Yii::$app->formatter->format($value, $format);
                        break;
                    case 'decimal':
                        $value = (float)$format;
                        break;
                    case 'integer':
                        $value = (int)$value;
                        break;
                }
            }
            ArrayHelper::assignByPath($result, ArrayHelper::getValue($field, 'label', $attr), $value);
        }

        return $result;
    }
}