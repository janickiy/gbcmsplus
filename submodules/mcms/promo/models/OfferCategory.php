<?php

namespace mcms\promo\models;


use mcms\common\helpers\Html;
use mcms\common\helpers\Link;
use mcms\common\multilang\LangAttribute;
use mcms\common\multilang\MultiLangModel;
use mcms\common\traits\Translate;
use mcms\promo\models\search\LandingSearch;
use rgk\utils\behaviors\TimestampBehavior;
use rgk\utils\widgets\modal\Modal;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class OfferCategory
 * @package mcms\promo\models
 *
 * @property int $id
 * @property string code
 * @property string name
 * @property int status
 * @property int created_at
 * @property int updated_at
 *
 * @property int $activeLandingsCount
 */
class OfferCategory extends MultiLangModel
{
  use Translate;

  const LANG_PREFIX = 'promo.offer_categories.';

  const DEFAULT_CATEGORY_ID = 1;

  const STATUS_INACTIVE = 0;
  const STATUS_ACTIVE = 1;

  /**
   * @return string
   */
  public static function tableName()
  {
    return 'offer_categories';
  }

  /**
   * @param bool $activeOnly
   * @param null|int|array $included
   * @return array
   */
  public static function getDropdownItems($activeOnly = false, $included = null)
  {
    return array_map(function ($name) {
      return new LangAttribute($name);
    }, static::find()
      ->select('name')
      ->andFilterWhere(['id' => $included])
      ->andFilterWhere(['status' => $activeOnly ? self::STATUS_ACTIVE : null])
      ->indexBy('id')
      ->column());
  }

  /**
   * @return array
   */
  public static function getStatusesMap()
  {
    return [
      self::STATUS_INACTIVE => self::translate('status-inactive'),
      self::STATUS_ACTIVE => self::translate('status-active'),
    ];
  }

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['code', 'name'], 'required'],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayString', 'params' => ['min' => 1, 'max' => 50]],
      [['code'], 'string'],
      [['status'], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return string
   */
  public function getViewLink()
  {
    return Modal::widget([
      'url' => ['/promo/offer-categories/view-modal', 'id' => $this->id],
      'title' => $this->getStringInfo(),
      'toggleButtonOptions' => [
        'tag' => 'a',
      ],
    ]);
  }

  /**
   * @return string
   */
  public function getStatusLabel()
  {
    return static::getStatusesMap()[$this->status];
  }

  /**
   * @return string
   */
  public function getStringInfo()
  {
    return Yii::$app->formatter->asText($this->name);
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
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'code',
      'name',
      'status',
      'created_at',
      'updated_at',
    ]);
  }

  /**
   * @return ActiveQuery
   */
  public function getLandings()
  {
    return $this->hasMany(Landing::class, ['offer_category_id' => 'id']);
  }

  /**
   * @return ActiveQuery
   */
  public function getActiveLandings()
  {
    return $this->getLandings()
      ->andWhere(['status' => Landing::STATUS_ACTIVE]);
  }

  /**
   * @return int|string
   */
  public function getActiveLandingsCount()
  {
    return $this->getActiveLandings()->count();
  }

  /**
   * @return string
   */
  public function getLandingsLink()
  {
    return Link::get(
        'landings/index',
        [(new LandingSearch())->formName() . '[offer_category_id]' => $this->id],
        ['data-pjax' => 0],
        Yii::_t('promo.landings.main'))
      . ' '
      . Html::tag('span', $this->activeLandingsCount, ['class' => 'label label-default']
      );
  }

  /**
   * @return bool
   */
  public function isDisabled()
  {
    return $this->status !== self::STATUS_ACTIVE;
  }

  /**
   * @return bool
   */
  public function isEnabled()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  /**
   * @return $this
   */
  public function setEnabled()
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  /**
   * @return $this
   */
  public function setDisabled()
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }
}