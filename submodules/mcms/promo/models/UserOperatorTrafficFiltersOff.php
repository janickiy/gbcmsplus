<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\components\ApiHandlersHelper;
use mcms\user\models\User;
use Yii;

/**
 * This is the model class for table "user_operator_traffic_filters_off".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $operator_id
 *
 * @property Operator $operator
 * @property User $user
 */
class UserOperatorTrafficFiltersOff extends \yii\db\ActiveRecord
{
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'user_operator_traffic_filters_off';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'operator_id'], 'required'],
      [['user_id', 'operator_id'], 'integer'],
      [['user_id', 'operator_id'], 'unique', 'targetAttribute' => ['user_id', 'operator_id'], 'message' => 'The combination of User ID and Operator ID has already been taken.'],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => Yii::_t('promo.user_operator_traffic_filters_off.user_id'),
      'operator_id' => Yii::_t('promo.user_operator_traffic_filters_off.operator_id'),
    ];
  }

  public function afterDelete()
  {
    $this->invalidateCache($this->user_id, $this->operator_id);
    parent::afterDelete();
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    $this->invalidateCache($this->user_id, $this->operator_id);
    if (!$insert) {
      $userId = ArrayHelper::getValue($changedAttributes, 'user_id', $this->user_id);
      $operatorId = ArrayHelper::getValue($changedAttributes, 'operator_id', $this->operator_id);
      $this->invalidateCache($userId, $operatorId);
    }

    parent::afterSave($insert, $changedAttributes);
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
   * Очистка кеша
   * @param $userId
   * @param $operatorId
   */
  protected function invalidateCache($userId, $operatorId)
  {
    $cacheKey = sprintf('UserOperatorTrafficFiltersOff_%d_%d', $userId, $operatorId);
    ApiHandlersHelper::clearCache($cacheKey);
  }
}
