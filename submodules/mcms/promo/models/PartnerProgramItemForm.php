<?php

namespace mcms\promo\models;

use Yii;
use yii\helpers\Html;

/**
 * Модель формы элемента набора лендингов
 *
 * @property integer|array $landing_id
 */
class PartnerProgramItemForm extends PartnerProgramItem
{
  public $isMultiple = 0;

  /**
   * @var string|array список лендингов
   */
  public $landings;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    $isMultipleInputName = Html::getInputName($this, 'isMultiple');
    return [
      ['operator_id', 'checkUniqueConditionsValidator', 'skipOnEmpty' => false, 'when' => function ($model) {
        return !$model->isMultiple;
      }],
      [['partner_program_id'], 'required'],
      [['partner_program_id', 'operator_id'], 'integer'],
      [['partner_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerProgram::class, 'targetAttribute' => ['partner_program_id' => 'id']],

      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],

      [['cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'], 'number'],

      ['rebill_percent', 'number', 'max' => PersonalProfit::MAX_REBILL_PERCENT],
      ['buyout_percent', 'number'],
      [['rebill_percent'], 'oneConditionRequiredValidator', 'skipOnEmpty' => false],


      ['landing_id', 'integer', 'when' => function ($model) {
        return !$model->isMultiple;
      }, 'whenClient' => "function (attribute, value) {
        return !parseInt($('$isMultipleInputName').val());
      }"],
      ['landings', 'filter', 'filter' => function ($landings) {
        !is_array($landings) && $landings = preg_split('/[\s,]+/', str_replace(' ', '', $landings));
        return array_unique(array_filter($landings)) ? : [null];
      }],
      ['landings', 'validateLandings'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge_recursive(parent::scenarios(), [
      static::SCENARIO_DEFAULT => ['isMultiple', 'landings'],
    ]);
  }

  /**
   * @param $attribute
   * @param $params
   */
  public function validateLandings($attribute, $params)
  {
    if (!$this->isMultiple) {
      return;
    }

    foreach ((array) $this->$attribute as $landingId) {
      $attributes = $this->attributes;
      $attributes['landing_id'] = $landingId;

      $modelItem = new parent($attributes);

      $modelItem->validate();

      if ($modelItem->hasErrors('landing_id')) {
        $this->addErrors(['landings' => $modelItem->getErrors('landing_id')]);
        break;
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return array_merge(parent::attributeLabels(), self::translateAttributeLabels([
      'landings' => 'landings'
    ]));
  }

  /**
   * @param bool $runValidation
   * @param null $attributeNames
   * @return bool
   */
  public function save($runValidation = true, $attributeNames = null)
  {
    if ($this->getIsNewRecord()) {
      return $this->isMultiple
        ? $this->saveMultiple($runValidation)
        : $this->insert($runValidation, $attributeNames);
    } else {
      return $this->update($runValidation, $attributeNames) !== false;
    }
  }

  /**
   * @param bool $runValidation
   * @param null $attributeNames
   * @return bool
   */
  public function saveMultiple($runValidation = true, $attributeNames = null)
  {
    if ($runValidation && !$this->validate()) {
      return false;
    }

    $batch = [];
    foreach ($this->landings as $landingId) {
      $attributes = $this->attributes;
      $attributes['landing_id'] = $landingId;

      $modelItem = new parent($attributes);

      $modelItem->validate();
      $modelItem->beforeSave(true); // чтобы сработали behaviors родительского класса

      $attributes = array_map(function ($item) {
        return $item != '' ? $item : null; // иначе пытается добавить пустую строку в INT поле
      }, $modelItem->attributes);

      $batch[] = $attributes;
    }

    $countExecuted = Yii::$app->db->createCommand()->batchInsert(
      static::tableName(),
      (new parent())->attributes(),
      $batch
    )->execute();

    $this->afterSave(true, array_fill_keys(array_keys($this->attributes), null));

    return (bool) $countExecuted;
  }
}