<?php

namespace mcms\support\components\storage;

use mcms\common\storage\StorageInterface;
use mcms\common\traits\StorageTrait;
use mcms\support\models\SupportCategory;

class CategoryStorage implements \mcms\common\storage\StorageInterface
{
  use \mcms\common\traits\StorageTrait;

  /**
   * CategoryStorage constructor.
   * @param SupportCategory $supportCategory
   */
  public function __construct(SupportCategory $supportCategory)
  {
    $this->_model = $supportCategory;
  }
}