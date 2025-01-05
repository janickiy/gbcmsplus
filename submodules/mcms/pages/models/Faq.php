<?php

namespace mcms\pages\models;

use Exception;
use mcms\common\multilang\MultiLangModel;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use mcms\pages\components\events\FaqCreateEvent;
use mcms\pages\components\events\FaqUpdateEvent;

/**
 * This is the model class for table "faqs".
 *
 * @property integer $id
 * @property string $question
 * @property string $answer
 * @property integer $sort
 * @property integer $visible
 * @property integer $faq_category_id
 *
 * @property FaqCategory $faqCategory
 */
class Faq extends MultiLangModel
{
  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';

  const IS_VISIBLE = 1;
  const IS_NOT_VISIBLE = 0;

  const CACHE_KEY = 'faq';
  const CACHE_FAQ_LIST_KEY = 'mcms.pages.faq.list';
  const CACHE_FAQ_LIST_DURATION = 3600;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'faqs';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['question', 'answer'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['question', 'answer'], 'validateArrayRequired'],
      [['question', 'answer'], 'validateArrayString'],
      [['sort', 'visible', 'faq_category_id'], 'required'],
      [['sort', 'visible', 'faq_category_id'], 'integer'],
      [['question'], 'validateArrayString'],
      [['answer'], 'validateArrayString'],
      [['faq_category_id'], 'exist', 'targetClass' => FaqCategory::class, 'targetAttribute' => ['faq_category_id' => 'id']],
    ];
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return ['question', 'answer'];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('faq.id'),
      'question' => Yii::_t('faq.question'),
      'answer' => Yii::_t('faq.answer'),
      'sort' => Yii::_t('faq.sort'),
      'visible' => Yii::_t('faq.visible'),
      'faq_category_id' => Yii::_t('faq.faq_category'),
    ];
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['question', 'answer', 'sort', 'visible', 'faq_category_id'],
      self::SCENARIO_UPDATE => ['question', 'answer', 'sort', 'visible', 'faq_category_id'],
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFaqCategory()
  {
    return $this->hasOne(FaqCategory::class, ['id' => 'faq_category_id']);
  }

  public function getDropDownFaqRangeArray($categoryId)
  {
    $rangeTo = Faq::find()->where(['faq_category_id' => $categoryId])->count();
    ($this->isNewRecord || $this->faq_category_id != $categoryId) && $rangeTo++;
    if ($rangeTo == 1) {
      return ['output' => [['id' => "1", 'name' => "1"]], 'selected' => "1"];
    }
    $rangeToArray = [];
    foreach (range(1, $rangeTo) as $rangeVal) {
      array_push($rangeToArray, ['id' => $rangeVal, 'name' => $rangeVal]);
    }
    return ['output' => $rangeToArray, 'selected' => (string)$this->sort];
  }

  public function saveFaq()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      $this->isNewRecord && self::updateAllCounters(['sort' => 1], [
        'and',
        ['>=', 'sort', $this->sort],
        ['faq_category_id' => $this->faq_category_id]
      ]);
      if (!$this->isNewRecord && $this->getOldAttribute('faq_category_id') != $this->faq_category_id) {
        self::updateAllCounters(['sort' => -1], [
          'and',
          ['>', 'sort', $this->getOldAttribute('sort')],
          ['faq_category_id' => $this->getOldAttribute('faq_category_id')]
        ]);
        self::updateAllCounters(['sort' => 1], [
          'and',
          ['>=', 'sort', $this->sort],
          ['faq_category_id' => $this->faq_category_id]
        ]);
      }
      if (!$this->isNewRecord && $this->getOldAttribute('faq_category_id') == $this->faq_category_id
        && $this->getOldAttribute('sort') != $this->sort) {

        if ($this->getOldAttribute('sort') > $this->sort) {
          self::updateAllCounters(['sort' => 1], [
            'and',
            ['>=', 'sort', $this->sort],
            ['<', 'sort', $this->getOldAttribute('sort')],
            ['faq_category_id' => $this->faq_category_id]
          ]);
        } else {
          self::updateAllCounters(['sort' => -1], [
            'and',
            ['<=', 'sort', $this->sort],
            ['>', 'sort', $this->getOldAttribute('sort')],
            ['faq_category_id' => $this->faq_category_id]
          ]);
        }
      }

      if (!$this->save()) {
        throw new Exception("Can't save new faq.");
      }

      Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->invalidate();
      $transaction->commit();
      return true;
    } catch(Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  public function deleteFaq()
  {
    $transaction = Yii::$app->db->beginTransaction();
    try {
      self::updateAllCounters(['sort' => -1], [
        'and',
        ['>', 'sort', $this->sort],
        ['faq_category_id' => $this->faq_category_id]
      ]);
      if (!$this->delete()) {
        throw new Exception("Can't delete faq.");
      }

      Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->invalidate();
      $transaction->commit();
      return true;
    } catch(Exception $e) {
      $transaction->rollBack();
      return false;
    }
  }

  public function afterSave($insert, $changedAttributes)
  {
    $this->invalidateCache();
    parent::afterSave($insert, $changedAttributes);

    if ($insert) {
      (new FaqCreateEvent($this))->trigger();
    } else {
      (new FaqUpdateEvent($this))->trigger();
    }
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
    (new FaqUpdateEvent($this))->trigger();
  }

  protected function invalidateCache()
  {
    Yii::$app->getModule('pages')->api('GetCachedVisibleFaqList')->invalidate();
  }

  public function isVisible()
  {
    return $this->visible;
  }

  public static function getCachedVisibleFaqList($tagDependencyArray)
  {
    if (!($data = Yii::$app->getCache()->get(self::CACHE_FAQ_LIST_KEY))) {
      $data = FaqCategory::find()
        ->joinWith(['faqs' => function(ActiveQuery $query){
          $query->where(['faqs.visible' => Faq::IS_VISIBLE])->orderBy(['faqs.sort' => SORT_ASC]);
        }])
        ->where(['faq_categories.visible' => FaqCategory::IS_VISIBLE])
        ->orderBy(['faq_categories.sort' => SORT_ASC])
        ->all();

      Yii::$app->cache->set(
        self::CACHE_FAQ_LIST_KEY,
        $data,
        self::CACHE_FAQ_LIST_DURATION,
        new TagDependency(['tags' => $tagDependencyArray])
      );
    }
    return $data;
  }

  public function getReplacements()
  {
    return [
      'question' => [
        'value' => $this->isNewRecord ? null : $this->question,
        'help' => [
          'label' => Yii::_t('replacements.faq-question')
        ]
      ],
      'answer' => [
        'value' => $this->isNewRecord ? null : $this->answer,
        'help' => [
          'label' => Yii::_t('replacements.faq-answer')
        ]
      ],
      'category' => [
        'value' => $this->isNewRecord ? null : $this->faqCategory->name,
        'help' => [
          'label' => Yii::_t('replacements.faq-category')
        ]
      ],
    ];
  }
}
