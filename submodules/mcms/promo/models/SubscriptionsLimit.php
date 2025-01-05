<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use mcms\user\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Ограничение объема подписок реселлерки.
 * В последнее время часто начали возникать ситуации,
 * когда на реселлерку назначаются ограничения на кол-по ПДП/сутки (как только лимит превышен, траф лить в ТБ)
 *
 * @property integer $id
 * @property integer $country_id
 * @property integer $operator_id
 * @property integer $user_id ID партнера
 * @property integer $subscriptions_limit Лимит подписок
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Operator $operator
 * @property User $user Партнер
 * @property Country $country
 */
class SubscriptionsLimit extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'promo.subscription_limits.';
  const MICROSERVICE_SUBS_LIMITS_TAG = 'SUBSLIMITSTAG';

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      BlameableBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'subscription_limits';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['country_id', 'subscriptions_limit'], 'required'],
      [['country_id', 'operator_id', 'user_id'], 'integer'],
      [['subscriptions_limit'], 'integer', 'max' => 100000000],
      ['operator_id', 'unique', 'targetAttribute' => ['country_id', 'operator_id', 'user_id']],
      [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_id' => 'id']],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      ['operator_id', 'checkOperatorCountry'],
    ];
  }

  public function checkOperatorCountry()
  {
    if (!$this->operator_id) {
      return;
    }

    $operatorModel = Operator::findOne((int)$this->operator_id);

    if ((int)$operatorModel->country_id !== (int)$this->country_id) {
      // внештатная ошибка, поэтому не стал пихать в переводы
      $this->addError('operator_id', 'Operator does not belong to the selected country.');
    }
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels(array_keys($this->getAttributes()));
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCountry()
  {
    return $this->hasOne(Country::class, ['id' => 'country_id']);
  }

  /**
   * @return null|string
   */
  public function getUserLink()
  {
    return $this->user ? $this->user->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getCountryLink()
  {
    return $this->country ? $this->country->getViewLink() : null;
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    ApiHandlersHelper::invalidateTags(static::MICROSERVICE_SUBS_LIMITS_TAG);
  }

  public function afterDelete()
  {
    parent::afterDelete();

    ApiHandlersHelper::invalidateTags(static::MICROSERVICE_SUBS_LIMITS_TAG);
  }
}
