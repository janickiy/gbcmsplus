<?php

namespace mcms\holds\models;

use DateTime;
use mcms\holds\components\RuleUnholdPlan;
use mcms\holds\components\RuleUnholdPlanner;
use mcms\promo\models\Country;
use mcms\holds\queues\RuleUnholdPlannerPayload;
use mcms\holds\queues\RuleUnholdPlannerWorker;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "hold_program_rules".
 *
 * @property integer $id
 * @property integer $hold_program_id
 * @property integer $country_id
 * @property integer $unhold_range
 * @property integer $unhold_range_type
 * @property integer $min_hold_range
 * @property integer $min_hold_range_type
 * @property integer $at_day
 * @property integer $at_day_type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $key_date
 *
 * @property HoldProgram $program
 */
class HoldProgramRule extends ActiveRecord
{
  const UNHOLD_RANGE_TYPE_DAY = 1;
  const UNHOLD_RANGE_TYPE_WEEK = 2;
  const UNHOLD_RANGE_TYPE_MONTH = 3;

  const AT_DAY_TYPE_WEEK = 1;
  const AT_DAY_TYPE_MONTH = 2;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'hold_program_rules';
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
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['hold_program_id', 'unhold_range', 'unhold_range_type', 'min_hold_range', 'min_hold_range_type'], 'required'],
      [['hold_program_id', 'country_id', 'unhold_range', 'unhold_range_type', 'min_hold_range', 'min_hold_range_type', 'at_day', 'at_day_type'], 'integer'],
      [['hold_program_id', 'unhold_range', 'unhold_range_type', 'min_hold_range', 'min_hold_range_type'], 'filter', 'filter' => 'intval'],
      [['hold_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => HoldProgram::class, 'targetAttribute' => ['hold_program_id' => 'id']],

      [['min_hold_range_type'], 'required', 'when' => function (HoldProgramRule $model) {
        return !empty($model->min_hold_range);
      }],
      ['min_hold_range_type', 'default', 'value' => 0, 'when' => function (HoldProgramRule $model) {
        return empty($model->min_hold_range);
      }],

      [['unhold_range'], 'compare', 'compareValue' => 0, 'operator' => '>'],

      [['min_hold_range'], 'compare', 'compareValue' => 0, 'operator' => '>='],


      // at_day обязательный если указин тип At Day Type
      ['at_day', 'required', 'when' => function (HoldProgramRule $model) {
        return !empty($model->at_day_type);
      }],

      // at_day_type обязательный если указин тип At Day
      ['at_day_type', 'required', 'when' => function (HoldProgramRule $model) {
        return !empty($model->at_day);
      }],

      // Больше 0 если указин тип At Day Type
      ['at_day', 'compare', 'compareValue' => 0, 'operator' => '>', 'when' => function (HoldProgramRule $model) {
        return !empty($model->at_day_type);
      }],

      // Не более 7 дней в неделе
      ['at_day', 'compare', 'compareValue' => 7, 'operator' => '<=', 'when' => function (HoldProgramRule $model) {
        return $model->at_day_type === self::AT_DAY_TYPE_WEEK;
      }],

      // Не более 31 дней в месяце
      ['at_day', 'compare', 'compareValue' => 31, 'operator' => '<=', 'when' => function (HoldProgramRule $model) {
        return $model->at_day_type === self::AT_DAY_TYPE_MONTH;
      }],
      // TRICKY: без этого фильтра не работает валидатор на уникальность, когда country_id не заполнено
      [['country_id'], 'filter', 'filter' => function () {
        return $this->country_id ?: null;
      }],
      [['country_id'], 'unique', 'targetAttribute' => ['country_id', 'hold_program_id'], 'message' => Yii::_t('holds.main.rule_already_exists'), 'skipOnEmpty' => false],

      [['country_id'], 'exist',
        'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_id' => 'id']],
      ['key_date', 'required', 'when' => function (HoldProgramRule $model) {
        return $model->unhold_range > 1;
      }],
      ['key_date', 'date', 'format' => 'php:Y-m-d'],
      ['key_date', 'keyDateValidator', 'when' => function (HoldProgramRule $model) {
        return $model->unhold_range > 1;
      }],
    ];
  }

  /**
   * Подставляем key_date, если не задана (диапазон - 1 неделя/месяц/день)
   * @param bool $insert
   * @return bool
   */
  public function beforeSave($insert)
  {
    if ($this->unhold_range > 1) {
      return parent::beforeSave($insert);
    }

    switch ($this->unhold_range_type) {
      case self::UNHOLD_RANGE_TYPE_WEEK:
        $this->key_date = date('Y-m-d', strtotime('last Monday'));
        break;
      case self::UNHOLD_RANGE_TYPE_MONTH:
        $this->key_date = date('Y-m-01');
        break;
      default:
        $this->key_date = date('Y-m-d');
        break;
    }

    return parent::beforeSave($insert);
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);
    try {
      Yii::$app->queue->push(
        RuleUnholdPlannerWorker::CHANNEL_NAME,
        new RuleUnholdPlannerPayload(['ruleId' => $this->id])
      );
    } catch (\Exception $e) {
      Yii::error(RuleUnholdPlannerWorker::CHANNEL_NAME . ' worker exception! ' . $e->getMessage());
    }
  }


  /**
   * Проверяет корректность key_date
   * @param $attribute
   */
  public function keyDateValidator($attribute)
  {
    $date = new DateTime($this->key_date);
    $dayOfWeek = (int)$date->format('N');
    $dayOfMonth = (int)$date->format('d');

    $message = '';
    $isError = false;
    switch ($this->unhold_range_type) {
      case self::UNHOLD_RANGE_TYPE_WEEK:
        $message = Yii::_t('holds.main.key_date-week_error');
        $isError = $dayOfWeek !== 1;
        break;
      case self::UNHOLD_RANGE_TYPE_MONTH:
        $message = Yii::_t('holds.main.key_date-month_error');
        $isError = $dayOfMonth !== 1;
        break;
    }
    if ($isError) {
      $this->addError($attribute, $message);
    }
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'hold_program_id' => 'Program ID',
      'country_id' => Yii::_t('holds.main.rule-country_id'),
      'unhold_range' => Yii::_t('holds.main.rule-unhold_range'),
      'unhold_range_type' => Yii::_t('holds.main.rule-unhold_range_type'),
      'min_hold_range' => Yii::_t('holds.main.rule-min_hold_range'),
      'min_hold_range_type' => Yii::_t('holds.main.rule-min_hold_range_type'),
      'at_day' => Yii::_t('holds.main.rule-at_day'),
      'at_day_type' => Yii::_t('holds.main.rule-at_day_type'),
      'key_date' => Yii::_t('holds.main.rule-key_date'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getProgram()
  {
    return $this->hasOne(HoldProgram::class, ['id' => 'hold_program_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCountry()
  {
    return $this->hasOne(Country::class, ['id' => 'country_id']);
  }

  /**
   * Получить тип диапазона в виде days|weeks|months. Нужно для подстановки в форматтер дат
   * @param $type
   * @return string
   */
  public static function getRangeTypeStr($type)
  {
    $type = (int)$type;

    if ($type === static::UNHOLD_RANGE_TYPE_WEEK) {
      return 'weeks';
    }
    if ($type === static::UNHOLD_RANGE_TYPE_MONTH) {
      return 'months';
    }

    return 'days';
  }

  /**
   * Получить план расхолда для правила по дате холда
   * TODO с целью не загромождать данный класс, лучше этот метод вынести в отдельный класс
   * @param $date
   * @return RuleUnholdPlan
   */
  public function getUnholdPlan($date)
  {
    $data = (new Query)->select([
      'rule_id',
      'date_from',
      'date_to',
      'unhold_date',
    ])
      ->from(RuleUnholdPlanner::tableName())
      ->where(['rule_id' => $this->id])
      ->andWhere(['<=', 'date_from', $date])
      ->andWhere(['>=', 'date_to', $date])
      ->one();
    return new RuleUnholdPlan($data);
  }
}
