<?php

namespace mcms\promo\models;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\Module;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;

/**
 * This is the model class for table "landing_convert_tests".
 *
 * @property integer $id
 * @property integer $source_id
 * @property integer $max_hits
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Source $source
 */
class LandingConvertTest extends \yii\db\ActiveRecord
{

  const STATUS_ACTIVE = 1;
  const STATUS_INACTIVE = 0;

  const SCENARIO_TEST_CREATE = 'test_create';
  const SCENARIO_TEST_CREATE_NEW_LANDS = 'test_create_new_lands';
  const SCENARIO_TEST_DEACTIVATE = 'test_deactivate';

  /**
   * на случай если вдруг в модуле промо не указана настройка
   */
  const MIN_LANDING_RATING_DEFAULT = 5;
  const MAX_HITS_DEFAULT = 1000;

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }


  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'landing_convert_tests';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['max_hits', 'default', 'value' => $this->maxHitsDefaultApplier(), 'skipOnEmpty' => false],
      [['source_id', 'max_hits', 'status'], 'required'],
      [['source_id', 'max_hits', 'status', 'created_at', 'updated_at'], 'integer'],
      [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::class, 'targetAttribute' => ['source_id' => 'id']],
    ];
  }

  public function scenarios()
  {
    return [
      self::SCENARIO_TEST_CREATE => ['source_id', 'max_hits', 'status'],
      self::SCENARIO_TEST_CREATE_NEW_LANDS => ['source_id', 'max_hits', 'status'],
      self::SCENARIO_TEST_DEACTIVATE => ['status'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    /**
     * Пока без переводов, т.к. в интерфейсе ничего с этой моделью нет, только бэкенд
     */
    return [
      'id' => 'ID',
      'source_id' => 'Source ID',
      'max_hits' => 'Max Hits',
      'status' => 'Status',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getSource()
  {
    return $this->hasOne(Source::class, ['id' => 'source_id']);
  }


  public function maxHitsDefaultApplier()
  {
    return function ($model, $attribute) {
      /**
       * @var $model LandingConvertTest
       */
      $module = Yii::$app->getModule('promo');
      return $module->settings->getValueByKey(Module::SETTINGS_LAND_CONVERT_TEST_MAX_HITS) ?: self::MAX_HITS_DEFAULT;
    };
  }

  public function beforeSave($insert)
  {

    if (!$insert) return parent::beforeSave($insert);

    /**
     * Остановим все запущенные для этого источника тесты.
     */
    self::updateAll(['status' => self::STATUS_INACTIVE], ['source_id' => $this->source_id]);

    return parent::beforeSave($insert);
  }


  public function afterSave($insert, $changedAttributes)
  {
    if ($this->scenario != self::SCENARIO_TEST_CREATE) return parent::afterSave($insert, $changedAttributes);

    /**
     * Создаем тестовый набор лендингов.
     * Выбираются лендинги той же категории что и источник
     * с рейтингом не ниже заданного в настройках модуля.
     * Условие по рейтингу временно отключено.
     *
     * Для каждого оператора должны быть выбраны ленды. Если для нашей категории нет нужного ленда -
     * создается из другой категории из поля landing_categories.alter_categories
     *
     * Предварительно список лендов для источника очищается.
     */
    SourceOperatorLanding::deleteAll(['source_id' => $this->source_id]);

    //$module = Yii::$app->getModule('promo');

    //$minRating = $module->settings->getValueByKey(Module::SETTINGS_LAND_CONVERT_TEST_MIN_RATING) ?: self::MIN_LANDING_RATING_DEFAULT;

    $activeOperators = Operator::find()->where(['status' => Operator::STATUS_ACTIVE])->each();

    $availableCategoryIds = $this->getAvailableCategoryIds();

    $operatorLandings = $this->getActiveLandingOperators();

    $landingOperatorsModels = [];

    // Для каждого активного оператора подбираем ленды
    foreach ($activeOperators as $operator) {

      $availableOperatorLandings = $this->getAvailableOperatorLandings(ArrayHelper::getValue($operatorLandings, $operator->id), $availableCategoryIds);

      foreach ($availableOperatorLandings as $availableOperatorLanding) {
        $landingOperatorModel = new SourceOperatorLanding([
          'source_id' => $this->source->id,
          'profit_type' => $this->source->default_profit_type,
          'operator_id' => $operator->id,
          'landing_id' => ArrayHelper::getValue($availableOperatorLanding, 'landing_id'),
          'landing_choose_type' => SourceOperatorLanding::LANDING_CHOOSE_TYPE_AUTO
        ]);

        // Сначала только валидируем. Ниже сохраним массив через batchInsert
        if (!$landingOperatorModel->validate()) throw new Exception('Landing operator model save error');

        $landingOperatorsModels[] = $landingOperatorModel;
      }
    }

    $countExecuted = Yii::$app->db->createCommand()->batchInsert(
      SourceOperatorLanding::tableName(),
      (new SourceOperatorLanding())->attributes(),
      ArrayHelper::getColumn($landingOperatorsModels, 'attributes')
    )->execute();

    if ($countExecuted !== count($landingOperatorsModels)) throw new Exception('Landing operator model save error');

    return parent::afterSave($insert, $changedAttributes);
  }

  /**
   * Проверка завершился ли тест (по кол-ву хитов с момента создания теста)
   * @return bool
   */
  public function getIsFinished()
  {
    $statModule = Yii::$app->getModule('statistic');

    $hitsCount = $statModule->api('sourcesHitsCount', [
      'sourceId' => $this->source_id,
      'timeFrom' => $this->created_at
    ])->getResult();

    if (!$hitsCount) return false;

    return (int)$hitsCount >= (int)$this->max_hits;
  }

  public function getLandingsConvert()
  {
    $statModule = Yii::$app->getModule('statistic');

    return $statModule->api('sourcesLandingsConvert', [
      'sourceId' => $this->source_id,
      'timeFrom' => $this->created_at
    ])->getResult();
  }

  /**
   * Завершить все конверт тесты по источнику
   * @param int $sourceId
   */
  public static function forceFinishAllTests($sourceId)
  {
    static::updateAll(['status' => static::STATUS_INACTIVE], ['source_id' => $sourceId]);
  }

  /**
   * @return array
   */
  private function getActiveLandingOperators()
  {
    $landingOperators = (new Query())
      ->select([
        'lo.landing_id',
        'lo.operator_id',
        'l.category_id'
      ])
      ->from(LandingOperator::tableName() . ' lo')
      ->innerJoin(Operator::tableName() . ' o', 'lo.operator_id = o.id')
      ->innerJoin(Landing::tableName() . ' l', 'lo.landing_id = l.id')
      ->innerJoin(Country::tableName() . ' c', 'o.country_id = c.id')
      ->where(['l.status' => Landing::STATUS_ACTIVE])
      ->andWhere(['o.status' => Operator::STATUS_ACTIVE])
      ->andWhere(['c.status' => Country::STATUS_ACTIVE]);

    // Группируем по оператору
    $grouped = [];
    foreach ($landingOperators->each() as $landingOperator) {
      $grouped[$landingOperator['operator_id']][] = $landingOperator;
    }

    return $grouped;
  }

  /**
   * Получаем массив лендов для оператора и списка доступных категорий.
   * Если список лендов для первой категории будет пуст, берём следующую и т.д.
   *
   * @param $operatorLandings
   * @param $availableCategoryIds
   * @return array
   */
  private function getAvailableOperatorLandings($operatorLandings, $availableCategoryIds)
  {
    if (!$operatorLandings) return [];

    foreach ($availableCategoryIds as $availableCategoryId) {
      $lands = array_filter($operatorLandings, function ($operatorLanding) use ($availableCategoryId) {
        return $availableCategoryId == ArrayHelper::getValue($operatorLanding, 'category_id');
      });
      if (!empty($lands)) return $lands;
    }

    return [];
  }

  /**
   * @return array
   */
  private function getAvailableCategoryIds()
  {
    /** @var LandingCategory $sourceCategory */
    $sourceCategory = LandingCategory::findOne($this->source->category_id);

    $alterCategories = $sourceCategory->getAlterCategories();

    return array_merge([$sourceCategory->id], ArrayHelper::getColumn($alterCategories, 'id'));
  }

}