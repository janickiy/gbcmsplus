<?php

namespace mcms\user\models;

use mcms\common\validators\IpValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * Лог регистрации пользователей
 *
 * @property string $id
 * @property string $user_id
 * @property string $ip
 * @property string $user_agent
 * @property string $created_at
 *
 * @property User $user
 */
class SignupLog extends \yii\db\ActiveRecord
{

  /**
   * @param $userId
   */
  public function __construct($userId)
  {
    $this->user_id = (int)$userId;
    $this->user_agent = Yii::$app->request->userAgent;
    $this->ip = Yii::$app->request->userIP;

    parent::__construct();
  }

  /**
   * @param $userId
   * @return SignupLog
   */
  public static function getInstance($userId)
  {
    return new self($userId);
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'signup_logs';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::class,
        'updatedAtAttribute' => false,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'ip', 'user_agent'], 'required'],
      [['user_id', 'created_at'], 'integer'],
      [['ip'], IpValidator::class],
      [['user_agent'], 'string', 'max' => 512],
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return bool
   */
  public function create()
  {
    // Сохраняем даже если не прошла валидация
    $valid = $this->validate();
    // Сохраняем заранее, чтобы в ошибке в списке атрибутов получить id записи и время created_at
    $result = $this->save(false);

    if (!$valid) {
      Yii::error(strtr('SignupLog not valid. Errors:{errors}. Model attributes:{attributes}', [
        '{errors}' => json_encode($this->getErrors()),
        '{attributes}' => json_encode($this->getAttributes()),
      ]), __METHOD__);
    }

    return $result;
  }
}
