<?php

namespace mcms\api\controllers\v2;

use mcms\api\components\ApiResponse;
use mcms\api\components\MapperBuilder;
use mcms\api\components\HttpQueryParser;
use mcms\api\mappers\OperatorsMapper;
use Yii;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Class OperatorsController
 * @package mcms\api\controllers
 */
class OperatorsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'POST'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function actionIndex()
    {
        $params = $this->getQueryParams();
        $queryParser = Yii::createObject(HttpQueryParser::class, [$params]);

        $mapper = (new MapperBuilder(OperatorsMapper::getName()))->build($queryParser);

        return new ApiResponse([
            'data' => $mapper->getMappedResult(),
        ]);
    }

    /**
     * @return array
     */
    protected function getQueryParams()
    {
        return ArrayHelper::merge(Yii::$app->request->queryParams, Yii::$app->request->post());
    }
}
