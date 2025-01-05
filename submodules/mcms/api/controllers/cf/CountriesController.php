<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\CountriesMapper;
use mcms\api\mappers\OperatorsMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class CountriesController extends \mcms\api\controllers\v2\CountriesController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . CountriesMapper::getStatFilterBy());

            // фильтр по дочерним тоже сбрасываем, т.к. влияет на сумму
            unset($params['filters'][CountriesMapper::getStatFilterBy()], $params['filters'][OperatorsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
