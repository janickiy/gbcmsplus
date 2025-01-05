<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\promo\Module;
use Yii;

/**
 * This is the model class for table "banner_template_attributes".
 *
 * @property integer $id
 * @property integer $type
 * @property string $code
 * @property string $name
 * @property integer $template_id
 *
 * @property BannerAttributeValue[] $bannerAttributeValues
 * @property BannerTemplate $template
 */
class BannerTemplateAttribute extends MultiLangModel
{

  const TYPE_INPUT = 1;
  const TYPE_TEXTAREA = 2;
  const TYPE_IMAGE = 3;

  use Translate;

  const LANG_PREFIX = 'promo.banner-templates.attribute-';

  /**
   * @var array
   */
  public static $types = [
    self::TYPE_INPUT,
    self::TYPE_TEXTAREA,
    self::TYPE_IMAGE,
  ];


  /** @var   */
  public $file;
  /** @var   */
  public $entities;
  /** @var   */
  public $index;


  public function getMultilangAttributes()
  {
    return ['name'];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'banner_template_attributes';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['type', 'code', 'template_id'], 'required'],
      [['type', 'template_id'], 'integer'],
      [['code'], 'string', 'max' => 255],
      [['template_id', 'code'], 'unique', 'targetAttribute' => ['template_id', 'code'], 'message' => BannerTemplate::translate('code_unique')],
      [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => BannerTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'type',
      'code',
      'name',
      'template_id',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getBannerAttributeValues()
  {
    return $this->hasMany(BannerAttributeValue::class, ['attribute_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplate()
  {
    return $this->hasOne(BannerTemplate::class, ['id' => 'template_id']);
  }

  /**
   * @param $attributeId
   * @return BannerTemplateAttribute
   */
  static public function findOrCreateModel($attributeId)
  {
    return $attributeId && ($model = self::findOne($attributeId)) ? $model : new self();
  }

  /**
   * @param $templateId
   * @param null $id
   * @return array
   */
  public static function getModalLink($templateId, $id = null)
  {
    return [
      sprintf(
        '/%s/%s/%s',
        Module::getInstance()->id,
        'banner-templates',
        'attribute-modal'
      ),
      'templateId' => $templateId,
      'id' => $id
    ];
  }

  /**
   * @param string|null $filter
   * @return array
   */
  public function getTypesLabels($filter = null)
  {
    $result = []; foreach (self::$types as $type) {
    $result[$type] = self::translate('type-' . $type);
  }
    return $filter ? ArrayHelper::getValue($result, $filter) : $result;
  }
}
