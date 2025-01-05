<?php

namespace mcms\notifications\traits;

use mcms\common\helpers\Link;
use mcms\user\models\User;
use yii\db\ActiveQuery;

/**
 * Базовый класс для моделей уведомлений
 * @property User $user
 * @property User $fromUser
 */
trait BaseNotificationTrait
{
  /**
   * @return ActiveQuery
   */
  public function getFromUser()
  {
    return $this->hasOne(User::class, ['id' => 'from_user_id']);
  }

  /**
   * @return string|null
   */
  public function getFromUserLink()
  {
    return $this->userLink($this->fromUser);
  }

  /**
   * @return string|null
   */
  public function getUserLink()
  {
    return $this->userLink($this->user);
  }

  /**
   * @param User|null $user
   * @return string|null
   */
  public function userLink(User $user = null)
  {
    return $user ? $user->getViewLink() : null;
  }
}