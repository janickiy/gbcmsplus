<?php

namespace mcms\promo\models;

use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\model\Disabled;
use mcms\promo\components\ApiHandlersHelper;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\Connection;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use mcms\promo\components\api\Banners as BannerApi;

/**
 * This is the model class for table "banners".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $template_id
 * @property integer $is_disabled
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $opacity
 * @property string $cross_position
 * @property string $timeout
 * @property string $is_default
 *
 * @property BannerAttributeValue[] $attributeValues
 * @property BannerTemplate $template
 */
class Banner extends MultiLangModel implements \JsonSerializable
{

  use Disabled;

  public $valuesModels;

  const SCENARIO_CREATE = 'create';
  const SCENARIO_UPDATE = 'update';
  const SCENARIO_ENABLE = 'enable';
  const SCENARIO_DISABLE = 'disable';
  const SCENARIO_FILE_DELETE = 'file_delete';

  const CROSS_LEFT_TOP = 0;
  const CROSS_RIGHT_TOP = 1;
  const CROSS_LEFT_BOTTOM = 2;
  const CROSS_RIGHT_BOTTOM = 3;

  const DEFAULT_OPACITY = 60;

  const DESABLED = 1;
  const ENABLED = 0;

  const DROP_DOWN_BANNERS_CACHE_KEY = 'banners_dropdown';
  const DROP_DOWN_BANNERS_TAGS = ['banner', 'template'];

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      [
        'class' => BlameableBehavior::class,
        'updatedByAttribute' => false,
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'banners';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['code', 'default', 'value' => Yii::$app->security->generateRandomString()],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['code', 'template_id'], 'required'],
      [['template_id', 'created_by', 'created_at', 'updated_at', 'cross_position', 'timeout'], 'integer'],
      [['opacity'], 'integer', 'max' => 100, 'min' => 0],
      [['code'], 'string', 'max' => 255],
      [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => BannerTemplate::class, 'targetAttribute' => ['template_id' => 'id']],
      ['code', 'unique', 'targetAttribute' => ['template_id', 'code']],
      ['is_default', 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['name', 'code', 'is_disabled', 'opacity', 'cross_position', 'timeout', 'is_default'],
      self::SCENARIO_UPDATE => ['name', 'is_disabled', 'opacity', 'cross_position', 'timeout', 'is_default'],
      self::SCENARIO_ENABLE => ['is_disabled'],
      self::SCENARIO_DISABLE => ['is_disabled'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function beforeSave($insert)
  {
    if (!$beforeSaveResult = parent::beforeSave($insert)) return false;

    switch ($this->scenario) {
      case self::SCENARIO_ENABLE:
        $this->setEnabled();
        break;
      case self::SCENARIO_DISABLE:
        $this->setDisabled();
        break;
    }

    return true;
  }


  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::t('main.id', 'ID'),
      'code' => Yii::_t('banners.attribute_code'),
      'name' => Yii::_t('banners.attribute_name'),
      'template_id' => Yii::_t('banners.attribute_template_id'),
      'created_by' => Yii::_t('main.created_by'),
      'created_at' => Yii::_t('main.created_at'),
      'updated_at' => Yii::_t('main.updated_at'),
      'is_disabled' => Yii::_t('banners.attribute_is_disabled'),
      'opacity' => Yii::_t('banners.opacity'),
      'cross_position' => Yii::_t('banners.cross_position'),
      'timeout' => Yii::_t('banners.timeout'),
      'is_default' => Yii::_t('banners.is_default'),
    ];
  }

  public function saveValues()
  {
    if (!in_array($this->scenario, [
      self::SCENARIO_CREATE, self::SCENARIO_UPDATE
    ])) return;

    /** @noinspection PhpParamsInspection */
    $oldIDs = ArrayHelper::getColumn($this->getAttributeValues()->each(), 'id');

    $newIds = [];
    foreach ($this->valuesModels as $valueModel) {
      /** @var $valueModel BannerAttributeValue */
      switch($valueModel->templateAttribute->type) {
        case BannerTemplateAttribute::TYPE_INPUT:
        case BannerTemplateAttribute::TYPE_TEXTAREA:
          if ($valueModel->attributeIsEmpty('multilang_value')) continue 2; // пустой инпут можно удалить из БД
          break;

        case BannerTemplateAttribute::TYPE_IMAGE:

          $path = $valueModel::getUploadPath($valueModel->attribute_id);
          FileHelper::createDirectory($path);

          $filesValue = $valueModel->id ? ArrayHelper::toArray($valueModel->multilang_value) : [];

          foreach(Yii::$app->params['languages'] as $lang){
            $files = UploadedFile::getInstances($valueModel, '[' . $valueModel->index . ']file[' . $lang . ']');

            foreach ($files as $file) {
              // store the source file name
              $ext = pathinfo($file->name)['extension'];
              // generate a unique file name
              $newFilename = Yii::$app->security->generateRandomString() . ".{$ext}";

              $file->saveAs($path . '/' . $newFilename);

              $filesValue[$lang] = $newFilename;
            }
          }

          $valueModel->multilang_value = $filesValue;
          $valueModel->banner_id = $this->id;

          $empty = $valueModel->attributeIsEmpty('multilang_value');

          if (!$empty && !$valueModel->save()){
            throw new \Exception('BannerAttributeValue file save error');
          }
          if (!$empty) $newIds[] = $valueModel->id;

          continue 2;
      }

      $valueModel->banner_id = $this->id;

      if (!$valueModel->save()){
        throw new \Exception('BannerAttributeValue model save error');
      }

      $newIds[] = $valueModel->id;
    }

    $deletedIDs = array_diff($oldIDs, $newIds);

    if (!empty($deletedIDs)) BannerAttributeValue::deleteAll(['id' => $deletedIDs]);
  }

