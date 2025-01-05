<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use mcms\user\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "traffic_blocks".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $provider_id
 * @property integer $operator_id
 * @property integer $is_blacklist
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $comment
 * @property User $user
 * @property Operator $operator
 * @property Provider $provider
 */
class TrafficBlock extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.traffic_block.';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'traffic_blocks';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'is_blacklist'], 'required'],
      ['operator_id', 'required', 'when' => function (TrafficBlock $model) {return empty($model->provider_id);}],
      ['provider_id', 'required', 'when' => function (TrafficBlock $model) {return empty($model->operator_id);}],
      [['user_id', 'operator_id', 'provider_id'], 'integer'],
      ['comment', 'string'],
      ['operator_id', 'unique', 'targetAttribute' => ['user_id', 'operator_id', 'is_blacklist'], 'message' => Yii::_t('promo.traffic_block.unique-user-operator-error')],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'provider_id',
      'operator_id',
      'user_id',
      'is_blacklist',
      'created_at',
      'updated_at',
      'comment',
    ]);
  }

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
   * @return \yii\db\ActiveQuery
   */
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return $this->user->editLink;
  }

  /**
   * @return string
   */
  public function getOperatorLink()
  {
    return ($operator = $this->operator) ? $operator->viewLink : null;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProvider()
  {
    return $this->hasOne(Provider::class, ['id' => 'provider_id']);
  }

  public function getProviderLink()
  {
    return ($provider = $this->provider) ? $provider->viewLink : null;
  }

  /**
   * @return array
   */
  public static function getIsBlacklistOptions()
  {
    return [
      0 => self::t('is_blacklist_false'),
      1 => self::t('is_blacklist_true'),
    ];
  }

  /**
   * Чистим кеш для микросервисов
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    Yii::$app->getModule('promo')->api('cachedCountries', ['userId' => $this->user_id])->invalidateCache();

    ApiHandlersHelper::clearTrafficBlockCache($this->user_id);
    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * Чистим кеш для микросервисов
   * @inheritdoc
   */
  public function afterDelete()
  {
    Yii::$app->getModule('promo')->api('cachedCountries', ['userId' => $this->user_id])->invalidateCache();
    ApiHandlersHelper::clearTrafficBlockCache($this->user_id);
    parent::afterDelete();
  }
}
