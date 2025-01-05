<?php

namespace admin\modules\alerts\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "alert_event_logs".
 *
 * @property integer $id
 * @property integer $event_id
 * @property integer $model_id
 * @property string $created_at
 *
 * @property Event $event
 */
class EventLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'alert_event_logs';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_id',], 'required'],
            [['event_id', 'model_id'], 'integer'],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => Event::class, 'targetAttribute' => ['event_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'model_id' => 'Model ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(Event::class, ['id' => 'event_id']);
    }
}
