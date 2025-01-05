<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\ProvidersMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class ProvidersController extends \mcms\api\controllers\v2\ProvidersController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . ProvidersMapper::getStatFilterBy());
            unset($params['filters'][ProvidersMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
