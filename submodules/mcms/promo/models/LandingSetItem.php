<?php

namespace mcms\promo\models;

use mcms\common\traits\model\Disabled;
use mcms\common\traits\Translate;
use mcms\promo\components\SourceLandingSetsSync;

/**
 * This is the model class for table "landing_sets_items".
 *
 * TRICKY В классе \mcms\promo\components\LandingSetsNewLandsHandler работа с лендингами набора идет напрямую с БД в обход модели
 *
 * @property integer $id
 * @property integer $set_id
 * @property integer $landing_id
 * @property integer $operator_id
 *
 * @property Landing $landing
 * @property Operator $operator
 * @property LandingSet $set
 */
class LandingSetItem extends \yii\db\ActiveRecord
{
  use Disabled, Translate;

  const LANG_PREFIX = 'promo.landing_set_items.';

  public $categoryId;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_set_items';
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      static::SCENARIO_DEFAULT => ['landing_id', 'operator_id', 'is_enabled', 'is_disabled', 'categoryId'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['set_id', 'landing_id', 'operator_id'], 'required'],
      [['set_id', 'category_id', 'landing_id', 'operator_id'], 'integer'],
      ['landing_id', 'checkUniqueConditionsValidator', 'skipOnEmpty' => false],
      [['set_id'], 'exist', 'skipOnError' => true, 'targetClass' => LandingSet::class, 'targetAttribute' => ['set_id' => 'id']],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],
      [['landing_id'], 'exist', 'skipOnError' => true,
        'targetClass' => Landing::class,
        'targetAttribute' => ['landing_id' => 'id'],
        'filter' => $this->categoryId ? ['category_id' => $this->categoryId] : null,
        'message' => static::translate('landing_not_found_in_category', [
          'landing_id' => $this->landing_id
        ]),
      ],
      [
        ['landing_id'], 'exist', 'skipOnError' => true,
        'targetClass' => LandingOperator::class,
        'targetAttribute' => ['operator_id' => 'operator_id', 'landing_id' => 'landing_id'],
        'message' => static::translate('landing_operator_does_not_exits', [
          'landing_id' => $this->landing_id,
        ]), 'when' => function ($model) {
          return $model->operator_id && $model->landing_id;
        }
      ],
      [['is_enabled', 'is_disabled'], 'boolean'],
      [['is_disabled'], 'default', 'value' => false],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'set_id',
      'landing_id',
      'operator_id',
      'is_enabled',
      'is_disabled',
    ]);
  }

  /**
   * @param $attribute
   * @return bool
   */
  public function checkUniqueConditionsValidator($attribute)
  {
    $existsQuery = self::find()
      ->andWhere([
        'set_id' => $this->set_id,
        'landing_id' => $this->landing_id,
        'operator_id' => $this->operator_id,
    ]);

    if (!$this->isNewRecord) {
      $existsQuery->andWhere(['<>', 'id', $this->id]);
    }

    $exists = $existsQuery->exists();

    if ($exists) {
      $this->addError($attribute, self::translate('unique-validate-fail', [
        'landing' => $this->$attribute,
      ]));
    }

    return !$exists;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSet()
  {
    return $this->hasOne(LandingSet::class, ['id' => 'set_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getLanding()
  {
    return $this->hasOne(Landing::class, ['id' => 'landing_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getOperator()
  {
    return $this->hasOne(Operator::class, ['id' => 'operator_id']);
  }

  /**
   * @return null|string
   */
  public function getLandingLink()
  {
    return $this->landing ? $this->landing->getViewLink() : null;
  }

  /**
   * @return null|string
   */
  public function getOperatorLink()
  {
    return $this->operator ? $this->operator->getViewLink() : null;
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $this->syncSources();
  }

  public function afterDelete()
  {
    parent::afterDelete();

    $this->syncSources();
  }

  public function syncSources()
  {
    (new SourceLandingSetsSync())->run();
  }

}
