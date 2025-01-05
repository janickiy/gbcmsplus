<?php

namespace mcms\promo\components\rbac;

use Yii;
use yii\rbac\Rule;

class ViewOwnPersonalProfitsWidgetRule extends Rule
{
  public $name = 'ViewOwnPersonalProfitsWidgetRule';
  public $description = 'Can view own personal profits widget';

  public function execute($user, $item, $owner)
  {
    if ($user != $owner) return true;

    return Yii::$app->authManager->checkAccess($user, $this->name);
  }

}