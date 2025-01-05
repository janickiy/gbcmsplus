<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\OfferCategoriesMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр категории/лендинги
 */
class OfferCategoriesComplexFilter extends ComplexFilter
{
  public $label = 'Landings';
  public $fieldLabelMask = '{name}';
  public $relatedFieldLabelMask = '#{id}. {name}';
  public $isAjax = true;
  public $mapperName = 'offerCategories';
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
    if (isset($filters[OfferCategoriesMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[OfferCategoriesMapper::getStatFilterBy()];
      unset($filters[OfferCategoriesMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
