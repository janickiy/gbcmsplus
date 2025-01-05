<?php

namespace mcms\pages\models;

use Exception;
use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\MultiLangModel;
use Yii;

/**
 * This is the model class for table "faq_categories".
 *
 * @property integer $id
 * @property string $name
 * @property integer $sort
 * @property integer $visible
 *
 * @property Faq[] $faqs
 */
class FaqCategory extends MultiLangModel
{
  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';

  const IS_VISIBLE = 1;
  const IS_NOT_VISIBLE = 0;

  const CACHE_KEY = 'faq_category';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
      return 'faq_categories';
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
      [['sort', 'visible'], 'required'],
      ['visible', 'boolean'],
      [['sort'], 'integer'],
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
    return [
      'id' => Yii::_t('faq.id'),
      'name' => Yii::_t('faq.category_name'),
      'sort' => Yii::_t('faq.sort'),
      'visible' => Yii::_t('faq.visible'),
    ];
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['name', 'sort', 'visible'],
      self::SCENARIO_UPDATE => ['name', 'sort', 'visible'],
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFaqs()
  {
    return $this->hasMany(Faq::class, ['faq_category_id' => 'id']);
  }

  public function saveCategory()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $this->isNewRecord && self::updateAllCounters(['sort' => 1], ['>=', 'sort', $this->sort]);
      if (!$this->isNewRecord && $this->getOldAttribute('sort') != $this->sort) {
        if ($this->getOldAttribute('sort') > $this->sort) {
          self::updateAllCounters(['sort' => 1], [
            'and',
            ['>=', 'sort', $this->sort],
            ['<', 'sort', $this->getOldAttribute('sort')]
          ]);
        } else {
          self::updateAllCounters(['sort' => -1], [
            'and',
            ['<=', 'sort', $this->sort],
            ['>', 'sort', $this->getOldAttribute('sort')]
          ]);
        }
      }
      if (!$this->save()) {
        throw new Exception("Can't save new faq category.");
      }
      $transaction->commit();
      return true;
    } catch(Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  public function getDropDownCategoryRangeArray()
  {
    $rangeTo = FaqCategory::find()->count();
    $this->scenario == self::SCENARIO_CREATE && $rangeTo++;
    return array_combine(range(1, $rangeTo), range(1, $rangeTo));
  }

  public function deleteCategory()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      Faq::deleteAll(['faq_category_id' => $this->id]);
      self::updateAllCounters(['sort' => -1], ['>', 'sort', $this->sort]);
      if (!$this->delete()) {
        throw new Exception("Can't dellete faq category.");
      }
      $transaction->commit();
      return true;
    } catch(Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  public static function getAllCategoriesDropDownArray()
  {
    return ArrayHelper::map(self::find()->all(), 'id', 'name');
  }

  public function afterSave($insert, $changedAttributes)
  {
    $this->invalidateCache();
    parent::afterSave($insert, $changedAttributes);
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  protected function invalidateCache()
  {
    Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->invalidate();
  }
}
