<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\PlatformsMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр платформы
 */
class PlatformsComplexFilter extends ComplexFilter
{
  public $label = 'Platforms';
  public $fieldLabelMask = '#{id}. {name}';
  public $isAjax = false;
  public $mapperName = 'platforms';
  public $searchFields = ['name'];
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
    if (isset($filters[PlatformsMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[PlatformsMapper::getStatFilterBy()];
      unset($filters[PlatformsMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
