<?php

namespace mcms\promo\models;

use mcms\user\models\User;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "subscription_correct_conditions".
 *
 * @property integer $id
 * @property string $name
 * @property integer $operator_id
 * @property integer $landing_id
 * @property integer $user_id
 * @property integer $percent
 * @property integer $is_active
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property User $user
 * @property Landing $landing
 * @property Operator $operator
 */
class SubscriptionCorrectCondition extends \yii\db\ActiveRecord
{
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
    return 'subscription_correct_conditions';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'percent'], 'required'],
      ['name', 'unique'],
      [['name'], 'string', 'max' => 255],
      [['percent'], 'integer', 'min' => 0, 'max' => 100],
      [['operator_id', 'landing_id', 'user_id', 'percent', 'is_active'], 'integer'],
      [['operator_id'], 'exist', 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['landing_id'], 'exist', 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['user_id', 'operator_id', 'landing_id'], 'checkAtLeastOneRequired'],
      ['operator_id', 'unique', 'targetAttribute' => ['operator_id', 'user_id', 'landing_id'], 'message' => Yii::_t('promo.buyout_conditions.validation-combination_exists')],
    ];
  }

  /**
   * @return bool
   */
  public function checkAtLeastOneRequired()
  {
    if ($this->user_id || $this->operator_id || $this->landing_id) {
      return true;
    }

    $this->addError('user_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('user_id')]));
    $this->addError('operator_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('operator_id')]));
    $this->addError('landing_id', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => $this->getAttributeLabel('landing_id')]));
    return false;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('promo.correct-conditions.attribute-id'),
      'name' => Yii::_t('promo.correct-conditions.attribute-name'),
      'operator_id' => Yii::_t('promo.correct-conditions.attribute-operator_id'),
      'landing_id' => Yii::_t('promo.correct-conditions.attribute-landing_id'),
      'user_id' => Yii::_t('promo.correct-conditions.attribute-user_id'),
      'percent' => Yii::_t('promo.correct-conditions.attribute-percent'),
      'is_active' => Yii::_t('promo.correct-conditions.attribute-is_active'),
      'created_by' => 'Created By',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return ActiveQuery
   */
  public function getUser()
  {
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
   * @return ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * @return ActiveQuery|null
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return null|string
   */
  public function getLandingLink()
  {
    return $this->landing ? $this->landing->getViewLink() : null;
  }
}
