<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\ProvidersMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр провайдеры
 */
class ProvidersComplexFilter extends ComplexFilter
{
  public $label = 'Providers';
  public $fieldLabelMask = '{name}';
  public $isAjax = false;
  public $mapperName = 'providers';
  public $searchFields = ['id', 'name'];
  public $fields = ['id', 'name'];
  public $customFieldFormatter = ['currency', 'eur'];
  public $limit = 100;
  /**
   * @inheritdoc
   */
  public function getInitFilters()
  {
    $filters = parent::getInitFilters();

    /** объяснение тут: @see PartnersComplexFilter::getInitFilters() */
    if (isset($filters[ProvidersMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[ProvidersMapper::getStatFilterBy()];
      unset($filters[ProvidersMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
