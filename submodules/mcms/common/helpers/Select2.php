<?php

namespace mcms\common\helpers;

use Yii;
class Select2
{
    const SELECT2_LIMIT = 10;

    /**
     * @param $searchModel
     * @param bool|true $addIdMatch нужен для
     * @return array
     */
    public static function getItems($searchModel, $addIdMatch = true)
    {
        $elements = [];
        $pageSize = self::SELECT2_LIMIT;

        $params = Yii::$app->request->get();

        if (isset($params['q'])) {
            $params['queryName'] = $params['q'];
            unset($params['q']);

            if ($addIdMatch && preg_match('/\d+/', $params['queryName'])) {
                $searchModelById = clone $searchModel;
                $paramsById = $params;
                unset($paramsById['queryName']);
                $paramsById['id'] = $params['queryName'];

                $elements = array_merge($elements, static::getModels($searchModelById, [$searchModelById->formName() => $paramsById], 1));
                if (!empty($elements)) {
                    $pageSize--;
                }
            }
        }

        $elements = array_merge($elements, static::getModels($searchModel, [$searchModel->formName() => $params], $pageSize));

        return ['results' => $elements];
    }

    /**
     * @param $searchModel
     * @param $params
     * @param $pageSize
     * @return array|array[]
     */
    private static function getModels($searchModel, $params, $pageSize)
    {
        $dataProvider = $searchModel->search($params);
        $dataProvider->getPagination()->setPageSize($pageSize);

        return array_map(function ($item) {
            return [
                'text' => $item->getStringInfo(),
                'id' => ArrayHelper::getValue($item, 'id')
            ];
        }, $dataProvider->getModels());
    }

}
