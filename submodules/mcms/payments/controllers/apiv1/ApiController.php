<?php

namespace mcms\payments\controllers\apiv1;

use mcms\payments\lib\mgmp\TokenAuth;
use Yii;

/**
 * todo этот класс удалить и использовать из common/mgmp
 * Class ApiController
 * @package mcms\common\controller
 */
class ApiController extends \yii\rest\Controller
{
  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    $user = Yii::$app->getUser();
    $user->enableSession = false;

    return [
      'authenticator' => [
        'class' => TokenAuth::class,
        'user' => $user,
      ]
    ];
  }

}