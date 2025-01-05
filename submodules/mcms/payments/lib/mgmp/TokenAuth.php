<?php
namespace mcms\payments\lib\mgmp;

use Yii;
use yii\filters\auth\AuthMethod;

/**
 * todo этот класс удалить и использовать из common/mgmp
 * Class TokenAuth
 * @package mcms\common\auth
 */
class TokenAuth extends AuthMethod
{

  /** пока храним тут, потом можно вынести в настройки */
  const LIFETIME = 3600 * 24;

  /**
   * @var string the parameter name for passing the access token
   */
  public $tokenParam = 'access_token';

  /**
   * @var string the parameter time
   */
  public $timeParam = 'time';

  /**
   * @inheritdoc
   */
  public function authenticate($user, $request, $response)
  {
    $accessToken = $request->get($this->tokenParam);
    $time = (int) $request->get($this->timeParam);


    if ($accessToken && $time && (time() - $time) >= self::LIFETIME) {
      $this->handleFailure($response);
    }

    $secretKey = Yii::$app->getModule('payments')->getMgmpSecretKey();
    if (md5($secretKey . $time) !== $accessToken) {
      $this->handleFailure($response);
    }

    return true;
  }

}