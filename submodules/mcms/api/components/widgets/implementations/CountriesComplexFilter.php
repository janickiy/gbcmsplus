<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\CountriesMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр страны/операторы
 */
class CountriesComplexFilter extends ComplexFilter
{
  public $label = 'Countries';
  public $fieldLabelMask = '{name}';
  public $relatedFieldLabelMask = '#{id}. {name}';
  public $isAjax = true;
  public $mapperName = 'countries';
  public $fields = ['id', 'name', 'operators' => ['id', 'name']];
  public $searchFields = ['id', 'name', 'operators' => ['id', 'name']];
  public $limit = 1000;
  public $relatedLimit = 1000;
  public $customFieldFormatter = ['currency', 'eur'];
  public $relatedCustomFieldFormatter = ['currency', 'eur'];

  /**
   * @inheritdoc
   */
  public function getInitFilters()
  {
    $filters = parent::getInitFilters();

    /** объяснение тут: @see PartnersComplexFilter::getInitFilters() */
    if (isset($filters[CountriesMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[CountriesMapper::getStatFilterBy()];
      unset($filters[CountriesMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
