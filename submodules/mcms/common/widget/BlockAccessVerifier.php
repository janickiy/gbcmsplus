<?php

namespace mcms\common\widget;

use Yii;
use yii\widgets\Block;

class BlockAccessVerifier extends Block
{
  public $renderInPlace = true;
  public $permissions = [];

  public function run()
  {
    $isAccessGrunted = true;

    if (count($this->permissions)) foreach ($this->permissions as $permission => $params) {
      $isAccessGrunted &= is_integer($permission)
        ? Yii::$app->getUser()->can($params)
        : Yii::$app->getUser()->can($permission, $params)
      ;
    }

    $block = ob_get_clean();

    if ($isAccessGrunted) {
      if ($this->renderInPlace) {
        echo $block;
      }
      $this->view->blocks[$this->getId()] = $block;
    }
  }
}