<?php

namespace mcms\common\web;

use mcms\common\widget\BlockAccessVerifier;

class View extends \yii\web\View
{
  public function beginBlockAccessVerifier($id, array $permissions = [], $renderInPlace = true)
  {
    return BlockAccessVerifier::begin([
      'id' => $id,
      'permissions' => $permissions,
      'renderInPlace' => $renderInPlace,
      'view' => $this
    ]);
  }

  public function endBlockAccessVerifier()
  {
    return BlockAccessVerifier::end();
  }
}