<?php

namespace mcms\promo\components;

use mcms\common\helpers\ArrayHelper;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingConvertTest;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use Yii;
use yii\helpers\BaseHtml;

/**
 * Class WebmasterNewLandsHandler
 * @package mcms\promo\components
 */
class WebmasterNewLandsHandler
{

  public function run()
  {
    // Берём массив активных источников вебмастеров
    $activeSourcesQuery = Source::find()
      ->where([
        'status' => Source::STATUS_APPROVED,
        'source_type' => Source::SOURCE_TYPE_WEBMASTER_SITE,
        'landing_set_autosync' => 0
      ]);

    foreach ($activeSourcesQuery->each() as $source) {
      /** @var Source $source */
      $newLandOperators = $this->getSourceNewLandOperators($source);

      self::log('SOURCE: ' . json_encode(ArrayHelper::toArray($source)));
      self::log('newLandOperators: ' . json_encode(ArrayHelper::toArray($newLandOperators, ['landing_id', 'operator_id', 'created_at'])));

      if (empty($newLandOperators)) continue; // нет новых


      $this->addNewLandsToSource($source, $newLandOperators);

      // Запуск конверт теста с флагом
      $this->beginLandingsConvertTest($source);
    }
  }

  /**
   *
   * @param Source $source
   * @return LandingOperator[]
   */
  public function getSourceNewLandOperators(Source $source)
  {
    /** @var LandingConvertTest $lastConvertTest */

    $lastConvertTest = $source->getLastLandingConvertTest()->one();

    $newLandOperators = LandingOperator::findActiveLandingOperators()
      ->where([
        Landing::tableName() . '.category_id' => $source->category_id
      ])
      ->andWhere(['>', LandingOperator::tableName() . '.created_at', $lastConvertTest->created_at])
      ->all();

    return $newLandOperators;
  }

  /**
   *
   * @param Source $source
   * @param $newLandOperators
   * @throws \yii\db\Exception
   */
  public function addNewLandsToSource(Source $source, $newLandOperators)
  {
    $inserts = array_map(function ($landOperator) use ($source) {
      return [
        $source->id,
        $source->default_profit_type,
        $landOperator->operator_id,
        $landOperator->landing_id,
        SourceOperatorLanding::LANDING_CHOOSE_TYPE_AUTO
      ];
    }, $newLandOperators);

    $insertCommand = Yii::$app->db->createCommand()->batchInsert(SourceOperatorLanding::tableName(), [
      'source_id',
      'profit_type',
      'operator_id',
      'landing_id',
      'landing_choose_type'
    ], $inserts)->getRawSql();

    $command = Yii::$app->db->createCommand($insertCommand . ' ON DUPLICATE KEY UPDATE id = id');

    self::log('INSERT: ' . $command->getRawSql());

    $command->execute();
  }

  /**
   * @param Source $source
   */
  public function beginLandingsConvertTest(Source $source)
  {
    $testConvert = new LandingConvertTest([
      'source_id' => $source->id,
      'status' => LandingConvertTest::STATUS_ACTIVE,
      'scenario' => LandingConvertTest::SCENARIO_TEST_CREATE_NEW_LANDS
    ]);

    if (!$testConvert->save()) self::log('Convert test not saved: ' . BaseHtml::errorSummary($testConvert));
  }

  /**
   * @param $msg
   */
  protected static function log($msg)
  {
    Yii::warning($msg, 'webmaster_new_lands'); // искать в console.log по [warning][webmaster_new_lands]
  }
}