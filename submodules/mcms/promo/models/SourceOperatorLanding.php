<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use Yii;

/**
 * This is the model class for table "{{%sources_operator_landings}}".
 *
 * @property integer $id
 * @property integer $source_id
 * @property integer $profit_type
 * @property integer $operator_id
 * @property integer $landing_id
 * @property integer $is_changed
 * @property string $change_description
 * @property integer $landing_choose_type
 * @property float $rating
 *
 * @property Landing $landing
 * @property Operator $operator
 * @property Source $source
 * @property LandingOperator $landingOperator
 */
class SourceOperatorLanding extends \yii\db\ActiveRecord
{
  use Translate;
  const LANG_PREFIX = 'promo.sources_operator_landings.';

  const PROFIT_TYPE_REBILL = 1;
  const PROFIT_TYPE_BUYOUT = 2;

  const IS_CHANGED = 1;
  const IS_NOT_CHANGED = 0;

  const LANDING_CHOOSE_TYPE_MANUAL = 0;
  const LANDING_CHOOSE_TYPE_AUTO = 1;
  const SCENARIO_ADMIN_UPDATE = 'admin-update';

  public $_isUniqueFormName = false;

  /** @var bool $_landingHasUnlockedUnblockedRequest Лендинг имеет одобренную заявку на разблокировку */
  private $_landingHasUnlockedUnblockedRequest;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%sources_operator_landings}}';
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      static::SCENARIO_ADMIN_UPDATE => ['operator_id', 'landing_id', 'source_id'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['source_id', 'operator_id', 'landing_id'], 'required'],
      [['source_id', 'profit_type', 'operator_id', 'landing_id', 'is_changed', 'landing_choose_type'], 'integer'],
      [['rating'], 'number'],
      ['rating', 'default', 'value' => 0],
      [['change_description'], 'string'],
      [
        ['source_id', 'operator_id', 'landing_id'],
        'unique',
        'targetAttribute' => ['source_id', 'operator_id', 'landing_id'],
        'message' => 'The combination of Source ID, Operator ID and Landing ID has already been taken.',
      ],
      [
        ['landing_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Landing::class,
        'targetAttribute' => ['landing_id' => 'id']
      ],
      [
        ['operator_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Operator::class,
        'targetAttribute' => ['operator_id' => 'id']
      ],
      [
        ['source_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => Source::class,
        'targetAttribute' => ['source_id' => 'id']
      ],
      ['source_id', 'validateSource'],
      ['landing_id', 'validateLanding'],
      ['is_disable_handled', 'boolean']
    ];
  }

  /**
   * @param $attribute
   * @param $params
   */
  public function validateLanding($attribute, $params)
  {
    if ($this->landing->status == Landing::STATUS_INACTIVE && $this->isNewRecord) {
      $this->addError($attribute, Yii::_t('landings.landing_inactive'));
    }
  }

  /**
   * @param $attribute
   * @param $params
   */
  public function validateSource($attribute, $params)
  {
    // Если в источнике включена авторотация, то нельзя редактировать вручную
    if ($this->source->isAutoRotationEnabled()) {
      $this->addError($attribute, Yii::_t('sources_operator_landings.can_not_edit_landing_in_source'));
    }
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'source_id',
      'profit_type',
      'operator_id',
      'landing_id',
      'is_changed',
      'change_description',
      'landing_choose_type',
      'rating',
    ]);
  }

  /**
   * @return bool
   */
  public function beforeDelete()
  {
    if (!parent::beforeDelete()) {
      return false;
    }

    // Если включена авторотация лендов, то удалять вручную нельзя
    return !$this->source->isAutoRotationEnabled();
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
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandingOperator()
  {
    return $this->hasOne(LandingOperator::class, ['operator_id' => 'operator_id', 'landing_id' => 'landing_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSource()
  {
    return $this->hasOne(Source::class, ['id' => 'source_id']);
  }

  public static function getProfitTypes($value = null)
  {
    $list = [
      self::PROFIT_TYPE_BUYOUT => self::translate('profit_type_buyout'),
      self::PROFIT_TYPE_REBILL => self::translate('profit_type_rebill'),
    ];
    return isset($value) ? ArrayHelper::getValue($list, $value, null) : $list;
  }

  public function getProfitTypeName()
  {
    return $this->profit_type ? $this->getProfitTypes($this->profit_type) : null;
  }

  static public function findOrCreateModelForLink($sourceId, $operatorId, $landingId)
  {
    $params = [
      'source_id' => $sourceId,
      'operator_id' => $operatorId,
      'landing_id' => $landingId
    ];

    $found = self::findOne($params);
    return $found ? : new self($params );
  }

  static public function findOrCreateModel($source_id, $operator_id, $landing_id)
  {
    return $source_id && $operator_id && $landing_id && ($model = self::findOne([
      'source_id' => $source_id,
      'operator_id' => $operator_id,
      'landing_id' => $landing_id])
    ) ? $model : new self([
      'source_id' => $source_id,
      'operator_id' => $operator_id,
      'landing_id' => $landing_id
    ]);
  }

  public function getLandings($activeOnly = true, $withLandings = null)
  {
    $landings = Landing::find()->orderBy('name');
    if ($activeOnly) $landings->where('status = :active_status', [':active_status' => Landing::STATUS_ACTIVE]);
    if ($withLandings) {
      $landingsIds = array_map(function($land) { return $land->landing_id; }, $withLandings);
      $landings->orWhere(['id' => $landingsIds]);
    }
    return ArrayHelper::map($landings->each(), 'id', 'name');
  }

  public static function getAttributesArray($except = [])
  {
    return array_keys((new SourceOperatorLanding())->getAttributes(null, $except));
  }

  public static function clearDisableHandled(array $condition)
  {
    return static::updateAll([
      'is_disable_handled' => 0
    ], $condition);
  }

  /**
   * Лендинг по запросу и имеет одобренную заявку на разблокировку
   * @return bool
   */
  public function isLandingUnblocked()
  {
    return !$this->landing->isNormal() &&
      $this->landing->isEnabled() &&
      $this->landingHasUnlockedUnblockedRequest();
  }

  /**
   * Лендинг по запросу и не имеет одобренной заявки на разблокировку
   * @return bool
   */
  public function isLandingBlocked()
  {
    return !$this->landing->isNormal() &&
    $this->landing->isEnabled() &&
    !$this->landingHasUnlockedUnblockedRequest();
  }

  /**
   * Лендинг имеет одобренную заявку на разблокировку
   * @return bool
   */
  private function landingHasUnlockedUnblockedRequest()
  {
    if ($this->_landingHasUnlockedUnblockedRequest) return $this->_landingHasUnlockedUnblockedRequest;
    $this->_landingHasUnlockedUnblockedRequest = $this->landing->getLandingUnblockRequest()->andWhere([
      'user_id' => $this->source->user_id,
      'status' => LandingUnblockRequest::STATUS_UNLOCKED
    ])->exists();

    return $this->_landingHasUnlockedUnblockedRequest;
  }

  /**
   * Получить запрос на разблокировку лендинга от текущего юзера
   * @return LandingUnblockRequest
   */
  public function getLandingUnblockRequest()
  {
    $model = $this->landing->getLandingUnblockRequest()->andWhere([
      'user_id' => $this->source->user_id
    ])->one();

    if ($model === null) {
      $model = new LandingUnblockRequest([
        'landing_id' => $this->landing_id,
        'user_id' => $this->source->user_id,
      ]);
    }
    return $model;
  }
}
