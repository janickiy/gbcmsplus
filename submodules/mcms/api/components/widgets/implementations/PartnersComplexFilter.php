<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\PartnersMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр партнеры/источники
 */
class PartnersComplexFilter extends ComplexFilter
{
  public $label = 'Partners';
  public $fieldLabelMask = '#{id}. {username}';
  public $relatedFieldLabelMask = '#{id}. {name}';
  public $isAjax = true;
  public $mapperName = 'partners';
  public $searchFields = ['id', 'username', 'sources' => ['id', 'name']];
  public $fields = ['id', 'username', 'sources' => ['id', 'name']];
  public $customFieldFormatter = ['currency', 'eur'];
  public $relatedCustomFieldFormatter = ['currency', 'eur'];

  /**
   * @inheritdoc
   */
  public function getInitFilters()
  {
    $filters = parent::getInitFilters();

    // фильтр по юзерам заменяем на принудительный возврат нужных элементов
    // это нужно например если в фильтре уже выбрано пара юзеров, то сервер должен каждый раз вернуть
    // профиты по этим элементам. Для этого мы передаем эти элементы в forceIds, чтобы они вернулись и точно попали в
    // лимит запроса.
    if (isset($filters[PartnersMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[PartnersMapper::getStatFilterBy()];
      unset($filters[PartnersMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
