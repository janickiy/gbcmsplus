<?php

namespace mcms\pages\models;

use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\pages\components\widgets\PagesWidget;
use Yii;
use mcms\common\helpers\ArrayHelper;
use yii\caching\TagDependency;

/**
 * This is the model class for table "page_category_props".
 *
 * @property integer $id
 * @property integer $type
 * @property string $code
 * @property string $name
 * @property integer $page_category_id
 * @property integer $is_multivalue
 *
 * @property CategoryProp[] $pageCategoryPropEntities
 * @property PageProp[] $pageProps
 * @property Category $category
 */
class CategoryProp extends MultiLangModel
{

  use Translate;

  const LANG_PREFIX = 'pages.category.prop-';

  const TYPE_INPUT = 1;
  const TYPE_TEXTAREA = 5;
  const TYPE_CHECKBOX = 2;
  const TYPE_SELECT = 3;
  const TYPE_FILE = 4;

  public static $types = [
    self::TYPE_INPUT,
    self::TYPE_TEXTAREA,
    self::TYPE_CHECKBOX,
    self::TYPE_SELECT,
    self::TYPE_FILE,
  ];

  const BY_CODE_CACHE_TAG_PREFIX = 'page_category_code_';
  const BY_CODE_CACHE_TAG = 'page_category_code';

  public function getMultilangAttributes()
  {
    return ['name'];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'page_category_props';
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
      [['type', 'code', 'page_category_id', 'is_multivalue'], 'required'],
      [['type', 'page_category_id', 'is_multivalue'], 'integer'],
      [['code'], 'string', 'max' => 255],
      [['page_category_id', 'code'], 'unique', 'targetAttribute' => ['page_category_id', 'code'], 'message' => Category::translate('code_unique')],
      [['page_category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['page_category_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'type',
      'code',
      'name',
      'page_category_id',
      'is_multivalue'
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPropEntities()
  {
    return $this->hasMany(CategoryPropEntity::class, ['page_category_prop_id' => 'id']);
  }


  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPageProps()
  {
    return $this->hasMany(PageProp::class, ['page_category_prop_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategory()
  {
    return $this->hasOne(Category::class, ['id' => 'page_category_id']);
  }

  public static function getModalLink($categoryId, $id = null)
  {
    return [
      sprintf(
        '/%s/%s/%s',
        Yii::$app->getModule('pages')->id,
        'categories',
        'prop-modal'
      ),
      'categoryId' => $categoryId,
      'id' => $id
    ];
  }

  public function getTypesLabels($filter = null)
  {
    $result = []; foreach (self::$types as $type) {
      $result[$type] = self::translate('type-' . $type);
    }
    return $filter ? ArrayHelper::getValue($result, $filter) : $result;
  }

  public function getEntityByCode($code)
  {
    foreach ($this->propEntities as $entity) {
      if ($entity->code === $code) return $entity;
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
    TagDependency::invalidate(Yii::$app->cache, [
      PagesWidget::CACHE_TAG,
      CategoryProp::BY_CODE_CACHE_TAG_PREFIX . $this->code
    ]);
  }
}