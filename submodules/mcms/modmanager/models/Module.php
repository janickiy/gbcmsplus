<?php

namespace mcms\modmanager\models;

use mcms\common\module\settings\NavDivider;
use Yii;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\caching\TagDependency;
use yii\behaviors\TimestampBehavior;
use yii\helpers\BaseInflector;

/**
 * This is the model class for table "modules".
 *
 * @property integer $id
 * @property string $module_id
 * @property string $name
 * @property string $settings
 * @property integer $is_disabled
 * @property integer $created_at
 * @property integer $updated_at
 *
 */
class Module extends \yii\db\ActiveRecord
{

  const CACHE_AVAILABLE_MODULES = 'modules.available';
  const CACHE_ENABLED_MODULES = 'modules.enabled';

  const SCENARIO_INSTALL = 'install';
  const SCENARIO_SETTINGS = 'settings';

  const EVENT_MODULE_CHANGED = 'moduleChanged';
  const EVENT_MODULE_INSTALL = 'moduleInstall';

  const MODULES_TDEP_NAME = 'modules';
  const MAIN_DIVIDER_NAME = 'main';

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'modules';
  }

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
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'settings' => false,
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['module_id'], 'required'],
      [['settings'], 'string'],
      [['is_disabled', 'created_at', 'updated_at'], 'integer'],
      [['module_id', 'name'], 'string', 'max' => 255],
      [['module_id'], 'unique'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('modules.ID'),
      'module_id' => Yii::_t('modules.module_id'),
      'name' => Yii::_t('modules.name'),
      'settings' => Yii::_t('modules.settings'),
      'is_disabled' => Yii::_t('modules.is_disabled'),
      'created_at' => Yii::_t('modules.created_at'),
      'updated_at' => Yii::_t('modules.updated_at'),
    ];
  }

  public function scenarios()
  {
    $scenarios = parent::scenarios();
    return array_merge($scenarios, [
      self::SCENARIO_INSTALL => ['module_id', 'name', 'is_disabled', 'created_at', 'updated_at'],
      self::SCENARIO_SETTINGS => ['settings', 'updated_at'],
    ]);
  }

  public static function findEnabled()
  {
    if (!$enabled = Yii::$app->cache->get(self::CACHE_ENABLED_MODULES)) {
      $enabled = static::find()->where(['is_disabled' => 0])->indexBy('module_id')->all();

      Yii::$app->cache->set(
        self::CACHE_ENABLED_MODULES,
        $enabled,
        0,
        new TagDependency(['tags' => self::MODULES_TDEP_NAME])
      );
    }

    return $enabled;
  }

  public function getModuleId()
  {
    return $this->getAttribute('module_id');
  }

  public function findAvailable()
  {
    $enabledModules = ArrayHelper::map(static::findEnabled(), 'module_id', 'name');
    $availableModules = [];
    $configs = array_merge(
      glob(\Yii::getAlias('@mcms') . '/*/config/main.php'),
      glob(\Yii::getAlias('@admin') . '/modules/*/config/main.php')
    );
    foreach ($configs as $configFile) {
      $config = require($configFile);
      $configId = $config['id'];
      if ($configId == 'modmanager') continue;

      $config['is_enabled'] = array_key_exists($configId, $enabledModules);
      $availableModules[$configId] = $config;
    }
    return $availableModules;
  }

  private function getModuleByIdCacheKey($moduleId)
  {
    return sprintf('modules.byId.%s', $moduleId);
  }

  private function getModuleByIdTagName($moduleId)
  {
    return sprintf('module-id-%s', $moduleId);
  }

  public function getModuleById($moduleId)
  {
    $availableModules = $this->findAvailable();
    $module = ArrayHelper::getValue($availableModules, $moduleId);

    if (!$module) return null;
    if (!$module['is_enabled']) return $module;

    $cacheKey = $this->getModuleByIdCacheKey($moduleId);
    if (!$dbModule = Yii::$app->cache->get($cacheKey)) {
      $dbModule = $this->findOne(['module_id' => $moduleId]);
      Yii::$app->cache->set(
        $cacheKey,
        $dbModule,
        0,
        new TagDependency(['tags' => $this->getModuleByIdTagName($moduleId)])
      );
    }

    if (!($dbModule instanceof self)) {
      TagDependency::invalidate(Yii::$app->cache, $this->getModuleByIdTagName($moduleId));
      return $this->getModuleById($moduleId);
    }
    
    return $module;
  }

  /** \mcms\common\module\settings\Repository */
  public function getSettings()
  {
    return unserialize($this->settings) ? : null;
  }

  public function getModuleName() {
    return Yii::_t($this->name);
  }

  public function isDisabled()
  {
    return $this->is_disabled;
  }

  public function afterSave($insert, $changedAttributes)
  {
    TagDependency::invalidate(Yii::$app->cache, self::MODULES_TDEP_NAME);
    TagDependency::invalidate(Yii::$app->cache, $this->getModuleByIdTagName($this->module_id));

    Yii::$app->trigger(
      ($this->isNewRecord ? self::EVENT_MODULE_INSTALL : self::EVENT_MODULE_CHANGED),
      new Event(['sender' => $this])
    );

    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * @param $moduleId
   * @return bool
   */
  public static function canEdit($moduleId)
  {
    return Yii::$app->user->can('EditModuleSettings' . BaseInflector::camelize($moduleId));
  }

  /**
   * @param $moduleId
   * @return bool
   */
  public static function canEditTranslations($moduleId)
  {
    return Yii::$app->user->can('EditModuleTranslations' . BaseInflector::camelize($moduleId));
  }

  public static function getFromAttributesMap($attributes)
  {
    $attrMap = [Yii::_t(self::MAIN_DIVIDER_NAME) => []];
    $divider = Yii::_t(self::MAIN_DIVIDER_NAME);
    foreach ($attributes as $key => $attribute) {
      if ($attribute['type'] == NavDivider::NAV_DIVIDER_TYPE) {
        $divider = $attribute['name'];
        continue;
      }
      $attrMap[$divider][$key] = $attribute;
    }
    !$attrMap[Yii::_t(self::MAIN_DIVIDER_NAME)] && ArrayHelper::remove($attrMap, Yii::_t(self::MAIN_DIVIDER_NAME));
    return $attrMap;
  }
}
