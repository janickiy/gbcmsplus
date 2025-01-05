<?php

namespace mcms\mcms\api\components\widgets\implementations;

use mcms\mcms\api\components\widgets\ComplexFilter;

/**
 * Фильтр фейки
 */
class FakeComplexFilter extends ComplexFilter
{
  public $label = 'Subscriptions';
  public $fieldLabelMask = '{name}';
  public $isAjax = false;
  public $searchFields = ['name'];
  public $fields = ['id', 'name'];
}
