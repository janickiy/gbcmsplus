<?php

namespace mcms\pages\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;
use mcms\common\traits\model\MultiLang;
use mcms\common\traits\Translate;
use mcms\pages\components\widgets\PagesWidget;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\helpers\Url;
use mcms\common\multilang\MultiLangModel;

/**
 * This is the model class for table "page_categories".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $is_seo_visible
 * @property integer $is_url_visible
 * @property integer $is_index_visible
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CategoryProp[] $pageCategoryProps
 */
class Category extends MultiLangModel
{

  use Translate;

  public $dynamicProps;

  const LANG_PREFIX = 'pages.category.';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'page_categories';
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
      [['code'], 'string', 'max' => 255],
      [['is_seo_visible', 'is_index_visible', 'is_url_visible'], 'integer'],
      [['code'], 'unique'],
      [['is_seo_visible', 'is_index_visible', 'is_url_visible'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'code',
      'name',
      'is_seo_visible',
      'is_url_visible',
      'is_index_visible',
      'created_at',
      'updated_at'
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProps()
  {
    return $this->hasMany(CategoryProp::class, ['page_category_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getActivePages()
  {
    return $this->hasMany(Page::class, ['page_category_id' => 'id'])->andWhere(['is_disabled' => 0]);
  }

  /**
   * @return mixed
   */
  public function getActivePagesCount()
  {
    return $this->getActivePages()->count();
  }


  public static function getListLink()
  {
    return [sprintf(
      '/%s/%s/%s',
      Yii::$app->getModule('pages')->id,
      'categories',
      'index'
    )];
  }

  public static function getCreateLink($id = null)
  {
    return [
      sprintf(
        '/%s/%s/%s',
        Yii::$app->getModule('pages')->id,
        'categories',
        'create'
      ),
      'id' => $id
    ];
  }

  public function getLink()
  {
    return Html::a($this->name, self::getCreateLink($this->id), [], [], false);
  }

  public static function getDropdown()
  {
    return ArrayHelper::map(self::find()->each(), 'id', 'name');
  }

  public static function findByCode($code)
  {
    return self::find()->where(['code' => $code])->one();
  }

  /**
   * @param $code
   * @return CategoryProp | null
   */
  public function getPropByCode($code)
  {
    foreach ($this->props as $prop) {
      if ($prop->code === $code) return $prop;
    }

    return null;
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