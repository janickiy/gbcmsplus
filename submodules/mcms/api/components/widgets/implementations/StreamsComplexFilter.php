<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\api\mappers\StreamsMapper;
use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр потоки
 */
class StreamsComplexFilter extends ComplexFilter
{
  public $label = 'Streams';
  public $fieldLabelMask = '#{id}. {name}';
  public $isAjax = true;
  public $mapperName = 'streams';
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
    if (isset($filters[StreamsMapper::getStatFilterBy()])) {
      $filters['forceIds'] = $filters[StreamsMapper::getStatFilterBy()];
      unset($filters[StreamsMapper::getStatFilterBy()]);
    }

    return $filters;
  }
}
