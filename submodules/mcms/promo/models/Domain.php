<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\traits\Translate;
use mcms\common\validators\UrlValidator;
use mcms\common\web\Response;
use mcms\promo\components\domains\DomainsResponse;
use mcms\promo\components\DomainsHelper;
use mcms\promo\components\events\DomainAdded;
use mcms\promo\components\events\DomainChanged;
use mcms\promo\Module;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%domains}}".
 *
 * @property integer $id
 * @property string $url
 * @property integer $status
 * @property integer $user_id
 * @property integer $type
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $is_system
 *
 * @property User $createdBy
 * @property User $user
 * @property Source[] $sources
 */
class Domain extends \yii\db\ActiveRecord
{

  use Translate;

  /**
   *
   */
  const STATUS_INACTIVE = 0;
  /**
   *
   */
  const STATUS_ACTIVE = 1;
  /**
   *
   */
  const STATUS_BANNED = 2;

  /**
   *
   */
  const TYPE_NORMAL = 1;
  /**
   *
   */
  const TYPE_PARKED = 2;

  const IS_SYSTEM_KEY_SYSTEM = 'system';
  const IS_SYSTEM_KEY_PARKED = 'parked';

  const SCENARIO_PARTNER_PARK = 'park';
  const SCENARIO_PARTNER_REGISTER = 'register';

  /**
   * @return array
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
  const LANG_PREFIX = 'promo.domains.';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%domains}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['type'], 'default', 'value' => self::TYPE_NORMAL, 'skipOnEmpty' => false],
      [['created_by'], 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['url', 'user_id', 'created_by', 'type', 'status'], 'required'],
      [['status', 'user_id', 'type', 'created_by', 'is_system'], 'integer'],
      [['url'], 'string', 'max' => 255],
      [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['url'], 'checkBalance', 'on' => self::SCENARIO_PARTNER_REGISTER],
      [['type'], 'compare', 'compareValue' => self::TYPE_NORMAL, 'on' => self::SCENARIO_PARTNER_REGISTER], // внештатная ситуация но всё же
      [['url'], 'filter', 'filter' => function($value) {
        return parse_url($value, PHP_URL_SCHEME) ? $value : 'http://' . $value;
      }],
      [['url'], UrlValidator::class, 'enableIDN' => true],
      [['url'], 'checkDomainCorrect', 'on' => [self::SCENARIO_PARTNER_REGISTER]],
      [['url'], 'unique', 'targetClass' => self::class, 'targetAttribute' => ['domain_name' => 'domain_name']]
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'url',
      'status',
      'user_id',
      'type',
      'created_by',
      'created_at',
      'updated_at',
      'is_system',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_PARTNER_PARK => ['url', 'type'],
      self::SCENARIO_PARTNER_REGISTER => ['url', 'type'],
    ]);
  }

  public function checkBalance($attribute)
  {
    if (!DomainsHelper::isEnoughBalanceToRegister()){
      $this->addError($attribute, Yii::_t('promo.domains.not_enough_balance'));
      return false;
    }
    return true;
  }

  public function checkDomainCorrect($attribute)
  {
    $check = explode('.', $this->$attribute);
    $domainZone = substr($check[1], 0, -1);

    if (
      count($check) !== 2 ||
      !preg_match("#([A-z0-9]([A-z0-9\-]{0,61}[A-z0-9])?\.)+[A-z]{2,6}#i", $this->$attribute) ||
      !in_array($domainZone, Module::getInstance()->acceptedDomainZones)
    ) {
      $this->addError($attribute, Yii::_t('promo.domains.wrong_address'));
      return false;
    }

    return true;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCreatedBy()
  {
    return $this->hasOne(User::class, ['id' => 'created_by']);
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
  public function getSource()
  {
    return $this->hasMany(Source::class, ['domain_id' => 'id']);
  }

  /**
   * @param null $status
   * @return array|mixed
   */
  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => self::translate('status-inactive'),
      self::STATUS_ACTIVE => self::translate('status-active'),
      self::STATUS_BANNED => self::translate('status-banned'),
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
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
  public function getIsSystemName()
  {
    return $this->is_system ? Yii::_t('sources.yes') : Yii::_t('sources.no');
  }

  public function isSystem()
  {
    return !!$this->is_system;
  }

