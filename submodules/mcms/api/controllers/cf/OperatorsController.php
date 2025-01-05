<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\OperatorsMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class OperatorsController extends \mcms\api\controllers\v2\OperatorsController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . OperatorsMapper::getStatFilterBy());
            unset($params['filters'][OperatorsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
