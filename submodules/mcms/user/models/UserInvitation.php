<?php

namespace mcms\user\models;


use mcms\notifications\models\UserInvitationEmailSent;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Class UserInvitation
 * @package mcms\user\models
 *
 * @property int $id
 * @property string $hash
 * @property string $username
 * @property string $password
 * @property string $contact
 * @property int $user_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 */
class UserInvitation extends ActiveRecord
{
  const STATUS_AWAITING = 0; // ожидат регистрации
  const STATUS_SIGNUP_BY_LINK = 1; // зарегался по ссылке
  const STATUS_SIGNUP_BY_LOGIN = 2; // зарегался через форму логинки
  const STATUS_SIGNUP_BY_MANUAL = 3; // зарегистрировался сам через форму регистрации
  const STATUS_SIGNUP_BY_ADMIN = 4; // зарегали через админку
  const STATUS_ALREADY_SIGNED = 5; // уже был зареган

  /**
   * @return string
   */
  public static function tableName()
  {
    return 'users_invitations';
  }

  /**
   * @param $hash
   * @return UserInvitation
   */
  public static function findByHash($hash)
  {
    return static::find()
      ->andWhere([
        'hash' => $hash,
        'user_id' => null,
      ])
      ->one();
  }

  /**
   * @param $username
   * @param $password
   * @return UserInvitation
   */
  public static function findByCredentials($username, $password = null)
  {
    return static::find()
      ->andWhere([
        'username' => $username,
        'user_id' => null,
      ])
      ->andFilterWhere([
        'password' => $password, // если регается через форму регистрации, то пароль ввел другой
      ])
      ->one();
  }

  /**
   * @return array
   */
  public static function getStatusesMap()
  {
    return [
      self::STATUS_AWAITING => Yii::_t('users.users-invitations.status-awaiting'),
      self::STATUS_SIGNUP_BY_LINK => Yii::_t('users.users-invitations.status-by_link'),
      self::STATUS_SIGNUP_BY_LOGIN => Yii::_t('users.users-invitations.status-by_login'),
      self::STATUS_SIGNUP_BY_MANUAL => Yii::_t('users.users-invitations.status-by_manual'),
      self::STATUS_SIGNUP_BY_ADMIN => Yii::_t('users.users-invitations.status-by_admin'),
      self::STATUS_ALREADY_SIGNED => Yii::_t('users.users-invitations.status-already'),
    ];
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      ['hash', 'default', 'value' => Yii::$app->security->generateRandomString(16)],
      ['hash', 'unique'],
      ['password', 'default', 'value' => Yii::$app->security->generateRandomString(8)],
      [['hash', 'username', 'password',], 'required'],
      ['username', 'trim'],
      ['username', 'unique'],
      ['status', 'default', 'value' => function () {
        return User::find()->andWhere(['email' => $this->username])->exists()
          ? self::STATUS_ALREADY_SIGNED
          : self::STATUS_AWAITING;
      }],
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return array
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'hash' => Yii::_t('users.users-invitations.attribute-hash'),
      'username' => Yii::_t('users.users-invitations.attribute-username'),
      'password' => Yii::_t('users.users-invitations.attribute-password'),
      'contact' => Yii::_t('users.users-invitations.attribute-contact'),
      'user_id' => Yii::_t('users.users-invitations.attribute-user_id'),
      'status' => Yii::_t('users.users-invitations.attribute-status'),
      'created_at' => Yii::_t('users.users-invitations.attribute-created_at'),
      'updated_at' => Yii::_t('users.users-invitations.attribute-updated_at'),
    ];
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return sprintf('%s - %s', $this->id, $this->username);
  }

  /**
   * @return ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return ActiveQuery
   */
  public function getEmailSent()
  {
    return $this->hasMany(UserInvitationEmailSent::class, ['invitation_id' => 'id']);
  }

  /**
   * @return bool
   */
  public function isUserExist()
  {
    return $this->user_id && $this->getUser()->exists();
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return $this->user ? $this->user->getViewLink() : null;
  }

  /**
   * @return string
   */
  public function getStatusName()
  {
    return self::getStatusesMap()[$this->status];
  }

  /**
   * @return string ссылка на автоматическую регистрацию
   */
  public function getLink()
  {
    return Url::to(['/users/api/invite', 'hash' => $this->hash], true);
  }

  /**
   * @param User $user
   * @param int $status
   */
  public function setUser($user, $status = self::STATUS_SIGNUP_BY_LINK)
  {
    $this->user_id = $user->id;
    $this->status = $status;
  }
}