  /**
   * @return array
   */
  static function getStatusColors()
  {
    return [
      self::STATUS_BANNED => 'danger',
      self::STATUS_ACTIVE => 'success',
      self::STATUS_INACTIVE => 'warning',
    ];
  }

  /**
   * @param null $type
   * @return array|mixed
   */
  public function getTypes($type = null)
  {
    $list = [
      self::TYPE_PARKED => self::translate('type-parked'),
      self::TYPE_NORMAL => self::translate('type-normal'),
    ];
    return isset($type) ? ArrayHelper::getValue($list, $type, null) : $list;
  }


  /**
   * @return array|mixed
   */
  public function getCurrentTypeName()
  {
    return $this->getTypes($this->type);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    $insert
      ? (new DomainAdded($this))->trigger()
      : (new DomainChanged($this))->trigger()
    ;

    parent::afterSave($insert, $changedAttributes);
  }

  public function getReplacements()
  {
    /** @var User $createdBy */
    $createdBy = $this->getCreatedBy()->one();
    /** @var User $related */
    $related = $this->getUser()->one();

    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-id')
        ]
      ],
      'url' => [
        'value' => $this->isNewRecord ? null : $this->url,
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-url')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-status')
        ]
      ],
      'type' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentTypeName(),
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-type')
        ]
      ],
      'is_system' => [
        'value' => $this->isNewRecord ? null : $this->is_system,
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-is_system')
        ]
      ],
      'createdBy' => [
        'value' => $this->isNewRecord ? null : $createdBy->getReplacements(),
        'help' => [
          'label' => Yii::_t('promo.domains.replacement-createdBy'),
          'class' => Yii::$app->user->identityClass
        ]
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $related->getReplacements(),
        'help' => [
          'class' => Yii::$app->user->identityClass,
          'label' => Yii::_t('promo.domains.replacement-user')
        ]
      ]
    ];
  }

  /**
   * @return string
   */
  public function getUserLink()
  {
    return Html::a(
      $this->user->getStringInfo(),
      ['/users/users/view', 'id' => $this->user_id],
      ['data-pjax' => 0],
      [],
      false
    );
  }

  /**
   * @return int
   */
  public function isActive()
  {
    return $this->status == self::STATUS_ACTIVE;
  }

  /**
   * @return int
   */
  public function isBanned()
  {
    return $this->status == self::STATUS_BANNED;
  }

  /**
   * return string
   */
  public function isSystemKey()
  {
    return $this->is_system ? self::IS_SYSTEM_KEY_SYSTEM : self::IS_SYSTEM_KEY_PARKED;
  }

  /**
   * Возвращает активный системный домен
   * @return Domain|null
   */
  public static function getActiveSystemDomain()
  {
    return static::findOne(['is_system' => true, 'status' => self::STATUS_ACTIVE]);
  }

  /**
   * @inheritdoc
   */
  public function beforeValidate()
  {
    $this->url = trim($this->url);
    $this->domain_name = parse_url($this->url, PHP_URL_HOST) ? : $this->url;

    if (!in_array($this->scenario, [self::SCENARIO_PARTNER_REGISTER, self::SCENARIO_PARTNER_PARK])) {
      return true;
    }

    $this->url = $this->addSchema();

    return parent::beforeValidate();
  }

  public function getActiveSystem()
  {
    return self::findOne(['is_system' => 1, 'status' => self::STATUS_ACTIVE]);
  }

  private function addSchema()
  {
    $url = $this->url;
    $url .= substr($url, -1) == '/' ? '' : '/';

    $schema = strpos($url, 'http://') !== false || strpos($url, 'https://') !== false
      ? ''
      : 'http://'
    ;

    return $schema . $url;
  }

  /**
   * @inheritDoc
   */
  public function afterFind()
  {
    parent::afterFind();
    $this->url = $this->addSchema();
  }

  /**
   * Активные домены пользователя
   * @param array $includeDomainIds
   * @return array
   */
  public static function getUserActiveDomainItems($userId, $includeDomainIds = [])
  {
    $records = static::find()->andWhere([
      'or',
      ['status' => static::STATUS_ACTIVE,  'user_id' => $userId,],
      ['status' => static::STATUS_ACTIVE,  'is_system' => 1,],
      ['id' => $includeDomainIds,]
    ]);

    return ArrayHelper::map($records->each(), 'id', 'domain_name');
  }
}
