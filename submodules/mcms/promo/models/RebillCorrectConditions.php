<?php

namespace mcms\promo\models;

use mcms\common\helpers\Link;
use mcms\common\traits\Translate;
use mcms\common\helpers\ArrayHelper;
use mcms\promo\Module;
use Yii;
use mcms\user\models\User;
use mcms\promo\components\api\RebillCorrectConditions as RebillCorrectConditionsApi;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;

/**
 * This is the model class for table "rebill_correct_conditions".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $operator_id
 * @property integer $landing_id
 * @property string $percent
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Landing $landing
 * @property Provider $provider
 * @property Operator $operator
 * @property User $partner
 */
class RebillCorrectConditions extends \yii\db\ActiveRecord implements \JsonSerializable
{
  use Translate;

  const LANG_PREFIX = 'promo.rebill-correct-conditions.';

  const MIN_PERCENT = 0;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'rebill_correct_conditions';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['partner_id', 'operator_id', 'landing_id', 'created_by', 'created_at', 'updated_at'], 'integer'],
      [['percent', 'created_by'], 'required'],
      [['percent'], 'number', 'min' => self::MIN_PERCENT,
        'max' => Module::getInstance()->settings->getValueByKey(Module::SETTINGS_MAX_REBILL_CONDITIONS_PERCENT)],
      [['partner_id', 'operator_id', 'landing_id'], 'checkUniqueConditions', 'skipOnEmpty' => false],
      ['partner_id', function($attribute){
        /** @var \mcms\user\Module $usersModule */
        $usersModule = Yii::$app->getModule('users');
        $partnerRoles = ArrayHelper::getColumn($usersModule->api('rolesByUserId', ['userId' => $this->$attribute])->getResult(), 'name');
        if (!in_array($usersModule::PARTNER_ROLE, $partnerRoles)) {
          $this->addError($attribute, Yii::_t('promo.rebill-correct-conditions.user-is-not-partner'));
        }
      }],
      [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['partner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['partner_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'partner_id' => Yii::_t('promo.rebill-correct-conditions.attribute-partner_id'),
      'operator_id' => Yii::_t('promo.rebill-correct-conditions.attribute-operator_id'),
      'landing_id' => Yii::_t('promo.rebill-correct-conditions.attribute-landing_id'),
      'percent' => Yii::_t('promo.rebill-correct-conditions.attribute-percent'),
      'created_by' => Yii::_t('promo.rebill-correct-conditions.attribute-created_by'),
      'created_at' => Yii::_t('promo.rebill-correct-conditions.attribute-created_at'),
      'updated_at' => Yii::_t('promo.rebill-correct-conditions.attribute-updated_at'),
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class
    ];
  }

  /**
   * @param $attribute
   * @return bool
  */
  public function checkUniqueConditions($attribute)
  {
    $existsQuery = self::find()
      ->where(['partner_id' => $this->partner_id ?: null])
      ->andWhere(['landing_id' => $this->landing_id ?: null])
      ->andWhere(['operator_id' => $this->operator_id ?: null]);

    if (!$this->isNewRecord) {
      $existsQuery->andWhere(['<>', 'id', $this->id]);
    }

    if ($existsQuery->exists()) {
      $this->addError($attribute, self::translate('unique_validate_fail'));
      $this->addError('partner_id');
      $this->addError('landing_id');
      $this->addError('operator_id');
      return false;
    }
    return true;
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    $this->invalidateCache();
    parent::afterSave($insert, $changedAttributes);
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  protected function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [
      RebillCorrectConditionsApi::CACHE_KEY_PREFIX . 'partner_id' . $this->partner_id,
      RebillCorrectConditionsApi::CACHE_KEY_PREFIX . 'operator_id' . $this->operator_id,
      RebillCorrectConditionsApi::CACHE_KEY_PREFIX . 'landing_id' . $this->landing_id,
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProvider()
  {
    return $this->hasOne(Provider::class, ['id' => 'provider_id'])
      ->via('landing');
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
  public function getPartner()
  {
    return $this->hasOne(User::class, ['id' => 'partner_id']);
  }

  /**
   * @return null|string
   */
  public function getPartnerLink()
  {
    return $this->partner ? $this->partner->getViewLink() : null;
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
  public function getProviderLink()
  {
    return $this->provider ? $this->provider->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * @return float|null
   */
  public function getPercent()
  {
    return $this->percent === null ? null : floatval($this->percent);
  }

  /**
   * @inheritdoc
   */
  public function jsonSerialize()
  {
    $asArray = ArrayHelper::toArray($this);
    foreach($this->getRelatedRecords() AS $key => $related) {
      $asArray[$key] = ArrayHelper::toArray($related);
    }
    return $asArray;
  }
}