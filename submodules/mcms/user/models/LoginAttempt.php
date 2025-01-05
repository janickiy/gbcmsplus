<?php

namespace mcms\user\models;

use mcms\common\validators\IpValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "login_attempt".
 *
 * @property string $id
 * @property string $user_id
 * @property string $login
 * @property string $password
 * @property string $ip
 * @property string $user_agent
 * @property string $server_data
 * @property string $fail_reason
 * @property string $created_at
 *
 * @property User $user
 */
class LoginAttempt extends \yii\db\ActiveRecord
{
    const REASON_INVALID_LOGIN = 0;
    const REASON_INVALID_PASSWORD = 1;
    const REASON_INVALID_CAPTCHA = 2;
    const REASON_USER_INACTIVE = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_attempt';
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
            [['login', 'ip', 'user_agent'], 'required'],
            [['user_id', 'fail_reason', 'created_at'], 'integer'],
            ['fail_reason', 'default', 'value' => self::REASON_INVALID_LOGIN],
            [['ip'], IpValidator::class],
            [['login', 'ip', 'password',], 'string'],
            [['user_agent'], 'string', 'max' => 512],
            [['server_data'], 'safe'], // валидатор String экранирует
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // если причина неудачной попытки не в неверном пароле, то пароль не записываем
        if (!$this->isPasswordMustBeSave()) {
            $this->password = '';
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::_t('login_logs.user'),
            'login' => Yii::_t('login_logs.login'),
            'password' => Yii::_t('login_logs.password'),
            'ip' => Yii::_t('login_logs.ip'),
            'user_agent' => Yii::_t('login_logs.user_agent'),
            'server_data' => Yii::_t('login_logs.server_data'),
            'fail_reason' => Yii::_t('login_logs.fail_reason'), // TODO в переводы
            'created_at' => Yii::_t('login_logs.created_at'),
        ];
    }

    /**
     * @return array
     */
    public function failReasonLabels()
    {
        return [
            self::REASON_INVALID_LOGIN => Yii::_t('login_logs.reason_invalid_login'), // TODO в переводы
            self::REASON_INVALID_PASSWORD => Yii::_t('login_logs.reason_invalid_password'), // TODO в переводы
            self::REASON_INVALID_CAPTCHA => Yii::_t('login_logs.reason_invalid_captcha'), // TODO в переводы
            self::REASON_USER_INACTIVE => Yii::_t('login_logs.reason_user_inactive'), // TODO в переводы
        ];
    }

    /**
     * @return string
     */
    public function getFailReasonLabel()
    {
        return $this->failReasonLabels()[$this->fail_reason];
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getUserLink()
    {
        return $this->user ? $this->user->getViewLink() : null;
    }

    /**
     * @return bool
     */
    public function isPasswordMustBeSave()
    {
        return in_array($this->fail_reason, [self::REASON_INVALID_LOGIN, self::REASON_INVALID_PASSWORD]);
    }
}
