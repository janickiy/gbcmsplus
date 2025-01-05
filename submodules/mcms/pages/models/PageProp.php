<?php

namespace mcms\pages\models;

use mcms\common\helpers\ArrayHelper;
use mcms\pages\components\widgets\PagesWidget;
use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "page_props".
 *
 * @property integer $id
 * @property integer $page_id
 * @property integer $page_category_prop_id
 * @property integer $entity_id
 * @property string $multilang_value
 * @property string $value
 *
 * @property CategoryProp $categoryProp
 * @property Page $page
 */
class PageProp extends \mcms\common\multilang\MultiLangModel
{

  public $file;

  public $entities;

  public $index;

  const FILE_FOLDER = 'prop_files';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'page_props';
  }

  public function getMultilangAttributes()
  {
    return ['multilang_value'];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['page_id', 'page_category_prop_id'], 'required'],
      [['page_id', 'page_category_prop_id'], 'integer'],
      [['value'], 'string'],
      [['page_category_prop_id'], 'exist', 'skipOnError' => true, 'targetClass' => CategoryProp::class, 'targetAttribute' => ['page_category_prop_id' => 'id']],
      [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['page_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::t('pages.category', 'ID'),
      'page_id' => Yii::t('pages.category', 'Page ID'),
      'page_category_prop_id' => Yii::t('pages.category', 'Page Category Prop ID'),
      'value' => Yii::t('pages.category', 'Value'),
    ];
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategoryProp()
  {
    return $this->hasOne(CategoryProp::class, ['id' => 'page_category_prop_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getEntity()
  {
    return $this->hasOne(CategoryPropEntity::class, ['id' => 'entity_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPage()
  {
    return $this->hasOne(Page::class, ['id' => 'page_id']);
  }

  /**
   * @param int $propId
   * @return PageProp
   */
  static public function findOrCreateModel($propId)
  {
    return $propId && ($model = self::findOne(['id' => $propId])
    ) ? $model : new self();
  }

  public function deleteFile($fileName, $lang)
  {
    if ($this->categoryProp->type != CategoryProp::TYPE_FILE) return;

    $path = self::getUploadPath($this->page_category_prop_id) . '/' . $fileName;

    if (is_file($path)) unlink($path);

    $multiLang =  ArrayHelper::toArray($this->multilang_value);

    if (!$langFiles = ArrayHelper::getValue($multiLang, $lang)) return;

    if (($key = array_search($fileName, $langFiles)) === false) return;

    unset($multiLang[$lang][$key]);

    $this->multilang_value = $multiLang;

    $this->save();
  }

  public static function getUploadPath($categoryId)
  {
    return Yii::getAlias('@uploadPath/' . Yii::$app->getModule('pages')->id . '/' . PageProp::FILE_FOLDER . '/' . $categoryId);
  }

  public static function getUploadUrl($categoryId)
  {
    return '/uploads/' . Yii::$app->getModule('pages')->id . '/' . PageProp::FILE_FOLDER . '/' . $categoryId;
  }

  public function getImageUrl()
  {
    $uploadUrl = self::getUploadUrl($this->page_category_prop_id) . '/';
    $src = [];
    $curentLangValue = $this->multilang_value->getCurrentLangValue();
    if (is_array($curentLangValue)) {
      foreach ($curentLangValue as $fileName) {
        $src[] = $uploadUrl . $fileName;
      }
    }

    return $this->categoryProp->is_multivalue ? $src : current($src);
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    $this->invalidateCache();
  }

  public function afterDelete()
  {
    parent::afterDelete();
    $this->invalidateCache();
  }

  private function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [PagesWidget::CACHE_TAG]);
  }

}