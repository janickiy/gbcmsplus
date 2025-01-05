<?php

namespace mcms\holds\components;

use mcms\common\RunnableInterface;
use mcms\holds\models\HoldProgramRule;
use Yii;
use yii\base\Object;
use yii\helpers\Html;

/**
 * Для конкретного правила пересчитывает план расхолда. Для этого полностью удаляет существующий график.
 */
class RuleUnholdPlanner extends Object implements RunnableInterface
{
  const MAX_BATCH_SIZE = 100;

  public $dateFrom = '2018-01-26'; // ранее этой даты точно не было холдов партнеров
  public $dateTo = '2023-01-01'; // если сейчас уже 2023г, то привет из прошлого :)

  /**
   * @var HoldProgramRule
   */
  public $rule;

  /**
   * @var array инсертить будем пачками, предварительно соираем в этом массиве.
   * @see RuleUnholdPlanner::MAX_BATCH_SIZE
   */
  private $batchArray = [];

  /**
   * @return string
   */
  public static function tableName()
  {
    return 'rule_unhold_plan';
  }

  /**
   * Пересчитываем план для рули
   * @return bool
   */
  public function run()
  {
    if (date('Y-m-d') >= $this->dateTo) {
      Yii::warning('Hey, please check hold plans! dateTo=' . $this->dateTo, __METHOD__);
    }

    $this->rule->validate(['unhold_range', 'unhold_range_type', 'min_hold_range', 'min_hold_range_type', 'key_date']);

    if ($this->rule->hasErrors()) {
      Yii::error('rule model is not valid:' . Html::errorSummary($this->rule), __METHOD__);
      return false;
    }

    $this->deleteExistedPlan();
    $this->scheduleNewPlan();

    return true;
  }

  /**
   * удалить существующий график по указанному правилу
   * @throws \yii\db\Exception
   */
  protected function deleteExistedPlan()
  {
    Yii::$app->db->createCommand()
      ->delete(static::tableName(), ['rule_id' => $this->rule->id])
      ->execute();
  }

  /**
   * строим новый график
   * @throws \yii\base\InvalidConfigException
   */
  protected function scheduleNewPlan()
  {
    $this->scheduleLeftPacks();
    $this->scheduleRightPacks();
  }

  /**
   * Расчет пачек слева от ключевой пачки
   * @throws \yii\base\InvalidConfigException
   */
  protected function scheduleLeftPacks()
  {
    $packDateFrom = $this->rule->key_date;

    while ($packDateFrom >= $this->dateFrom) {
      $packDateTo = Yii::$app->formatter->asDate("$packDateFrom -1 day", 'php:Y-m-d');

      // вычитаем одну пачку назад
      $packDateFrom = $this->getPackDateFrom($packDateTo);

      $calc = new UnholdDateCalc([
        'packDateTo' => $packDateTo,
        'minHoldRange' => $this->rule->min_hold_range,
        'minHoldRangeType' => $this->rule->min_hold_range_type,
        'atDay' => $this->rule->at_day,
        'atDayType' => $this->rule->at_day_type,
      ]);

      $calc->calcUnholdDate();

      $this->insertPlanRow($packDateFrom, $packDateTo, $calc);
    }
  }

  /**
   * Расчет пачек справа от ключевой пачки, включая саму ключевую пачку
   * @throws \yii\base\InvalidConfigException
   */
  protected function scheduleRightPacks()
  {
    $packDateFrom = $this->rule->key_date;

    while ($packDateFrom <= $this->dateTo) {
      $packDateTo = $this->getPackDateTo($packDateFrom);

      $calc = new UnholdDateCalc([
        'packDateTo' => $packDateTo,
        'minHoldRange' => $this->rule->min_hold_range,
        'minHoldRangeType' => $this->rule->min_hold_range_type,
        'atDay' => $this->rule->at_day,
        'atDayType' => $this->rule->at_day_type,
      ]);

      $calc->calcUnholdDate();

      $this->insertPlanRow($packDateFrom, $packDateTo, $calc);

      $packDateFrom = Yii::$app->formatter->asDate("$packDateTo +1 day", 'php:Y-m-d');
    }

    /**
     * если в батче остались ещё строки в кол-ве меньше чем @see RuleUnholdPlanner::MAX_BATCH_SIZE
     * то их надо отправить в БД
     */
    $this->pushBatch();
  }

  /**
   * Получить дату начала пачки от даты её окончания
   * @param $packDateTo Y-m-d
   * @return string Y-m-d
   * @throws \yii\base\InvalidConfigException
   */
  protected function getPackDateFrom($packDateTo)
  {
    return Yii::$app->formatter->asDate(
      strtr('{packDateTo} -{packSize} {packType} + 1day', [
        '{packDateTo}' => $packDateTo,
        '{packSize}' => $this->rule->unhold_range,
        '{packType}' => HoldProgramRule::getRangeTypeStr($this->rule->unhold_range_type)
      ]),
      'php:Y-m-d'
    );
  }

  /**
   * Получить дату конца пачки от даты её начала
   * @param $packDateFrom Y-m-d
   * @return string Y-m-d
   * @throws \yii\base\InvalidConfigException
   */
  protected function getPackDateTo($packDateFrom)
  {
    return Yii::$app->formatter->asDate(
      strtr('{packDateFrom} +{packSize} {packType} - 1day', [
        '{packDateFrom}' => $packDateFrom,
        '{packSize}' => $this->rule->unhold_range,
        '{packType}' => HoldProgramRule::getRangeTypeStr($this->rule->unhold_range_type)
      ]),
      'php:Y-m-d'
    );
  }

  /**
   * Вставляем строку в БД
   * @param $packDateFrom
   * @param $packDateTo
   * @param UnholdDateCalc $calc
   */
  protected function insertPlanRow($packDateFrom, $packDateTo, UnholdDateCalc $calc)
  {
    $this->batchArray[] = [
      $this->rule->id,
      $packDateFrom,
      $packDateTo,
      $calc->unholdDate,
      (new RuleUnholdPlannerMeta([
        'ruleId' => $this->rule->id,
        'packDateFrom' => $packDateFrom,
        'packDateTo' => $packDateTo,
        'calcObj' => $calc
      ]))->getMetaText()
    ];
    if (count($this->batchArray) === static::MAX_BATCH_SIZE) {
      $this->pushBatch();
    }
  }

  /**
   * Вставляем строки в БД, которые есть в массиве [[batchArray]]
   * @throws \yii\db\Exception
   */
  protected function pushBatch()
  {
    if (empty($this->batchArray)) {
      return;
    }

    Yii::$app->db->createCommand()
      ->batchInsert(
        static::tableName(),
        [
          'rule_id',
          'date_from',
          'date_to',
          'unhold_date',
          'meta'
        ],
        $this->batchArray
      )
      ->execute();
    $this->batchArray = [];
  }
}
