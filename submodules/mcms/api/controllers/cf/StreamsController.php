<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\StreamsMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class StreamsController extends \mcms\api\controllers\v2\StreamsController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . StreamsMapper::getStatFilterBy());
            unset($params['filters'][StreamsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
