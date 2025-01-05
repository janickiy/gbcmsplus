<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\helpers\Link;
use mcms\common\traits\Translate;
use mcms\promo\components\events\StreamChanged;
use mcms\promo\models\search\SourceSearch;
use Yii;
use mcms\user\models\User;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%streams}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Source[] $sources
 * @property User $user
 */
class Stream extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.streams.';

  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;

  const SCENARIO_ADMIN_EDIT = 'admin_edit';

  const DEFAULT_NAME = 'Default';
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
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{%streams}}';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['user_id', 'default', 'value' => isset(Yii::$app->user) ? Yii::$app->user->id : null],
      [['name', 'user_id', 'status'], 'required'],
      [['status', 'user_id'], 'integer'],
      [['name'], 'string', 'max' => 255],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['name'], 'uniqueName'],
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    return [
      self::SCENARIO_ADMIN_EDIT => ['name', 'status'],
      self::SCENARIO_DEFAULT => array_keys($this->getAttributes())
    ];
  }

  /**
   * @return bool
   */
  public function uniqueName()
  {
    if ($this->status != self::STATUS_ACTIVE) return true;

    if (self::findOne(['user_id' => $this->user_id, 'status' => self::STATUS_ACTIVE, 'name' => $this->name])){

      $this->addError('name', Yii::$app->getI18n()->format('{attribute} "{value}" has already been taken.', [
        'attribute' => $this->getAttributeLabel('name'),
        'value' => $this->name
      ], Yii::$app->language));

      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'name',
      'status',
      'user_id',
      'created_at',
      'createdFrom',
      'createdTo',
      'updated_at',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   * @deprecated
   */
  public function getSource()
  {
    return $this->hasMany(Source::class, ['stream_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSources()
  {
    return $this->getSource();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSourceCount()
  {
    return $this->getSource()->count();
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
  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_ACTIVE => self::translate('status-active'),
      self::STATUS_INACTIVE => self::translate('status-inactive'),
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
   * @return array
   */
  static function getStatusColors()
  {
    return [
      self::STATUS_INACTIVE => 'danger',
      self::STATUS_ACTIVE => 'success',
    ];
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status !== self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * @return $this
   */
  public function setEnabled()
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  /**
   * @return $this
   */
  public function setDisabled()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    (new StreamChanged($this))->trigger();

    parent::afterSave($insert, $changedAttributes);
  }

  public function getReplacements()
  {
    /** @var User $user */
    $user = $this->getUser()->one();
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('promo.streams.replacement-id')
        ]
      ],
      'user' => [
        'value' => $this->isNewRecord ? null : $user->getReplacements(),
        'help' => [
          'label' => Yii::_t('promo.streams.replacement-user'),
          'class' => Yii::$app->user->identityClass
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => Yii::_t('promo.streams.replacement-status')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('promo.streams.replacement-name')
        ]
      ],
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

  public static function getViewUrl($id, $asString = false)
  {
    $arr = ['/promo/streams/view', 'id' => $id];
    return $asString ? Url::to($arr) : $arr;
  }

  /**
   * Все потоки пользователя
   * @param int $user_id
   * @param bool $activeOnly
   * @return Stream[]
   */
  public static function getStreamsByUserId($user_id, $activeOnly = true)
  {
    $result = self::find(['user_id' => $user_id]);

    return $activeOnly
      ? $result->andWhere(['status' => self::STATUS_ACTIVE])->all()
      : $result->all();
  }

  public function getSourcesLink()
  {
    $formName = (new SourceSearch())->formName();
    return Link::get(
        'arbitrary-sources/index',
        [$formName . '[stream_id]' => $this->id, $formName . '[status]' => ''],
        ['data-pjax' => 0],
        Yii::_t('promo.arbitrary_sources.main')) . ' ' . Html::tag('span', $this->sourceCount, ['class' => 'label label-default']);
  }

  public function getStringInfo()
  {
    return sprintf(
      '#%s - %s',
      ArrayHelper::getValue($this, 'id'),
      ArrayHelper::getValue($this, 'name')
    );
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public static function getInactiveStreamsQuery()
  {
    return static::find()
      ->where(['<>', 'status', self::STATUS_ACTIVE])
      ;
  }

  /**
   * Ссылка на просмотр потока
   * @return string
   */
  public function getViewLink()
  {
    return Yii::$app->user->can('PromoViewOtherPeopleStreams') ? Link::get(
      '/promo/streams/view',
      ['id' => $this->id],
      ['data-pjax' => 0],
      $this->getStringInfo(),
      false
    ) : $this->getStringInfo();
  }
}