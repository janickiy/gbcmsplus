<?php

namespace mcms\pages\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\model\Disabled;
use mcms\pages\components\widgets\PagesWidget;
use Yii;
use yii\behaviors\TimestampBehavior;
use mcms\common\multilang\MultiLangModel;
use mcms\pages\components\events\PageCreateEvent;
use mcms\pages\components\events\PageUpdateEvent;
use mcms\pages\components\events\PageDeleteEvent;
use yii\caching\TagDependency;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "pages".
 *
 * @property integer $page_category_id
 * @property integer $sort
 * @property Category $category
 * @property PageProp[] $props
 */
class Page extends MultiLangModel
{

  use Disabled;

  public $propModels;

  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';
  const SCENARIO_ENABLE = 'is_disabled-enable';
  const SCENARIO_DISABLE = 'is_disabled-disable';
  const SCENARIO_FILE_DELETE = 'file-delete';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class
    ];

  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return '{{pages}}';
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return [
      'name', 'text', 'seo_title', 'seo_keywords', 'seo_description'
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['sort', 'default', 'value' => 100],
      ['sort', 'required'],
      [['name'], 'validateArrayRequired'],
      [['seo_title', 'seo_keywords'], 'validateArrayString'],
      [['name', 'text', 'seo_description'], 'validateArrayString'],
      [['code', 'page_category_id'], 'required'],
      [['url'], 'unique'],
      [['url', 'code'], 'match', 'pattern' => '/^[a-z0-9\-\/_\\/#]*$/ui'],
      [['code'], 'unique', 'targetAttribute' => ['code', 'page_category_id']],

      [['noindex', 'is_disabled'], 'boolean'],
      ['images', 'image', 'extensions' => 'png, jpg, svg, webp'],
      ['sort', 'safe']
    ];
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['name', 'text', 'seo_title', 'seo_keywords', 'seo_description', 'noindex', 'is_disabled', 'images', 'url', 'code', 'page_category_id', 'sort'],
      self::SCENARIO_UPDATE => ['name', 'text', 'seo_title', 'seo_keywords', 'seo_description', 'noindex', 'is_disabled', 'images', 'url', 'code', 'page_category_id', 'sort'],
      self::SCENARIO_DISABLE => ['is_disabled'],
      self::SCENARIO_ENABLE => ['is_disabled'],
      self::SCENARIO_FILE_DELETE => ['images']
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::t('main.id', 'ID'),
      'name' => Yii::_t('main.name'),
      'text' => Yii::_t('main.text'),
      'seo_title' => Yii::_t('main.seo_title'),
      'url' => Yii::_t('main.url'),
      'code' => Yii::_t('main.code'),
      'seo_keywords' => Yii::_t('main.keywords'),
      'seo_description' => Yii::_t('main.seo_description'),
      'noindex' => Yii::_t('main.no_index'),
      'is_disabled' => Yii::_t('main.is_disabled'),
      'created_at' => Yii::_t('main.created_at'),
      'updated_at' => Yii::_t('main.updated_at'),
      'images' => Yii::_t('main.images'),
      'page_category_id' => Yii::_t('main.page_category_id'),
      'sort' => Yii::_t('main.sort'),
    ];
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert)) {

      if ($this->scenario === self::SCENARIO_DISABLE) {
        $this->is_disabled = 1;
      } elseif ($this->scenario === self::SCENARIO_ENABLE) {
        $this->is_disabled = 0;
      }

      return true;
    }
    return false;
  }

  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => Yii::_t('replacements.page_id')
        ]
      ],
      'noindex' => [
        'value' => $this->isNewRecord ? null : Yii::_t($this->noindex ? 'main.noindex_0' : 'main.noindex_1'),
        'help' => [
          'label' => Yii::_t('replacements')
        ]
      ],
      'is_disabled' => [
        'value' => $this->isNewRecord
          ? null
          : Yii::_t($this->isDisabled() ? 'main.is_disabled_0' : 'main.is_disabled_1'),
        'help' => [
          'label' => Yii::_t('replacements.is_disabled')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => Yii::_t('replacements.name')
        ]
      ],
      'text' => [
        'value' => $this->isNewRecord ? null : $this->text,
        'help' => [
          'label' => Yii::_t('replacements.text')
        ]
      ],
      'seo_title' => [
        'value' => $this->isNewRecord ? null : $this->seo_title,
        'help' => [
          'label' => Yii::_t('replacements.seo_title')
        ]
      ],
      'seo_keywords' => [
        'value' => $this->isNewRecord ? null : $this->seo_keywords,
        'help' => [
          'label' => Yii::_t('replacements.seo_keywords')
        ]
      ],
      'seo_description' => [
        'value' => $this->isNewRecord ? null : $this->seo_description,
        'help' => [
          'label' => Yii::_t('replacements.seo_description')
        ]
      ]
    ];
  }

  /**
   * @inheritDoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $this->saveProps();

    if ($insert) {
      (new PageCreateEvent($this))->trigger();
    } else {
      (new PageUpdateEvent($this))->trigger();
    }

    $this->invalidateCache();
  }

  public function saveProps()
  {
    if (!in_array($this->scenario, [
      self::SCENARIO_CREATE, self::SCENARIO_UPDATE
    ])) return;
    $oldIDs = ArrayHelper::getColumn($this->getProps()->each(), 'id');

    $newIds = [];
    foreach ($this->propModels as $propModel) {
      /** @var $propModel PageProp */
      switch($propModel->categoryProp->type) {
        case CategoryProp::TYPE_INPUT:
        case CategoryProp::TYPE_TEXTAREA:
          if ($propModel->attributeIsEmpty('multilang_value')) continue 2; // пустой инпут можно удалить из БД
          break;
        case CategoryProp::TYPE_CHECKBOX:
          if (!$propModel->value) continue 2; // нулевой чекбокс можно удалить из БД
          break;
        case CategoryProp::TYPE_SELECT:
          $propModel->entities = is_array($propModel->entities)
            ? $propModel->entities
            : [$propModel->entities]
          ;

          foreach ($propModel->entities as $selectedValue) {
            $propModel = new PageProp([
              'page_id' => $this->id,
              'page_category_prop_id' => $propModel->categoryProp->id,
              'entity_id' => $selectedValue
            ]);
            if (!$propModel->save()){
              throw new \Exception('PageProp model save error');
            }
            $newIds[] = $propModel->id;
          }
          continue 2;

        case CategoryProp::TYPE_FILE:

          $path = $propModel::getUploadPath($propModel->page_category_prop_id);
          FileHelper::createDirectory($path);

          $filesValue = $propModel->id ? ArrayHelper::toArray($propModel->multilang_value) : [];

          foreach(self::getLangs() as $lang){
            $files = UploadedFile::getInstances($propModel, '[' . $propModel->index . ']file[' . $lang . ']');

            foreach ($files as $file) {
              // store the source file name
              $ext = pathinfo($file->name)['extension'];
              // generate a unique file name
              $newFilename = Yii::$app->security->generateRandomString() . ".{$ext}";

              $file->saveAs($path . '/' . $newFilename);

              $filesValue[$lang][] = $newFilename;
            }
          }

          $propModel->multilang_value = $filesValue;
          $propModel->page_id = $this->id;

          $empty = $propModel->attributeIsEmpty('multilang_value');

          if (!$empty && !$propModel->save()){
            throw new \Exception('PageProp file save error');
          }
          if (!$empty) $newIds[] = $propModel->id;

          continue 2;
      }

      $propModel->page_id = $this->id;

      if (!$propModel->save()){
        throw new \Exception('PageProp model save error');
      }

      $newIds[] = $propModel->id;
    }

    $deletedIDs = array_diff($oldIDs, $newIds);

    if (!empty($deletedIDs)) PageProp::deleteAll(['id' => $deletedIDs]);
  }

  /**
   * @inheritDoc
   */
  public function afterDelete()
  {
    parent::afterDelete();
    $this->setOldAttributes($this->getAttributes());

    (new PageDeleteEvent($this))->trigger();

    $this->invalidateCache();
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategory()
  {
    return $this->hasOne(Category::class, ['id' => 'page_category_id']);
  }

  /**
   * @param $data
   * @return bool
   */
  public function loadProps($data)
  {
    $propModels = [];
    if (!empty($props = ArrayHelper::getValue($data, 'PageProp', []))) {
      foreach ($props as $index => $prop) {
        $propModel = PageProp::findOrCreateModel(
          ArrayHelper::getValue($prop, 'id', null)
        );
        foreach($prop as $attr => $val){
          $propModel->{$attr} = $val;
        }
        $propModel->index = $index;
        $propModels[] = $propModel;
      }
    }

    $this->propModels = $propModels;
    return true;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProps()
  {
    return $this->hasMany(PageProp::class, ['page_id' => 'id']);
  }

  public function getPropByCode($code)
  {
    $categoryProp = Yii::$app->cache->get(CategoryProp::BY_CODE_CACHE_TAG_PREFIX . $code);

    if (!$categoryProp) {
      $categoryProp = CategoryProp::find()->where(['code' => $code])->one();

      Yii::$app->cache->set(
        'page_category_code_' . $code,
        $categoryProp,
        3600,
        new TagDependency(['tags' => [
          CategoryProp::BY_CODE_CACHE_TAG_PREFIX . $code,
          CategoryProp::BY_CODE_CACHE_TAG
        ]])
      );
    }

    $multiValueResult = [];
    foreach ($this->props as $prop) {
      if ($prop->categoryProp->code !== $code) continue;

      if (!$categoryProp->is_multivalue || $categoryProp->type == CategoryProp::TYPE_FILE) return $prop;

      $multiValueResult[] = $prop;
    }

    return $multiValueResult;
  }

  private function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, [PagesWidget::CACHE_TAG]);
  }

  private static function getLangs()
  {
    return Yii::$app->params['languages'];
  }
}
