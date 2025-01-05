<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\LandingsMapper;
use mcms\api\mappers\OfferCategoriesMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class OfferCategoriesController extends \mcms\api\controllers\v2\OfferCategoriesController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . OfferCategoriesMapper::getStatFilterBy());

            // фильтр по дочерним тоже сбрасываем, т.к. влияет на сумму
            unset($params['filters'][OfferCategoriesMapper::getStatFilterBy()], $params['filters'][LandingsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
