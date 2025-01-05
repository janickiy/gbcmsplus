<?php

namespace mcms\promo\models;

use mcms\common\multilang\MultiLangModel;
use mcms\promo\Module;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "banner_attribute_values".
 *
 * @property integer $id
 * @property integer $banner_id
 * @property integer $attribute_id
 * @property string $multilang_value
 * @property string $value
 *
 * @property BannerTemplateAttribute $templateAttribute
 * @property Banner $banner
 */
class BannerAttributeValue extends MultiLangModel
{

  const FILE_FOLDER = 'banner_value_files';

  public $file;
  public $entities;
  public $index;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'banner_attribute_values';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['banner_id', 'attribute_id'], 'required'],
      [['banner_id', 'attribute_id'], 'integer'],
      [['multilang_value'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['multilang_value'], 'validateArrayString'],
      [['value'], 'string'],
      [['attribute_id'], 'exist', 'skipOnError' => true, 'targetClass' => BannerTemplateAttribute::class, 'targetAttribute' => ['attribute_id' => 'id']],
      [['banner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Banner::class, 'targetAttribute' => ['banner_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'banner_id' => 'Banner ID',
      'attribute_id' => 'Attribute ID',
      'multilang_value' => 'Multilang Value',
      'value' => 'Value',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplateAttribute()
  {
    return $this->hasOne(BannerTemplateAttribute::class, ['id' => 'attribute_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getBanner()
  {
    return $this->hasOne(Banner::class, ['id' => 'banner_id']);
  }

  static public function findOrCreateModel($valueId)
  {
    return $valueId && ($model = self::findOne($valueId)) ? $model : new self();
  }

  public static function getUploadPath($categoryId)
  {
    return Yii::getAlias('@uploadPath/' . Module::getInstance()->id . '/' . self::FILE_FOLDER . '/' . $categoryId);
  }

  public static function getUploadUrl($categoryId)
  {
    return '/uploads/' . Module::getInstance()->id . '/' . self::FILE_FOLDER . '/' . $categoryId;
  }

  public function deleteFile($fileName, $lang)
  {
    if ($this->templateAttribute->type != BannerTemplateAttribute::TYPE_IMAGE) return;

    $path = self::getUploadPath($this->attribute_id) . '/' . $fileName;

    if (is_file($path)) unlink($path);

    $multiLang =  ArrayHelper::toArray($this->multilang_value);

    if (!$langFiles = ArrayHelper::getValue($multiLang, $lang)) return;
    if (($key = array_search($fileName, $langFiles)) === false) return;

    unset($multiLang[$lang][$key]);

    $this->multilang_value = $multiLang;

    $this->save();
  }

  public function getMultilangAttributes()
  {
    return ['multilang_value'];
  }

  public function getBannerReplacements($lang)
  {
    $value = $this->value ? : ArrayHelper::getValue($this->multilang_value, $lang, '');
    $attribute = $this->getTemplateAttribute()->one();
    switch ($attribute->type) {
      case BannerTemplateAttribute::TYPE_IMAGE;
        $uploadPath = $value;
        if (mb_substr($value, 0, 1) != '/') {
          $uploadPath = $this->getUploadPath($this->attribute_id);
          $uploadPath .= '/' . $value;
        }
        $value = sprintf(
          'data:image/%s;base64,%s',
          pathinfo($uploadPath, PATHINFO_EXTENSION),
          base64_encode(file_get_contents($uploadPath))
        );
        break;
    }

    return [sprintf('{%s}', $attribute->code) => $value];
  }
}
