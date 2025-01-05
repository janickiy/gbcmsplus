<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\LandingCategoriesMapper;
use mcms\api\mappers\LandingsMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class LandingCategoriesController extends \mcms\api\controllers\v2\LandingCategoriesController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . LandingCategoriesMapper::getStatFilterBy());

            // фильтр по дочерним тоже сбрасываем, т.к. влияет на сумму
            unset($params['filters'][LandingCategoriesMapper::getStatFilterBy()], $params['filters'][LandingsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
