<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\PlatformsMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class PlatformsController extends \mcms\api\controllers\v2\PlatformsController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        /** объяснение тут: @see PartnersController::getQueryParams() */

        if (empty($params['search'])) {
            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . PlatformsMapper::getStatFilterBy());
            unset($params['filters'][PlatformsMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
