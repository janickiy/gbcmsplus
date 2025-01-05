<?php

namespace mcms\promo\models;

use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\promo\components\ApiHandlersHelper;
use mcms\promo\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "banner_templates".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $template
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $display_type
 *
 * @property BannerTemplateAttribute[] $templateAttributes
 * @property Banner[] $activeBanners
 * @property Banner[] $banners
 */
class BannerTemplate extends MultiLangModel implements \JsonSerializable
{

  use Translate;

  const DISPLAY_TYPE_DIV = 0;
  const DISPLAY_TYPE_IFRAME = 1;

  const LANG_PREFIX = 'promo.banner-templates.';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'banner_templates';
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
   * @return array
   */
  public function getMultilangAttributes()
  {
    return ['name'];
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
      [['code'], 'required'],
      [['template'], 'string'],
      [['code'], 'string', 'max' => 255],
      [['code'], 'unique'],
      [['display_type'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'code',
      'name',
      'template',
      'created_at',
      'updated_at',
      'display_type',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplateAttributes()
  {
    return $this->hasMany(BannerTemplateAttribute::class, ['template_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getBanners()
  {
    return $this->hasMany(Banner::class, ['template_id' => 'id']);
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    
    ApiHandlersHelper::generateBannerByTemplate($this->code);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActiveBanners()
  {
    return $this->hasMany(Banner::class, ['template_id' => 'id'])->andWhere([Banner::tableName(). '.is_disabled' => 0]);
  }

  /**
   * @return mixed
   */
  public function getActivePagesCount()
  {
    return $this->getActiveBanners()->count();
  }

  /**
   * @param null $id
   * @return array
   */
  public static function getCreateLink($id = null)
  {
    return [
      sprintf(
        '/%s/%s/%s',
        Module::getInstance()->id,
        'banner-templates',
        'create'
      ),
      'id' => $id
    ];
  }

  /**
   * @return array
   */
  public static function getListLink()
  {
    return [sprintf(
      '/%s/%s/%s',
      Module::getInstance()->id,
      'banner-templates',
      'index'
    )];
  }




  /**
   * @return ActiveQuery
   */
  public static function findAllActive()
  {
    return self::find()->where([self::tableName() . '.is_disabled' => 0]);
  }

  public static function getDisplayTypeDropdownItems()
  {
    return [
      self::DISPLAY_TYPE_DIV => Yii::_t('banner-templates.div'),
      self::DISPLAY_TYPE_IFRAME => Yii::_t('banner-templates.iframe'),
    ];
  }

  /**
   * @inheritdoc
   */
  function jsonSerialize()
  {
    return [
      'code' => $this->code
    ];
  }
}
