<?php

namespace mcms\promo\models;

use mcms\common\helpers\Html;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\common\traits\Translate;
use mcms\promo\components\landing_sets\LandingSetLandsUpdater;
use mcms\promo\components\landing_sets\LandingSetsLandsUpdater;
use mcms\promo\models\search\SourceSearch;
use mcms\promo\models\search\LandingSetItemSearch;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "landings_sets".
 *
 * @property integer $id
 * @property string $name
 * @property integer $category_id
 * @property integer $autoupdate при добавлении новых не скрытых лендов по выбраной тематике,
 * они должны добавляться к набору. Так же при добавлении нового оператора должны добавляться доступные ленды.
 * @see LandingSetsLandsUpdater
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 *
 * @property LandingSetItem $items
 * @property LandingCategory $category
 * @property string $categoryLink
 * @property array $categoriesVariants
 */
class LandingSet extends \yii\db\ActiveRecord
{
  use Translate;

  public $source_id;

  const LANG_PREFIX = 'promo.landing_sets.';
  const SCENARIO_SOURCE_ADD = 'source_add';

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
    return 'landing_sets';
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      static::SCENARIO_DEFAULT => ['name', 'category_id', 'description', 'autoupdate'],
      static::SCENARIO_SOURCE_ADD => ['source_id'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['name'], 'required'],
      [['description'], 'string'],
      [['name'], 'string', 'max' => 255],
      [['autoupdate'], 'boolean'],
      [['category_id'], 'required', 'message' => Yii::_t(static::LANG_PREFIX . 'autoupdate-category-required'),
        'when' => function ($model) {
          return $model->autoupdate;
        }],
      [['category_id'], 'integer'],
      [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => LandingCategory::class, 'targetAttribute' => ['category_id' => 'id']],
      ['source_id', 'required', 'on' => static::SCENARIO_SOURCE_ADD],
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
      'category_id',
      'autoupdate',
      'created_at',
      'updated_at',
      'description',
    ]);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCategory()
  {
    return $this->hasOne(LandingCategory::class, ['id' => 'category_id']);
  }

  /**
   * @return string
   */
  public function getCategoryLink()
  {
    return $this->category ? $this->category->getViewLink() : null;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getItems()
  {
    return $this->hasMany(LandingSetItem::class, ['set_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLandings()
  {
    return $this->hasMany(Landing::class, ['id' => 'landing_id'])->viaTable(LandingSetItem::tableName(), ['set_id' => 'id']);
  }

  /**
   * @return string
   */
  public function getItemsLabel()
  {
    return Yii::_t('promo.landing_sets.landings') . ' '
    . Html::tag('span', $this->getItems()->count(), ['class' => 'label label-default']);
  }

  /**
   * @return array
   */
  public function getReplacements()
  {
    return [
      'id' => [
        'value' => $this->isNewRecord ? null : $this->id,
        'help' => [
          'label' => self::translate('attribute-id')
        ]
      ],
      'name' => [
        'value' => $this->isNewRecord ? null : $this->name,
        'help' => [
          'label' => self::translate('attribute-name')
        ]
      ]
    ];
  }

  /**
   * @return array
   */
  public function getUpdateUrl()
  {
    return ['/promo/landing-sets/update/', 'id' => $this->id];
  }

  /**
   * Наборы с автообновлением нельзя редактировать вручную
   * @return bool
   */
  public function canManageManual()
  {
    return !$this->autoupdate;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSources()
  {
    return $this->hasMany(Source::class, ['set_id' => 'id']);
  }

  /**
   * Получить наборы по категории
   *
   * @param int|null $categoryId
   * @param int|null $currentSetId id текущего набора, который нужно включить в результат
   * @param bool $returnEmptyCategory
   * @return \yii\db\ActiveRecord[]
   */
  public static function getByCategory($categoryId = null, $currentSetId = null, $returnEmptyCategory = true)
  {
    $landingSet = self::find()->filterWhere(['or', ['category_id' => $categoryId], ['id' => $currentSetId]]);

    return $returnEmptyCategory && $categoryId
      ? $landingSet->orWhere(['category_id' => null])->all()
      : $landingSet->all();
  }

  /**
   * @return string
   */
  public function getSourcesLink()
  {
    return Link::get(
      '/promo/webmaster-sources/index/',
      [(new SourceSearch())->formName() . '[set_id]' => $this->id],
      ['data-pjax' => 0],
      Yii::_t('promo.landing_sets.sources')) . ' ' . Html::tag('span', $this->getSources()->count(), ['class' => 'label label-default']);
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $this->autoupdate && (new LandingSetLandsUpdater($this))->run();
  }
}
