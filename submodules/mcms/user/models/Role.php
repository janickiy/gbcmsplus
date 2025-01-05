<?php

namespace mcms\user\models;


use mcms\notifications\models\Notification;
use mcms\user\models\search\User as UserSearch;
use mcms\user\Module;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class Role
 * @package mcms\user\models
 */
class Role extends ActiveRecord
{
  /**
   * @inheritDoc
   */
  public static function tableName()
  {
    return '{{%auth_item}}';
  }

  public function getNotifications()
  {
    return $this
      ->hasMany(Notification::class, ['id' => 'notification_id'])
      ->viaTable('notifications_auth_item', ['auth_item_name' => 'name'])
      ;
  }

  public function getUsers()
  {
    return $this
      ->hasMany(User::class, ['id' => 'user_id'])
      ->viaTable('auth_assignment', ['item_name' => 'name'])
      ;
  }

  public function getActiveUsers()
  {
    return $this->getUsers()->where(['status' => User::STATUS_ACTIVE]);
  }

  public static function getDropdownListData()
  {
    $roles = Yii::$app->authManager->getRoles();
    if (!Yii::$app->user->can(UserSearch::PERMISSION_VIEW_ROOT_USER)) {
      unset($roles[Module::ROOT_ROLE]);
    }

    if (!Yii::$app->user->can(UserSearch::PERMISSION_VIEW_ADMIN_USER)) {
      unset($roles[Module::ADMIN_ROLE]);
    }

    if (!Yii::$app->user->can(UserSearch::PERMISSION_VIEW_RESELLER_USER)) {
      unset($roles[Module::RESELLER_ROLE]);
    }

    if (!Yii::$app->user->can(UserSearch::PERMISSION_VIEW_PARTNER_USER)) {
      unset($roles[Module::PARTNER_ROLE]);
    }

    /** @var Module $userModule */
    $userModule = Yii::$app->getModule('users');
    if (!Yii::$app->user->can(UserSearch::PERMISSION_VIEW_MANAGER_USER)) {
      foreach ($userModule->getManagerRoles() as $managerRole) {
        unset($roles[$managerRole]);
      }
    }

    unset($roles[Module::GUEST_ROLE]);

    $roles = array_keys($roles);

    return array_combine($roles, $roles);
  }
}