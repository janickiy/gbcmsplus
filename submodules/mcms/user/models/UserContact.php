<?php

namespace mcms\user\models;

use mcms\common\helpers\ArrayHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class UserParam
 * @package mcms\user\models
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $type
 * @property string $data
 * @property string $is_deleted
 * @property string $created_at
 * @property string $updated_at
 */
class UserContact extends ActiveRecord
{
    const TYPE_DEFAULT = 0;
    const TYPE_TELEGRAM = 1;
    const TYPE_SKYPE = 2;

    const SCENARIO_PARTNER = 'partner';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'user_contacts';
    }

    /**
     * @param bool $availableOnly
     * @return array
     */
    public static function getTypes($availableOnly = false)
    {
        $types = [];
        if ($availableOnly === false) {
            $types[self::TYPE_DEFAULT] = Yii::_t('users.forms.contact_type_default');
        }

        $types[self::TYPE_TELEGRAM] = Yii::_t('users.forms.contact_type_telegram');
        $types[self::TYPE_SKYPE] = Yii::_t('users.forms.contact_type_skype');

        return $types;
    }

    /**
     * @param UserContact $contact
     * @return bool
     */
    public static function existIdentical(UserContact $contact)
    {
        return static::find()
            ->andWhere([
                'user_id' => $contact->user_id,
                'type' => $contact->type,
                'data' => $contact->data,
                'is_deleted' => $contact->is_deleted,
            ])
            ->exists();
    }

    /**
     * @param int $userId
     * @param array $exceptIds
     */
    public static function markAsDeleted($userId, $exceptIds = [])
    {
        /** @var UserContact $models */
        $models = static::find()
            ->andWhere(['user_id' => $userId])
            ->andFilterWhere(['not in', 'id', $exceptIds])
            ->all();

        foreach ($models as $model) {
            $model->setDeleted();
            $model->save();
        }
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'data'], 'required'],
            [['user_id', 'type', 'is_deleted'], 'integer'],
            ['type', 'in', 'range' => array_keys(static::getTypes())],
            [['data'], 'filter', 'filter' => 'strtolower'],
            [['data'], 'string'],
            [['data'], 'validateData'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            self::SCENARIO_PARTNER => ['type', 'data'],
        ]);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::_t('users.forms.id'),
            'user_id' => Yii::_t('users.forms.user_id'),
            'type' => Yii::_t('users.forms.contact_type'),
            'data' => Yii::_t('users.forms.contact_data'),
            'is_deleted' => Yii::_t('users.forms.contact_is_deleted'),
            'created_at' => Yii::_t('users.forms.created_at'),
            'updated_at' => Yii::_t('users.forms.updated_at'),
        ];
    }

    /**
     * @return string
     */
    public function getTypeLabel()
    {
        return ArrayHelper::getValue(self::getTypes(), $this->type);
    }

    /**
     * @return string
     */
    public function getBuiltData()
    {
        switch ($this->type) {
            case self::TYPE_TELEGRAM:
                return $this->buildTelegramUrl();
                break;
            case self::TYPE_SKYPE:
                return $this->buildSkypeUrl();
                break;
        }

        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     *
     */
    public function validateData($attribute)
    {
        switch ($this->type) {
            case self::TYPE_TELEGRAM:
                $this->validateTelegram($attribute);
                break;
            case self::TYPE_SKYPE:
                $this->validateSkype($attribute);
                break;
        }
    }

    /**
     *
     */
    public function setDeleted()
    {
        $this->is_deleted = 1;

        return $this;
    }

    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param string $attribute
     * @return string
     */
    public function getFormFieldName($attribute)
    {
        return sprintf("contacts[%s][%s]value", $this->id, $attribute);
    }

    /**
     * @param $attribute
     */
    protected function validateTelegram($attribute)
    {
        if (mb_strlen($this->$attribute) > 0 && $this->$attribute[0] !== '@') {
            $this->addError($attribute, Yii::_t('users.forms.telegram_validate_first_cymbol_error'));
            return;
        }
        if (preg_match('/^.{6,33}$/', $this->$attribute) === 0) {
            $this->addError($attribute, Yii::_t('users.forms.length_username_error', [6, 33]));
            return;
        }
        if (preg_match('/^@[a-z0-9_]{5,32}$/', $this->$attribute) === 0) {
            $this->addError($attribute, Yii::_t('users.forms.telegram_contact_invalid_username'));
        }
    }

    /**
     * @param $attribute
     */
    protected function validateSkype($attribute)
    {
        if (preg_match('/^.{6,32}$/', $this->$attribute) === 0) {
            $this->addError($attribute, Yii::_t('users.forms.length_username_error', [6, 32]));
            return;
        }
        if (preg_match('/^[a-z][a-z0-9\.,\-_]{5,31}$/', $this->$attribute) === 0) {
            $this->addError($attribute, Yii::_t('users.forms.skype_invalid_username_error'));
        }
    }

    /**
     * @return string
     */
    protected function buildTelegramUrl()
    {
        return sprintf('https://t.me/%s', ltrim($this->data, '@'));
    }

    /**
     * @return string
     */
    protected function buildSkypeUrl()
    {
        return sprintf('skype:%s?chat', $this->data);
    }
}