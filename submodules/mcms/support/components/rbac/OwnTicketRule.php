<?php

namespace mcms\support\components\rbac;

use mcms\support\models\Support;
use yii\rbac\Rule;

class OwnTicketRule extends Rule
{
  public $name = 'SupportOwnTicketRule';
  public $description = 'Manipulate with own ticket';

  public function execute($user, $item, $params)
  {
    $can = isset($params['ticket'])
      && $params['ticket'] instanceof Support
      && $params['ticket']->created_by == $user
      ;

    return $can;
  }
}