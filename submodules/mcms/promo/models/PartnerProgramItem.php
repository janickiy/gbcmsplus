<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;
use mcms\promo\Module;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "partner_program_items".
 *
 * @property integer $id
 * @property integer $partner_program_id
 * @property integer $landing_id
 * @property integer $operator_id
 * @property string $rebill_percent
 * @property string $buyout_percent
 * @property string $cpa_profit_rub
 * @property string $cpa_profit_eur
 * @property string $cpa_profit_usd
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Landing $landing
 * @property Operator $operator
 * @property PartnerProgram $partnerProgram
 */
class PartnerProgramItem extends \yii\db\ActiveRecord
{
  use Translate;

  const LANG_PREFIX = 'promo.partner_program_items.';

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
    return 'partner_program_items';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['operator_id', 'checkUniqueConditionsValidator', 'skipOnEmpty' => false],
      [['partner_program_id'], 'required'],
      [['partner_program_id', 'landing_id', 'operator_id'], 'integer'],
      [['partner_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerProgram::class, 'targetAttribute' => ['partner_program_id' => 'id']],

      [['landing_id', 'operator_id'], 'integer'],
      [['landing_id'], 'exist', 'skipOnError' => true,
        'targetClass' => Landing::class,
        'targetAttribute' => ['landing_id' => 'id'],
        'message' => self::translate('landing_does_not_exist', [
          'landing_id' => $this->landing_id
        ]),
      ],
      [['operator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Operator::class, 'targetAttribute' => ['operator_id' => 'id']],

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

      [['cpa_profit_rub', 'cpa_profit_eur', 'cpa_profit_usd'], 'number'],

      ['rebill_percent', 'number', 'max' => PersonalProfit::MAX_REBILL_PERCENT],
      ['buyout_percent', 'number'],
      [['rebill_percent'], 'oneConditionRequiredValidator', 'skipOnEmpty' => false],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return self::translateAttributeLabels([
      'id',
      'partner_program_id',
      'landing_id',
      'operator_id',
      'rebill_percent',
      'buyout_percent',
      'cpa_profit_rub',
      'cpa_profit_eur',
      'cpa_profit_usd',
      'created_by',
      'updated_by',
      'created_at',
      'updated_at',
    ]);
  }

  /**
   * Заполните как минимум одно из полей: процент за ребилл, процент за выкуп или доход с CPA
   * @param string $attribute
   */
  public function oneConditionRequiredValidator($attribute)
  {
    if (
      empty($this->rebill_percent)
      && empty($this->buyout_percent)
      && empty($this->cpa_profit_eur)
      && empty($this->cpa_profit_usd)
      && empty($this->cpa_profit_rub)
    ) {
      $this->addError($attribute, Yii::_t(static::LANG_PREFIX . 'one-of-conditions-required'));
    }
  }

  /**
   * @param $attribute
   * @return bool
   */
  public function checkUniqueConditionsValidator($attribute)
  {
    $existsQuery = self::find()
      ->where(['partner_program_id' => $this->partner_program_id ? : null])
      ->andWhere(['landing_id' => $this->landing_id ? : null])
      ->andWhere(['operator_id' => $this->operator_id ? : null]);

    if (!$this->isNewRecord) {
      $existsQuery->andWhere(['<>', 'id', $this->id]);
    }

    $exists = $existsQuery->exists();

    if ($exists) {
      $this->addError('operator_id', self::translate('unique-validate-fail-operator'));
      $this->addError('landing_id', self::translate('unique-validate-fail-landing', [
        'landing_id' => $this->landing_id,
      ]));
    }

    return !$exists;
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
   * @return \yii\db\ActiveQuery
   */
  public function getPartnerProgram()
  {
    return $this->hasOne(PartnerProgram::class, ['id' => 'partner_program_id']);
  }
}