  /**
   * @param $data
   * @return bool
   */
  public function loadValues($data)
  {
    $templateAttributeModelList = BannerTemplateAttribute::find()
      ->indexBy('id')
      ->asArray()
      ->all()
    ;

    $this->valuesModels = [];

    $bannerTemplateAttributes = ArrayHelper::getValue($data, 'BannerAttributeValue', []);
    if (empty($bannerTemplateAttributes)) return true;

    foreach ($bannerTemplateAttributes as $index => $attributes) {
      $attributeTypeId = ArrayHelper::getValue($attributes, 'attribute_id');
      $bannerAttributeModel = BannerAttributeValue::findOrCreateModel(
        ArrayHelper::getValue($attributes, 'id')
      );
      foreach ($attributes as $attribute => $value) {
        $bannerAttributeModel->{$attribute} = $value;
      }

      $bannerAttributeModel->index = $index;

      if (ArrayHelper::getValue(
          $templateAttributeModelList,
          [$attributeTypeId, 'type']
        ) == BannerTemplateAttribute::TYPE_IMAGE) {

        $path = $bannerAttributeModel::getUploadPath($bannerAttributeModel->attribute_id);
        FileHelper::createDirectory($path);

        $filesValue = $bannerAttributeModel->id ? ArrayHelper::toArray($bannerAttributeModel->multilang_value) : [];

        foreach(Yii::$app->params['languages'] as $lang) {
          $files = UploadedFile::getInstances(
            $bannerAttributeModel,
            '[' . $bannerAttributeModel->index . ']file[' . $lang . ']'
          );

          foreach ($files as $file) {
            $filesValue[$lang] = $file->tempName;
          }
        }
        $bannerAttributeModel->multilang_value = $filesValue;
      }

      $this->valuesModels[] = $bannerAttributeModel;
    }

    return true;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getAttributeValues()
  {
    return $this->hasMany(BannerAttributeValue::class, ['banner_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplate()
  {
    return $this->hasOne(BannerTemplate::class, ['id' => 'template_id']);
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
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    $this->saveValues();

    Yii::$app->db->getTransaction()
      ? Yii::$app->db->on(Connection::EVENT_COMMIT_TRANSACTION, function () {
        ApiHandlersHelper::generateBanner($this->id);
      })
      : ApiHandlersHelper::generateBanner($this->id)
    ;

    if (isset($changedAttributes['is_default'])) {
      if ($this->is_default) {
        self::updateAll(['is_default' => 0], 'id != :id', [':id' => $this->id]);
      }
    }

    BannerApi::clearSelectedBannerCache();
  }

  /**
   * @param $bannerId
   * @return $this|null
   */
  public static function getEnabledBannersById($bannerId)
  {
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    return Banner::find()
      ->where(['id' => $bannerId, 'is_disabled' => 0])
      ->one()
    ;
  }

  /**
   * @inheritdoc
   */
  function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'code' => $this->code,
      'name' => $this->name,
      'updatedAt' => $this->updated_at,
      'template' => $this->template,
      'templateCode' => $this->template->code,
      'display_type' => $this->template->display_type,
      'opacity' => $this->opacity,
      'cross_position' => $this->cross_position,
      'timeout' => $this->timeout,
    ];
  }

  function getCrossPosition()
  {
    switch ($this->cross_position) {
      case self::CROSS_RIGHT_TOP:
        $crossPositionX = 'right';
        $crossPositionY = 'top';
        break;
      case self::CROSS_LEFT_BOTTOM:
        $crossPositionX = 'left';
        $crossPositionY = 'bottom';
        break;
      case self::CROSS_RIGHT_BOTTOM:
        $crossPositionX = 'right';
        $crossPositionY = 'bottom';
        break;
      default:
        $crossPositionX = 'left';
        $crossPositionY = 'top';
        break;
    }

    return [$crossPositionX, $crossPositionY];
  }

  public static function getBannersDropDown($templatesId = [])
  {
    $cacheKey = self::DROP_DOWN_BANNERS_CACHE_KEY;
    if ($templatesId) {
      asort($templatesId);
      $cacheKey .= '_templates_' . serialize($templatesId);
    }

    $items = Yii::$app->cache->get($cacheKey);
    if ($items === false) {
      $queryWhere = [self::tableName() . '.is_disabled' => self::ENABLED];
      if ($templatesId) {
        $queryWhere[BannerTemplate::tableName() . '.id'] = $templatesId;
      }

      $banners = self::find()
        ->select([self::tableName() . '.*', BannerTemplate::tableName() . '.name as templateName'])
        ->where($queryWhere)
        ->joinWith(['template'])
        ->orderBy([
          'templateName' => SORT_ASC,
          self::tableName() . '.name' => SORT_ASC
        ])
        ->all();

      $items = ArrayHelper::map($banners, 'id', 'name', 'templateName');

      // Кладем в кэш
      Yii::$app->cache->set(
        $cacheKey,
        $items,
        3600,
        new TagDependency(['tags' => self::DROP_DOWN_BANNERS_TAGS])
      );
    }

    return $items;
  }

  public function getTemplateName()
  {
    /* @var $item LangAttribute*/
    $item = $this->template->name;
    return $item->getCurrentLangValue();
  }

}
