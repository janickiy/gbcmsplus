<?php

namespace mcms\support\components\storage;

use mcms\common\storage\StorageInterface;
use mcms\common\traits\StorageTrait;
use mcms\support\models\Support;

class SupportStorage implements \mcms\common\storage\StorageInterface
{
  use \mcms\common\traits\StorageTrait;

  /**
   * CategoryStorage constructor.
   * @param Support $support
   */
  public function __construct(Support $support)
  {
    $this->_model = $support;
  }
}