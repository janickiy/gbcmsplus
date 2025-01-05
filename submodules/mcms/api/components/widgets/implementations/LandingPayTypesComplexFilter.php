<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\LandingPayTypesMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр типы оплат
 */
class LandingPayTypesComplexFilter extends ComplexFilter
{
  public $label = 'Pay type';
  public $fieldLabelMask = '{name}';
  public $isAjax = false;
  public $mapperName = 'landingPayTypes';
  public $searchFields = ['id', 'name'];
  public $fields = ['id', 'name'];
  public $customFieldFormatter = ['currency', 'eur'];

  /**
   * @inheritdoc
   */
  public function getInitFilters()
  {
    $filters = parent::getInitFilters();

    /** объяснение тут: @see PartnersComplexFilter::getInitFilters() */
    if (isset($filters[LandingPayTypesMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[LandingPayTypesMapper::getStatFilterBy()];
      unset($filters[LandingPayTypesMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
