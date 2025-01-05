<?php

namespace mcms\partners\components\helpers;

use Yii;
use yii\base\Object;

class PromoHelper extends Object
{
  public static function updatePartnerType($choose)
  {
    Yii::$app->getModule('users')
      ->api('editUser', [
        'user_id' => Yii::$app->user->id,
        'post_data' => ['partner_type' => $choose]
      ])->getResult();
  }
}