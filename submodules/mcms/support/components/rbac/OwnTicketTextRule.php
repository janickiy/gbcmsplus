<?php

namespace mcms\support\components\rbac;

use mcms\support\models\SupportText;
use yii\rbac\Rule;

class OwnTicketTextRule extends Rule
{
  public $name = 'SupportOwnTicketTextRule';
  public $description = 'Manipulate with own ticket text';

  public function execute($user, $item, $params)
  {
    $can = isset($params['ticketText'])
      && $params['ticketText'] instanceof SupportText
      && $params['ticketText']->from_user_id == $user
      ;

    return $can;
  }
}