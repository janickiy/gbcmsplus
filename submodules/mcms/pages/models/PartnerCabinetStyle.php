<?php

namespace mcms\pages\models;

use mcms\common\traits\Translate;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner_cabinet_styles".
 *
 * @property integer $id
 * @property string $name
 * @property integer $status
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class PartnerCabinetStyle extends ActiveRecord
{
  use Translate;

  /**
   * @const string префикс ключа превью
   */
  const CACHE_PREVIEW_KEY_PREFIX = 'partner-cabinet-preview';
  /**
   * @const int Не используется
   */
  const STATUS_INACTIVE = 0;
  /**
   * @const int Используется как основное оформление партнерского кабинета
   */
  const STATUS_ACTIVE = 1;

  const LANG_PREFIX = 'pages.partner_cabinet_styles.';

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      BlameableBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'partner_cabinet_styles';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
      [['name'], 'required'],
      [['name'], 'unique'],
      [['name'], 'string', 'max' => 128],
      [['status', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'name',
      'status',
    ]);
  }

  /**
   * Статусы оформлений
   * @return string[]
   */
  public function getStatuses()
  {
    return [
      static::STATUS_INACTIVE => Yii::_t(static::LANG_PREFIX . 'status_inactive'),
      static::STATUS_ACTIVE => Yii::_t(static::LANG_PREFIX . 'status_active'),
    ];
  }

  /**
   * Название статуса
   * @param $status
   * @return string
   */
  public function getStatusName($status)
  {
    return $this->getStatuses()[$status];
  }

  /**
   * Переключить активность оформления
   * @return bool
   */
  public function toggleActivity()
  {
    return $this->status == static::STATUS_INACTIVE ? $this->activate() : $this->deactivate();
  }

  /**
   * Активировать оформление
   * @return bool
   */
  public function activate()
  {
    PartnerCabinetStyle::updateAll(['status' => static::STATUS_INACTIVE]);
    $this->status = static::STATUS_ACTIVE;
    return $this->update() !== false;
  }

  /**
   * Деактивировать оформление
   * @return bool
   */
  public function deactivate()
  {
    $this->status = static::STATUS_INACTIVE;
    return $this->update() !== false;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getValues()
  {
    return $this->hasMany(PartnerCabinetStyleValue::class, ['style_id' => 'id']);
  }

  /**
   * Генерация css стилей для текущего оформления
   * @return string
   */
  public function generateCss()
  {
    $values = PartnerCabinetStyleValue::find()
      ->alias('v')
      ->joinWith('field f')
      ->where(['v.style_id' => $this->id])
      ->orderBy('f.sort_css')
      ->each();

    $styles = '';
    foreach ($values as $value) {
      $styles .= $this->makeStyle($value->field->css_selector, $value->field->css_prop, $value->value);
    }

    return $styles;
  }

  /**
   * @param $selector
   * @param $prop
   * @param $value
   * @return string
   */
  private function makeStyle($selector, $prop, $value)
  {
    return "$selector { $prop: $value }";
  }

  /**
   * Переключить preview для оформления
   * @param int $id
   */
  public static function togglePreview($id)
  {
    self::getPreview() == $id
      ? static::disablePreview()
      : static::enablePreview($id)
    ;
  }

  /**
   * ключ кеша
   * @return string
   */
  public static function getPreviewCacheKey()
  {
    $backIdentity = Yii::$app->session->get('session.back_identity', false);
    return  $backIdentity
      ? self::CACHE_PREVIEW_KEY_PREFIX . Yii::$app->session->get('session.back_identity', [false])[0]
      : self::CACHE_PREVIEW_KEY_PREFIX . Yii::$app->user->id;
  }
  /**
   * Включить превью
   * @param int $id
   */
  public static function enablePreview($id)
  {
    Yii::$app->cache->set(self::getPreviewCacheKey(), $id);
  }

  /**
   * Отключить превью
   */
  public static function disablePreview()
  {
    Yii::$app->cache->delete(self::getPreviewCacheKey());
  }

  /**
   * Получить оформление для которого включе preview
   * @return mixed
   */
  public static function getPreview()
  {
    return Yii::$app->cache->get(self::getPreviewCacheKey());
  }
}
