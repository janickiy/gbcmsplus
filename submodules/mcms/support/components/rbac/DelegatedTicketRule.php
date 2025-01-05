<?php

namespace mcms\support\components\rbac;

use mcms\support\models\Support;
use yii\rbac\Rule;

class DelegatedTicketRule extends Rule
{
  public $name = 'SupportDelegatedTicketRule';
  public $description = 'Manipulate with delegated ticket';

  public function execute($user, $item, $params)
  {
    return isset($params['ticket'])
      && $params['ticket'] instanceof Support
      && $params['ticket']->delegated_to == $user
      ;
  }
}
