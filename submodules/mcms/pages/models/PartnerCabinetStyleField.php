<?php

namespace mcms\pages\models;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "partner_cabinet_style_fields".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $css_selector
 * @property string $css_prop
 * @property integer $sort_css
 * @property string $default_value
 * @property integer $sort
 * @property integer $category_id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PartnerCabinetStyleValue $value
 * @property PartnerCabinetStyleCategory $category
 */
class PartnerCabinetStyleField extends MultiLangModel
{

  const LANG_PREFIX = 'pages.partner_cabinet_style_fields.';

  use Translate;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'partner_cabinet_style_fields';
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      BlameableBehavior::class,
    ];
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'css_selector' => false, // отключили фильтр для css_selector, т.к. .navbar-nav > li.dropdown
      'default_value' => '\yii\helpers\HtmlPurifier::process',
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['code', 'css_selector', 'css_prop', 'category_id', 'sort_css', 'sort'], 'required'],
      [['sort_css', 'sort', 'category_id'], 'integer'],
      [['sort_css', 'sort'], 'number', 'max' => 65535, 'min' => 0],
      [['code', 'css_selector'], 'string', 'max' => 255],
      [['css_prop'], 'string', 'max' => 128],
      [['code'], 'match', 'pattern' => '/^[a-z0-9_]*$/'],
      [['code'], 'unique'],
      [['category_id'],
        'exist',
        'skipOnError' => true,
        'targetClass' => PartnerCabinetStyleCategory::class,
        'targetAttribute' => ['category_id' => 'id']
      ],
      [['default_value'], 'safe'],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
    ];
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return ['name'];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'code',
      'name',
      'css_selector',
      'css_prop',
      'sort_css',
      'sort',
      'category_id',
      'default_value',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategory()
  {
    return $this->hasOne(PartnerCabinetStyleCategory::class, ['id' => 'category_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getValue()
  {
    return $this->hasOne(PartnerCabinetStyleValue::class, ['field_id' => 'id']);
  }
}