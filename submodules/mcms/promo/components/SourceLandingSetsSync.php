<?php

namespace mcms\promo\components;


use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingOperator;
use mcms\promo\models\LandingSet;
use mcms\promo\models\LandingSetItem;
use mcms\promo\models\LandingUnblockRequest;
use mcms\promo\models\Operator;
use mcms\promo\models\Source;
use mcms\promo\models\SourceOperatorLanding;
use Yii;
use yii\base\Component;
use yii\db\Query;

/**
 * Class SourceLandingSetsSync
 * @package mcms\promo\components
 */
class SourceLandingSetsSync extends Component
{
  public $sourceId;

  public function init()
  {
    parent::init();

  }

  public function run()
  {
    Yii::$app->db->createCommand(
    "INSERT INTO `sources_operator_landings` (`source_id`,`profit_type`,`operator_id`,`landing_id`)
      ({$this->landingsToInsertQuery->createCommand()->rawSql})"
    )->execute();

    if($ids = implode(',', $this->landingsToDeleteQuery->column())) {
      Yii::$app->db->createCommand(
        "DELETE FROM `sources_operator_landings`
      WHERE sources_operator_landings.id IN ($ids)"
      )->execute();
    }
  }

  /**
   * @return Query
   */
  public function getLandingsToDeleteQuery()
  {
    return (new Query())
      ->select([
        'sor.id',
      ])
      ->from(['sor' => "({$this->getSourceOperatorLandings()->createCommand()->rawSql})"])
      ->where([
        'and',
        $this->sourceId ? ['sources.id' => $this->sourceId] : ['sources.landing_set_autosync' => true],
        [
          'or',
          ['lsi.landing_id' => null],
          ['lsi.is_disabled' => true],
          ['lo.is_deleted' => 1],
        ]
      ])
      ->innerJoin('sources', 'sources.id = sor.source_id')
      ->innerJoin('landing_sets', 'landing_sets.id = sources.set_id')
      ->leftJoin(LandingOperator::tableName() . ' lo', 'lo.landing_id = sor.landing_id 
          AND lo.operator_id = sor.operator_id')
      ->leftJoin('landing_set_items lsi', 'landing_sets.id = lsi.set_id
        AND lsi.landing_id = sor.landing_id 
        AND lsi.operator_id = sor.operator_id ');
  }

  /**
   * @return Query
   */
  private function getSourceOperatorLandings()
  {
    return (new Query())->select('*')->from(SourceOperatorLanding::tableName());
  }

  /**
   * @return Query
   */
  public function getLandingsToInsertQuery()
  {
    return (new Query())
      ->select([
        's.id AS source_id',
        's.default_profit_type AS profit_type',
        'lsi.operator_id',
        'lsi.landing_id',
      ])
      ->from(['lsi' => LandingSetItem::tableName()])
      ->where([
        'and',
        $this->sourceId ? ['s.id' => $this->sourceId] : ['s.landing_set_autosync' => true],
        ['sor.landing_id' => null],
        ['lsi.is_disabled' => false],
        ['lo.is_deleted' => 0],
      ])
      ->innerJoin(LandingSet::tableName() . ' ls', 'ls.id = lsi.set_id')
      ->innerJoin(Landing::tableName() . ' l', 'lsi.landing_id = l.id')
      ->innerJoin(Source::tableName() . ' s', 's.set_id = ls.id')
      ->leftJoin(SourceOperatorLanding::tableName() . ' sor', 's.id = sor.source_id
          AND sor.landing_id = lsi.landing_id 
          AND sor.operator_id = lsi.operator_id')
      ->leftJoin(LandingOperator::tableName() . ' lo', 'lo.landing_id = lsi.landing_id 
          AND lo.operator_id = lsi.operator_id')
      ->leftJoin(LandingUnblockRequest::tableName() . ' r', 'r.landing_id = lsi.landing_id')
      ->andWhere(
        [
          'or',
          'l.access_type = :access_type_normal',
          'l.access_type IN (:access_type_hidden, :access_type_by_request) AND r.id IS NOT NULL',
        ],
        [
          ':access_type_normal' => Landing::ACCESS_TYPE_NORMAL,
          ':access_type_hidden' => Landing::ACCESS_TYPE_HIDDEN,
          ':access_type_by_request' => Landing::ACCESS_TYPE_BY_REQUEST,
        ]
      )
      ->groupBy(['sor.source_id', 'lsi.operator_id', 'lsi.landing_id']);

  }
}