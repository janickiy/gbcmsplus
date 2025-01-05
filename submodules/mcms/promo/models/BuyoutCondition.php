<?php

namespace mcms\promo\models;

use mcms\user\models\User;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "buyout_conditions".
 *
 * @property integer $id
 * @property string $name
 * @property integer $operator_id
 * @property integer $landing_id
 * @property integer $user_id
 * @property integer $type
 * @property integer $buyout_minutes
 * @property integer $is_buyout_only_after_1st_rebill
 * @property integer $is_buyout_only_unique_phone
 * @property integer $is_buyout_all
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class BuyoutCondition extends \yii\db\ActiveRecord
{
  const TYPE_BUYOUT_MINUTES = 1;
  const TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL = 2;
  const TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE = 3;
  const TYPE_IS_BUYOUT_ALL = 4;

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      [
        'class' => BlameableBehavior::class,
        'updatedByAttribute' => false,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'buyout_conditions';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'operator_id', 'landing_id'], 'default', 'value' => 0],
      [['name', 'type'], 'required'],
      ['type', 'in', 'range' => [
        self::TYPE_BUYOUT_MINUTES,
        self::TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL,
        self::TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE,
        self::TYPE_IS_BUYOUT_ALL,
      ]],
      [['operator_id', 'landing_id', 'user_id', 'created_by', 'created_at', 'updated_at', 'buyout_minutes', 'is_buyout_only_after_1st_rebill', 'is_buyout_only_unique_phone', 'is_buyout_all'], 'integer'],
      [['name'], 'string', 'max' => 255],
      [['operator_id'], 'exist', 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id'], 'when' => function (self $model) {
        return !empty($model->operator_id);
      }],
      [['landing_id'], 'exist', 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id'], 'when' => function (self $model) {
        return !empty($model->landing_id);
      }],
      [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id'], 'when' => function (self $model) {
        return !empty($model->user_id);
      }],
      [['user_id', 'operator_id', 'landing_id'], 'checkAtLeastOneRequired'],
      ['name', 'unique'],
      ['operator_id', 'unique', 'targetAttribute' => ['operator_id', 'user_id', 'landing_id', 'type'], 'message' => Yii::_t('promo.buyout_conditions.validation-combination_exists')],
      ['buyout_minutes', 'required', 'when' => function (self $model) {
        return $model->type == self::TYPE_BUYOUT_MINUTES;
      }],
    ];
  }

  /**
   * @return bool
   */
  public function checkAtLeastOneRequired()
  {
    if (!empty($this->user_id) || !empty($this->operator_id) || !empty($this->landing_id)) {
      return true;
    }

    $this->addError('user_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('user_id')]));
    $this->addError('operator_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('operator_id')]));
    $this->addError('landing_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('landing_id')]));
    return false;
  }

  /**
   * @return bool
   */
  public function beforeValidate()
  {
    if ($this->isConditionByMinutes()) {
      $this->is_buyout_only_after_1st_rebill = null;
      $this->is_buyout_only_unique_phone = null;
      $this->is_buyout_all = null;
    }
    if ($this->isConditionByFirstRebill()) {
      $this->buyout_minutes = null;
      $this->is_buyout_only_unique_phone = null;
      $this->is_buyout_all = null;
    }
    if ($this->isConditionByUniquePhone()) {
      $this->is_buyout_only_after_1st_rebill = null;
      $this->buyout_minutes = null;
      $this->is_buyout_all = null;
    }
    if ($this->isConditionAll()) {
      $this->is_buyout_only_after_1st_rebill = null;
      $this->buyout_minutes = null;
      $this->is_buyout_only_unique_phone = null;
      $this->is_buyout_all = 1;
    }
    return parent::beforeValidate();
  }

  /**
   * @return bool
   */
  public function isConditionByMinutes()
  {
    return (int)$this->type === self::TYPE_BUYOUT_MINUTES;
  }

  /**
   * @return bool
   */
  public function isConditionByFirstRebill()
  {
    return (int)$this->type === self::TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL;
  }

  /**
   * @return bool
   */
  public function isConditionByUniquePhone()
  {
    return (int)$this->type === self::TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE;
  }

  /**
   * @return bool
   */
  public function isConditionAll()
  {
    return (int)$this->type === self::TYPE_IS_BUYOUT_ALL;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('promo.buyout_conditions.attribute-id'),
      'name' => Yii::_t('promo.buyout_conditions.attribute-name'),
      'operator_id' => Yii::_t('promo.buyout_conditions.attribute-operator_id'),
      'landing_id' => Yii::_t('promo.buyout_conditions.attribute-landing_id'),
      'user_id' => Yii::_t('promo.buyout_conditions.attribute-user_id'),
      'type' => Yii::_t('promo.buyout_conditions.attribute-type'),
      'value' => Yii::_t('promo.buyout_conditions.attribute-value'),
      'buyout_minutes' => Yii::_t('promo.buyout_conditions.attribute-buyout_minutes'),
      'is_buyout_only_after_1st_rebill' => Yii::_t('promo.buyout_conditions.attribute-is_buyout_only_after_1st_rebill'),
      'is_buyout_only_unique_phone' => Yii::_t('promo.buyout_conditions.attribute-is_buyout_only_unique_phone'),
      'created_by' => 'Created By',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery|null
   */
  public function getUser()
  {
    if ($this->user_id === null) {
      return null;
    }
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return null|string
   */
  public function getUserLink()
  {
    return $this->user ? $this->user->getViewLink() : null;
  }

  /**
   * @return \yii\db\ActiveQuery|null
   */
  public function getOperator()
  {
    if ($this->operator_id === null) {
      return null;
    }
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return \yii\db\ActiveQuery|null
   */
  public function getLanding()
  {
    if ($this->landing_id === null) {
      return null;
    }
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return null|string
   */
  public function getLandingLink()
  {
    return $this->landing ? $this->landing->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * Массив для селекта с выбором типа
   * @return array
   */
  public function getTypesList()
  {
    return [
      self::TYPE_BUYOUT_MINUTES => Yii::_t('promo.buyout_conditions.type-buyout_minutes'),
      self::TYPE_IS_BUYOUT_ONLY_AFTER_1ST_REBILL => Yii::_t('promo.buyout_conditions.type-is_buyout_only_after_1st_rebill'),
      self::TYPE_IS_BUYOUT_ONLY_UNIQUE_PHONE => Yii::_t('promo.buyout_conditions.type-is_buyout_only_unique_phone'),
      self::TYPE_IS_BUYOUT_ALL => Yii::_t('promo.buyout_conditions.type-is_buyout_all'),
    ];
  }

  /**
   * @return string
   */
  public function getTypeName()
  {
    return ArrayHelper::getValue($this->getTypesList(), $this->type);
  }

  /**
   * Получить значение по типу
   * @return int|null
   */
  public function getValue()
  {
    $result = null;
    if ($this->isConditionByMinutes()) {
      $result = $this->buyout_minutes;
    }
    if ($this->isConditionByFirstRebill()) {
      $result = $this->is_buyout_only_after_1st_rebill
      ? Yii::_t('app.common.Yes')
      : Yii::_t('app.common.No');
    }
    if ($this->isConditionByUniquePhone()) {
      $result = $this->is_buyout_only_unique_phone
        ? Yii::_t('app.common.Yes')
        : Yii::_t('app.common.No');
    }

    return $result;
  }
}
