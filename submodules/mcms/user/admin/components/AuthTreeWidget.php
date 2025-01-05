<?php

namespace mcms\user\admin\components;

use yii\base\Widget;

/**
 * Class AuthTreeWidget
 * @package mcms\user\admin\components
 */
class AuthTreeWidget extends Widget
{

  /**
   * @inheritdoc
   */
  public function run()
  {
    $tree = new AuthTree();

    return $this->render('tree/index', [
      'roles' => $tree->getRoles(),
      'data' => $tree->getTree()
    ]);
  }

}