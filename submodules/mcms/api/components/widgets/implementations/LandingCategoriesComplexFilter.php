<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\LandingCategoriesMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр категории/лендинги
 */
class LandingCategoriesComplexFilter extends ComplexFilter
{
  public $label = 'Landings';
  public $fieldLabelMask = '{name}';
  public $relatedFieldLabelMask = '#{id}. {name}';
  public $isAjax = true;
  public $mapperName = 'landingCategories';
  public $searchFields = ['id', 'name', 'landings' => ['id', 'name']];
  public $fields = ['id', 'name', 'landings' => ['id', 'name']];
  public $customFieldFormatter = ['currency', 'eur'];
  public $relatedCustomFieldFormatter = ['currency', 'eur'];

  /**
   * @inheritdoc
   */
  public function getInitFilters()
  {
    $filters = parent::getInitFilters();

    /** объяснение тут: @see PartnersComplexFilter::getInitFilters() */
    if (isset($filters[LandingCategoriesMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[LandingCategoriesMapper::getStatFilterBy()];
      unset($filters[LandingCategoriesMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
