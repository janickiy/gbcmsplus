<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\LandingPayTypesMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class LandingPayTypesController extends \mcms\api\controllers\v2\LandingPayTypesController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . LandingPayTypesMapper::getStatFilterBy());
            unset($params['filters'][LandingPayTypesMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
