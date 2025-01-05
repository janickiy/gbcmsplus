<?php

namespace mcms\pages\models;

use mcms\common\traits\Translate;
use mcms\pages\components\widgets\PagesWidget;
use Yii;
use mcms\common\multilang\MultiLangModel;
use yii\caching\TagDependency;

/**
 * This is the model class for table "page_category_prop_entities".
 *
 * @property integer $id
 * @property string $code
 * @property string $label
 * @property integer $page_category_prop_id
 *
 * @property CategoryProp $pageCategoryProp
 */
class CategoryPropEntity extends MultiLangModel
{

  use Translate;

  const LANG_PREFIX = 'pages.category.prop-entity-';


  public function getMultilangAttributes()
  {
    return ['label'];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'page_category_prop_entities';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['label'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['label'], 'validateArrayRequired'],
      [['label'], 'validateArrayString'],
      [['code', 'page_category_prop_id'], 'required'],
      [['page_category_prop_id'], 'integer'],
      [['code'], 'string', 'max' => 255],
      [['code', 'page_category_prop_id'], 'unique', 'targetAttribute' => ['code', 'page_category_prop_id'], 'message' => 'The combination of Code and Page Category Prop ID has already been taken.'],
      [['page_category_prop_id'], 'exist', 'skipOnError' => true, 'targetClass' => CategoryProp::class, 'targetAttribute' => ['page_category_prop_id' => 'id']],
    ];
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'id',
      'label',
      'code',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPageCategoryProp()
  {
    return $this->hasOne(CategoryProp::class, ['id' => 'page_category_prop_id']);
  }

  public static function getModalLink($propId, $entityId = null)
  {
    return [
      sprintf(
        '/%s/%s/%s',
        Yii::$app->getModule('pages')->id,
        'categories',
        'prop-entity-modal'
      ),
      'propId' => $propId,
      'id' => $entityId
    ];
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
      CategoryProp::BY_CODE_CACHE_TAG_PREFIX . $this->pageCategoryProp->code
    ]);
  }
}