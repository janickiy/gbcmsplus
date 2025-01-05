<?php

namespace mcms\api\controllers\cf;

use mcms\api\mappers\PartnersMapper;
use mcms\api\mappers\SourcesMapper;
use yii\helpers\ArrayHelper;

/**
 * Перепределяем апи-контроллер специально для complex filter из-за слишком условного поведения
 */
class PartnersController extends \mcms\api\controllers\v2\PartnersController
{

    /**
     * @inheritdoc
     */
    protected function getQueryParams()
    {
        $params = parent::getQueryParams();

        if (empty($params['search'])) {
            // если не поиск по тексту, то фильтр по юзерам заменяем на принудительный возврат нужных элементов
            // это нужно например если в фильтре уже выбрано пара юзеров, то сервер должен каждый раз вернуть
            // профиты по этим элементам. Для этого мы передаем эти элементы в forceIds, чтобы они вернулись и точно попали в
            // лимит запроса.

            $params['filters']['forceIds'] = ArrayHelper::getValue($params, 'filters.' . PartnersMapper::getStatFilterBy());

            // фильтр по дочерним тоже сбрасываем, т.к. влияет на сумму
            unset($params['filters'][PartnersMapper::getStatFilterBy()], $params['filters'][SourcesMapper::getStatFilterBy()]);
        }

        return $params;
    }
}
