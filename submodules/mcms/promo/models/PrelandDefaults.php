<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Translate;
use mcms\promo\components\queue\prelands\Payload;
use mcms\promo\components\queue\prelands\Worker;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "preland_defaults".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $operators
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type
 * @property integer $status
 * @property integer $stream_id
 * @property integer $source_id
 *
 * @property User $user
 * @property Stream $stream
 * @property Source $source
 */
class PrelandDefaults extends \yii\db\ActiveRecord
{

  use Translate;

  const LANG_PREFIX = 'promo.preland-defaults.';
  const OPERATOR_GLUE = ' | ';
  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;
  //тип преленда по умолчанию включен
  const TYPE_ADD = 1;
  //тип преленда по умолчанию выключен
  const TYPE_OFF = 0;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'preland_defaults';
  }

  /**
   * @inheritdoc
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
  public function rules()
  {
    return [
      [['user_id', 'status', 'stream_id', 'source_id', 'status'], 'integer'],
      [['type'], 'required'],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
      [['stream_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stream::class, 'targetAttribute' => ['stream_id' => 'id']],
      [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::class, 'targetAttribute' => ['source_id' => 'id']],
      [['user_id', 'stream_id', 'source_id', 'type'], 'checkUniqueConditions', 'skipOnEmpty' => false],
      [['operators'], 'safe'],
      [['operators'], 'atLeastOneRequiredValidator', 'params' => ['operators', 'user_id', 'stream_id', 'source_id'], 'skipOnEmpty' => false],
    ];
  }

  /**
   * @param $attribute
   * @param $params
   * @return bool
   */
  public function checkUniqueConditions($attribute, $params)
  {
    $existsQuery = self::find()
      ->where([
        'user_id' => $this->user_id ?: null,
        'stream_id' => $this->stream_id ?: null,
        'source_id' => $this->source_id ?: null,
        'type' => $this->type ?: null,
      ]);

    if (!$this->isNewRecord) {
      $existsQuery->andWhere(['<>', 'id', $this->id]);
    }

    $exists = $existsQuery->exists();

    if ($exists) {
      $this->addError($attribute, self::translate('unique_validate_fail'));
    }

    return !$exists;
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'user_id',
      'operators',
      'created_at',
      'updated_at',
      'status',
      'type',
      'stream_id',
      'source_id',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
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

  public function afterFind()
  {
    parent::afterFind();
    $this->operators = unserialize($this->operators);
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   * @throws \Exception
   */
  public function afterSave($insert, $changedAttributes)
  {
    try {
      Yii::$app->queue->push(
        Worker::CHANNEL_NAME,
        new Payload([
          'userId' => $this->user_id,
          'streamId' => $this->stream_id,
          'sourceId' => $this->source_id,
          'type' => $this->type,
        ])
      );
    } catch (\Exception $e) {
      Yii::error(Worker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
    }

    parent::afterSave($insert, $changedAttributes);
  }

  public function afterDelete()
  {
    try {
      Yii::$app->queue->push(
        Worker::CHANNEL_NAME,
        new Payload([
          'userId' => $this->user_id,
          'streamId' => $this->stream_id,
          'sourceId' => $this->source_id,
          'type' => $this->type,
        ])
      );
    } catch (\Exception $e) {
      Yii::error(Worker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
    }
    parent::afterDelete();
  }

  /**
   * @return null|string
   */
  public function getOperatorNames()
  {
    if (!$this->operators || !is_array($this->operators)) {
      return null;
    }

    $operatorsList = Operator::find()->indexBy('id')->all();
    $operators = array_map(function ($operatorId) use ($operatorsList) {
      return ArrayHelper::getValue($operatorsList, $operatorId);
    }, $this->operators);

    $links = array_map(function ($operator) {
      /** @var Operator $operator */
      return $operator->getViewLink();
    }, $operators);

    return implode(self::OPERATOR_GLUE, $links);
  }

  /**
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    if (!parent::beforeSave($insert)) {
      return false;
    }

    if ($this->operators) {
      $this->operators = serialize($this->operators);
    }

    if ($this->type == self::TYPE_OFF) {
      $this->stream_id = null;
      $this->user_id = null;
    }

    return true;
  }

  /**
   * Список статусов
   * @param integer $status
   * @return array|string
   */
  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_ACTIVE => Yii::_t('promo.preland-defaults.status-active'),
      self::STATUS_INACTIVE => Yii::_t('promo.preland-defaults.status-inactive'),
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status) : $list;
  }

  /**
   * Тип прелендов по умолчанию
   * @param integer $type
   * @return array|string
   */
  public function getTypes($type = null)
  {
    $list = [
      self::TYPE_ADD => Yii::_t('promo.preland-defaults.type-add'),
      self::TYPE_OFF => Yii::_t('promo.preland-defaults.type-off')
    ];
    return isset($type) ? ArrayHelper::getValue($list, $type) : $list;
  }


  /**
   * Текущий статус
   * @return string
   */
  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
  }


  /**
   * Текущий тип
   * @return string
   */
  public function getCurrentTypeName()
  {
    return $this->getTypes($this->type);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getStream()
  {
    return $this->hasOne(Stream::class, ['id' => 'stream_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSource()
  {
    return $this->hasOne(Source::class, ['id' => 'source_id']);
  }

  /**
   * Ссылка на простотр потока
   * @return null|string
   */
  public function getStreamLink()
  {
    return $this->stream ? $this->stream->getViewLink() : null;
  }

  /**
   * Ссылка на просмотр источника/ссылки
   * @return null|string
   */
  public function getSourceLink()
  {
    return $this->source ? $this->source->getViewLink() : null;
  }

  /**
   * Проверяет что заполнен хотя один из аттрибутов в $params
   * @param $attribute
   * @param $params
   */
  public function atLeastOneRequiredValidator($attribute, $params)
  {
    $chosen = 0;
    $attrLabels = [];

    foreach ($params as $attrName) {
      $attrLabels[] = $this->getAttributeLabel($attrName);
      $chosen += empty($this->$attrName) ? 0 : 1;
    }

    if (!$chosen) {
      $message = Yii::_t('promo.preland-defaults.at_least_one_validation', [
        'attributes' => implode(', ', $attrLabels),
      ]);
      foreach ($params as $attrName) {
        $this->addError($attrName, $message);
      }
    }
  }

  /**
   * Workaround for twig. Returns classes for coloring rows in grid
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
    return $this->status != self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status == self::STATUS_ACTIVE;
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
}