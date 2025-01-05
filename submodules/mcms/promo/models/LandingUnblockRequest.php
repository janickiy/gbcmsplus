<?php

namespace mcms\promo\models;

use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\components\events\LandingUnblockRequestCreated;
use mcms\promo\components\events\LandingDisabled;
use mcms\promo\components\events\LandingUnlocked;
use mcms\promo\components\events\LandingUpdated;
use Yii;
use mcms\user\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use mcms\common\traits\Translate;

/**
 * This is the model class for table "{{%landing_unblock_requests}}".
 *
 * @property integer $id
 * @property string $traffic_type
 * @property string $description
 * @property string $reject_reason
 * @property integer $status
 * @property integer $landing_id
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Landing $landing
 * @property User $user
 */
class LandingUnblockRequest extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.landing_unblock_requests.';

  const STATUS_MODERATION = 0;
  const STATUS_UNLOCKED = 1;
  const STATUS_DISABLED = 2;

  const SCENARIO_PARTNER_CREATE = 'partner_create';
  const SCENARIO_REPLACE_BY_SYNC = 'replace_by_sync';

  /**
   * @var
   */
  public $findUser;
  /**
   * @var
   */
  public $findLanding;
  /**
   * @var
   */
  public $createdFrom;
  /**
   * @var
   */
  public $createdTo;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%landing_unblock_requests}}';
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
   * tricky: новые правила продублировать в модель UnblockRequest
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['landing_id', 'user_id', 'status'], 'required'],
      ['description', 'required', 'except' => self::SCENARIO_REPLACE_BY_SYNC],

      ['traffic_type', 'required', 'on' => self::SCENARIO_PARTNER_CREATE],
      [['description', 'reject_reason'], 'string'],
      [['status', 'landing_id', 'user_id'], 'integer'],
      [['landing_id'], 'exist', 'skipOnError' => true, 'targetClass' => Landing::class, 'targetAttribute' => ['landing_id' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      ['traffic_type', 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'traffic_type',
      'reject_reason',
      'description',
      'status',
      'landing_id',
      'user_id',
      'created_at',
      'updated_at',
      'findUser',
      'findLanding',
      'createdFrom',
      'createdTo',
      'countries',
      'operators'
    ]);
  }

  /**
   * @param $attributes
   * @return array
   */
  public function translateAttributeLabels($attributes)
  {
    $translated = [];
    foreach ($attributes as $attribute) {
      $translated[$attribute] = self::translate('attribute-' . $attribute);
    }
    return $translated;
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
  public function getUser()
  {
    return $this->hasOne(User::class, ['id' => 'user_id']);
  }


  /**
   * @param null $status
   * @return array|mixed
   */
  public static function getStatuses($status = null)
  {
    $list = [
      self::STATUS_UNLOCKED => self::translate('status-unlocked'),
      self::STATUS_MODERATION => self::translate('status-moderation'),
      self::STATUS_DISABLED => self::translate('status-disabled'),
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status) : $list;
  }

  /**
   * @return array|mixed
   */
  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }

  /**
   * @return array|mixed
   */
  public function getTrafficTypes()
  {
    return ArrayHelper::map(TrafficType::find()->each(), 'id', 'name');
  }

  /**
   * @return string
   */
  public function getLandingLink()
  {
    return $this->landing->getViewLink();
  }

  /**
   * @return array
   */
  static function getStatusColors()
  {
    return [
      self::STATUS_MODERATION => 'warning',
      self::STATUS_UNLOCKED => 'success',
      self::STATUS_DISABLED => 'danger',
    ];
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return \mcms\common\helpers\Link::get(
      '/users/users/view',
      ['id' => $this->user_id],
      ['data-pjax' => 0],
      $this->user->getStringInfo(),
      false
    );
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {

    $this->invalidateCache();

    parent::afterSave($insert, $changedAttributes);
    if ($insert && $this->status == self::STATUS_MODERATION) {
      (new LandingUnblockRequestCreated($this))->trigger();
      return ;
    }

    $oldStatus = ArrayHelper::getValue($changedAttributes, 'status', false);
    // одобрили
    if ($oldStatus !== false && $this->status == self::STATUS_UNLOCKED) {
      (new LandingUnlocked($this))->trigger();
      return ;
    }

    // запретили
    if ($oldStatus !== false && $this->status == self::STATUS_DISABLED) {
      (new LandingDisabled($this))->trigger();
      return ;
    }

    (new LandingUpdated($this->landing))->trigger();
  }

  public function afterFind()
  {
    $this->traffic_type = $this->traffic_type ? json_decode($this->traffic_type) : '';
    parent::afterFind();
  }

  public function beforeSave($insert)
  {
    $this->traffic_type = $this->traffic_type ? json_encode($this->traffic_type) : '';
    return parent::beforeSave($insert);
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    /** @var User $user */
    $user = $this->getUser()->one();

    /** @var Landing $landing */
    $landing = $this->getLanding()->one();

    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => self::translate('replacement-id')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => self::translate('replacement-status')
        ]
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $user->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => self::translate('replacement-user')
        ]
      ],
      'description' => [
        'value' => $this->isNewRecord ? null : $this->description,
        'help' => [
          'label' => self::translate('replacement-description')
        ]
      ],
      'traffic_type' => [
        'value' => $this->isNewRecord ? null : $this->traffic_type,
        'help' => [
          'label' => self::translate('replacement-traffic_type')
        ]
      ],
      'reject_reason' => [
        'value' => $this->isNewRecord ? null : $this->reject_reason,
        'help' => [
          'label' => self::translate('replacement-reject_reason')
        ]
      ],
      'landing' => [
        'value' => $this->isNewRecord ? null : $landing->getReplacements(),
        'help' => [
          'class' => Landing::class,
          'label' => self::translate('replacement-landing')
        ]
      ],
    ];
  }

  /**
   * @return bool
   */
  public function isStatusModeration()
  {
    return $this->status == self::STATUS_MODERATION;
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status == self::STATUS_DISABLED;
  }

  /**
   * @param string|null $description При смене статуса можно передать описание
   * @return $this
   */
  public function setUnlocked($description = null)
  {
    if ($description !== null) {
      $this->description = $description;
    }
    $this->status = self::STATUS_UNLOCKED;
    return $this;
  }

  /**
   * @param string|null $description При смене статуса можно передать описание
   * @return $this
   */
  public function setDisabled($description = null)
  {
    if ($description !== null) {
      $this->description = $description;
    }
    $this->status = self::STATUS_DISABLED;
    return $this;
  }

  public static function findByLanding($landingId)
  {
    return LandingUnblockRequest::findOne([
      'user_id' => Yii::$app->user->id,
      'landing_id' => $landingId
    ]);
  }

  public static function isCreated($landingId)
  {
    return LandingUnblockRequest::find()->where([
      'user_id' => Yii::$app->user->id,
      'landing_id' => $landingId
    ])->count();
  }

  protected function invalidateCache()
  {
    $sources = (new Query())
      ->select(['s.id', 'hash'])
      ->from(Source::tableName() . ' s')
      ->leftJoin(SourceOperatorLanding::tableName() . ' sol', 's.id = sol.source_id')
      ->where([
        'sol.landing_id' => $this->landing_id,
        's.user_id' => $this->user_id,
        's.status' => Source::STATUS_APPROVED
      ])
      ->groupBy('source_id')
      ->each();

    foreach ($sources as $source) {
      ApiHandlersHelper::clearCache('SourceData' . ArrayHelper::getValue($source, 'hash'));
      ApiHandlersHelper::clearCache('SourceDataById' . ArrayHelper::getValue($source, 'id'));
      ApiHandlersHelper::clearCache('SourceLandingIdsGroupByOperator' . ArrayHelper::getValue($source, 'id'));
    }
    ApiHandlersHelper::clearCache('LandingRequestIsApproved' . $this->user_id . '_' . $this->landing_id);
  }

}
