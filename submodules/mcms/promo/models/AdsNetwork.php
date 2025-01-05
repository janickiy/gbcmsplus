<?php

namespace mcms\promo\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\promo\components\events\ads_networks\AdsNetworkCreated;
use mcms\promo\components\events\ads_networks\AdsNetworkUpdated;
use mcms\promo\components\events\ads_networks\AdsNetworkDeleted;

/**
 * This is the model class for table "{{%countries}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $label1
 * @property string $label2
 * @property string $description1
 * @property string $description2
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Operator[] $operator
 * @property Operator[] $activeOperator
 */
class AdsNetwork extends MultiLangModel
{
  use Translate;

  const LANG_PREFIX = 'promo.ads-networks.';

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

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
    return '{{%ads_networks}}';
  }

  /**
   * @inheritdoc
   */
  public function getMultilangAttributes()
  {
    return ['description1', 'description2'];
  }


  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name', 'status', 'label1'], 'required'],
      [['status'], 'integer'],
      [['name', 'label1', 'label2'], 'string', 'max' => 50],
      [['description1', 'description2'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['description1', 'description2'], 'validateArrayString'],
      ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'name',
      'status',
      'label1',
      'label2',
      'description1',
      'description2',
      'created_at',
      'updated_at',
    ]);
  }

  public function getStatuses($status = null)
  {
    $list = [
      self::STATUS_INACTIVE => Yii::_t('promo.ads-networks.status-inactive'),
      self::STATUS_ACTIVE => Yii::_t('promo.ads-networks.status-active')
    ];
    return isset($status) ? ArrayHelper::getValue($list, $status, null) : $list;
  }


  public function getCurrentStatusName()
  {
    return $this->getStatuses($this->status);
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

  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => self::translate('replacement-id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => self::translate('replacement-name')
        ]
      ],
      'label1' => [
        'value' => $this->isNewRecord ? null : $this->label1,
        'help' => [
          'label' => self::translate('replacement-label1')
        ]
      ],
      'label2' => [
        'value' => $this->isNewRecord ? null : $this->label2,
        'help' => [
          'label' => self::translate('replacement-label2')
        ]
      ],
      'status' => [
        'value' => $this->isNewRecord ? null : $this->getCurrentStatusName(),
        'help' => [
          'label' => self::translate('replacement-status')
        ]
      ],
    ];
  }

  public function afterSave($insert, $changedAttributes)
  {
    if ($insert) {
      (new AdsNetworkCreated($this))->trigger();
    } else {
      (new AdsNetworkUpdated($this))->trigger();
    }

    parent::afterSave($insert, $changedAttributes);
  }

  public function afterDelete()
  {
    (new AdsNetworkDeleted($this))->trigger();

    parent::afterDelete();
  }

}
