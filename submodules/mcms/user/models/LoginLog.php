<?php

namespace mcms\user\models;

use mcms\common\validators\IpValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * This is the model class for table "login_logs".
 *
 * @property string $id
 * @property string $user_id
 * @property string $ip
 * @property string $user_agent
 * @property string $created_at
 * @property string $server_data
 *
 * @property User $user
 */
class LoginLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_logs';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'created_at',
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ip' => Yii::_t('login_logs.ip'),
            'user_agent' => Yii::_t('login_logs.user_agent'),
            'created_at' => Yii::_t('login_logs.created_at'),
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
     * Делаем запись в лог для текущего юзера под которым залогинены прям сейчас
     * @return bool
     */
    public static function create()
    {
        $log = new self;
        $log->user_id = Yii::$app->user->id;
        $log->user_agent = Yii::$app->request->userAgent;
        $log->ip = Yii::$app->request->userIP;
        $log->server_data = Json::encode($_SERVER);

        if (!$log->save()) {
            Yii::error(strtr('LoginLog not saved. Errors:{errors}. Model attributes:{attributes}', [
                '{errors}' => json_encode($log->getErrors()),
                '{attributes}' => json_encode($log->getAttributes()),
            ]), __METHOD__);
            return false;
        }

        return true;
    }
}
