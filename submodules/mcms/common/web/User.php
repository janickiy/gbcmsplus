<?php

namespace mcms\common\web;

use mcms\user\models\LoginLog;
use Yii;
use yii\console\Application as ConsoleApplication;
use mcms\user\models\User as UserModel;

class User extends \yii\web\User
{

  private static $_rootUser;

  /**
   * @param bool $autoRenew
   * @return UserModel|null|\yii\web\IdentityInterface
   */
  public function getIdentity($autoRenew = true)
  {
    if (Yii::$app instanceof ConsoleApplication) {
      if (self::$_rootUser) {
        return self::$_rootUser;
      }

      return self::$_rootUser = UserModel::findOne(UserModel::ROOT_USER_ID);
    }

    $identity = \yii\web\User::getIdentity($autoRenew);

    if ($identity === null) {
      return null;
    }

    $session = \Yii::$app->getSession();
    $sessionAuthToken = $session->get(UserModel::SESSION_AUTH_TOKEN_KEY);
    if ($sessionAuthToken === null) {
      return $identity;
    }
    return $identity->getAuthKey() === $sessionAuthToken
      ? $identity
      : null;
  }

  public function getIsGuest()
  {
    return Yii::$app instanceof ConsoleApplication ? false : \yii\web\User::getIsGuest();
  }

  public function can($permissionName, $params = [], $allowCaching = true)
  {
    return Yii::$app->authManager->getPermission($permissionName) && parent::can($permissionName, $params, $allowCaching);
  }

  /**
   * переопределил метод чтобы сделать публичным
   * @inheritdoc
   */
  public function sendIdentityCookie($identity, $duration)
  {
    parent::sendIdentityCookie($identity, $duration);
  }


  /**
   * переопределил метод чтобы сделать публичным
   * @inheritdoc
   */
  public function getIdentityAndDurationFromCookie()
  {
    return parent::getIdentityAndDurationFromCookie();
  }

  /**
   * переопределил для логирования логина
   * @inheritdoc
   */
  protected function afterLogin($identity, $cookieBased, $duration)
  {
    LoginLog::create();
    return parent::afterLogin($identity, $cookieBased, $duration);
  }
}