<?php

namespace mcms\partners\components\subidStat\query;

use Yii;
use yii\helpers\Inflector;

/**
 * Стата по subid
 */
class SdbQuery extends BaseQuery
{

  private static $_mainSchemaName;

  /**
   * @var bool чтобы не джойнить одну таблицу дважды, после 1го джойна ставим этот флаг в true
   */
  protected $isJoinedSubid1 = false;

  /**
   * @var bool чтобы не джойнить одну таблицу дважды, после 1го джойна ставим этот флаг в true
   */
  protected $isJoinedSubid2 = false;

  /**
   * @return string
   * @throws \yii\db\Exception
   */
  public function getMainSchemaName()
  {
    if (self::$_mainSchemaName) {
      return self::$_mainSchemaName;
    }

    self::$_mainSchemaName = Yii::$app->db->createCommand('SELECT DATABASE()')->queryScalar();

    return self::$_mainSchemaName;
  }

  /**
   * Подставляем наше подключение
   * @inheritdoc
   */
  public function createCommand($db = null)
  {
    if ($db === null) {
      $db = Yii::$app->sdb;
    }

    return parent::createCommand($db);
  }

  public function makePrepare()
  {
    $userId = Yii::$app->user->id;
    $this->from(['st' => "statistic_user_$userId"]);


    $this->select([
      'hits' => $this->getHitsExpression(),
      'uniques' => $this->getUniquesExpression(),
      'tb' => $this->getTbExpression(),
      'accepted' => $this->getAcceptedExpression(),
      'revshare_ons' => $this->getRevshareOnsExpression(),
      'revshare_offs' => $this->getRevshareOffsExpression(),
      'revshare_ratio' => $this->getRevshareRatioExpression(),
      'revshare_cr' => $this->getRevshareCrExpression(),
      'revshare_rebills' => $this->getRevshareRebillsExpression(),
      'revshare_profit' => $this->getRevshareProfitExpression(),
      'cpa_ons' => $this->getCpaOnsExpression(),
      'cpa_ecpm' => $this->getCpaEcpmExpression(),
      'cpa_profit' => $this->getCpaProfitExpression(),
      'cpa_ratio' => $this->getCpaRatioExpression(),
      'cpa_cr' => $this->getCpaCrExpression(),
      'total_profit' => $this->getTotalProfitExpression(),
    ]);

    foreach ($this->getFormModel()->groups as $group) {
      $methodName = 'handleGroupBy' . Inflector::camelize($group);
      if (!method_exists($this, $methodName)) {
        continue;
      }
      $this->$methodName();
    }

    $this->handleFilterByDates();
    $this->handleFilterBySources();
    $this->handleFilterByStreams();
    $this->handleFilterByCountries();
    $this->handleFilterByOperators();
    $this->handleFilterByPlatforms();
    $this->handleFilterByLandings();
    $this->handleFilterBySubid();

    $this->handleFilterByCountHits();
    $this->handleFilterByCountUniques();
    $this->handleFilterByCountTb();
    $this->handleFilterByCountAccepted();
    $this->handleFilterByCountRevshareOns();
    $this->handleFilterByCountRevshareOffs();
    $this->handleFilterByCountRevshareRebills();
  }


  /**
   */
  public function handleGroupBySubid1()
  {
    $this->addSelect(['subid1' => 'gl1.value']);
    $this->joinBySubid(1);
    $this->addGroupBy('subid1_id');
  }
  /**
   */
  public function handleGroupBySubid2()
  {
    $this->addSelect(['subid2' => 'gl2.value']);
    $this->joinBySubid(2);
    $this->addGroupBy('subid2_id');
  }

  /**
   * Приджойниваем таблицу справочника
   * @param $num
   * @throws \yii\db\Exception
   */
  protected function joinBySubid($num)
  {
    $property = 'isJoinedSubid' . $num;
    if ($this->$property) {
      return;
    }

    $this->leftJoin(["gl$num" => "{$this->getMainSchemaName()}.subid_glossary"], "gl$num.id = st.subid{$num}_id");
    $this->$property = true;
  }

  /**
   */
  public function handleFilterByDates()
  {
    $this->andFilterWhere(['>=', 'st.date', $this->getFormModel()->dateFrom]);
    $this->andFilterWhere(['<=', 'st.date', $this->getFormModel()->dateTo]);
  }

  public function handleFilterBySources()
  {
    $this->andFilterWhere(['st.source_id' => $this->getFormModel()->sources]);
  }

  public function handleFilterByStreams()
  {
    $this->andFilterWhere(['st.stream_id' => $this->getFormModel()->streams]);
  }

  public function handleFilterByCountries()
  {
    $this->andFilterWhere(['st.country_id' => $this->getFormModel()->countries]);
  }

  public function handleFilterByOperators()
  {
    $this->andFilterWhere(['st.operator_id' => $this->getFormModel()->operators]);
  }

  public function handleFilterByPlatforms()
  {
    $this->andFilterWhere(['st.platform_id' => $this->getFormModel()->platforms]);
  }

  public function handleFilterByLandings()
  {
    $this->andFilterWhere(['st.landing_id' => $this->getFormModel()->landings]);
  }

  public function handleFilterBySubid()
  {
    if ($this->getFormModel()->subid1) {
      $this->joinBySubid(1);
    }
    if ($this->getFormModel()->subid2) {
      $this->joinBySubid(2);
    }
    $this->andFilterWhere(['gl1.value' => $this->getFormModel()->subid1]);
    $this->andFilterWhere(['gl2.value' => $this->getFormModel()->subid2]);
  }


  public function handleFilterByCountHits()
  {
    $this->andFilterHaving(['>=', 'hits', $this->getFormModel()->hitsFrom]);
    $this->andFilterHaving(['<=', 'hits', $this->getFormModel()->hitsTo]);
  }

  public function handleFilterByCountUniques()
  {
    $this->andFilterHaving(['>=', 'uniques', $this->getFormModel()->uniquesFrom]);
    $this->andFilterHaving(['<=', 'uniques', $this->getFormModel()->uniquesTo]);
  }

  public function handleFilterByCountTb()
  {
    $this->andFilterHaving(['>=', 'tb', $this->getFormModel()->tbFrom]);
    $this->andFilterHaving(['<=', 'tb', $this->getFormModel()->tbTo]);
  }

  public function handleFilterByCountAccepted()
  {
    $this->andFilterHaving(['>=', 'accepted', $this->getFormModel()->acceptedFrom]);
    $this->andFilterHaving(['<=', 'accepted', $this->getFormModel()->acceptedTo]);
  }

  public function handleFilterByCountRevshareOns()
  {
    $this->andFilterHaving(['>=', 'revshare_ons', $this->getFormModel()->onsFrom]);
    $this->andFilterHaving(['<=', 'revshare_ons', $this->getFormModel()->onsTo]);
  }

  public function handleFilterByCountRevshareOffs()
  {
    $this->andFilterHaving(['>=', 'revshare_offs', $this->getFormModel()->offsFrom]);
    $this->andFilterHaving(['<=', 'revshare_offs', $this->getFormModel()->offsTo]);
  }

  public function handleFilterByCountRevshareRebills()
  {
    $this->andFilterHaving(['>=', 'revshare_rebills', $this->getFormModel()->rebillsFrom]);
    $this->andFilterHaving(['<=', 'revshare_rebills', $this->getFormModel()->rebillsTo]);
  }

  /**
   * @return string
   */
  private function getHitsExpression()
  {
    if ($this->getFormModel()->isRevshare()) {
      return $this->getRevshareHitsExpression();
    }

    if ($this->getFormModel()->isCPA()) {
      return $this->getCpaHitsExpression();
    }

    return 'SUM(IFNULL(hits, 0))';
  }

  /**
   * @return string
   */
  private function getUniquesExpression()
  {
    if ($this->getFormModel()->isRevshare()) {
      return 'SUM(IFNULL(revshare_uniques, 0))';
    }

    if ($this->getFormModel()->isCPA()) {
      return 'SUM(IFNULL(to_buyout_uniques, 0)) + SUM(IFNULL(otp_uniques, 0))';
    }

    return 'SUM(IFNULL(uniques, 0))';
  }

  /**
   * @return string
   */
  private function getTbExpression()
  {
    if ($this->getFormModel()->isRevshare()) {
      return $this->getRevshareTbExpression();
    }

    if ($this->getFormModel()->isCPA()) {
      return $this->getCpaTbExpression();
    }

    return 'SUM(IFNULL(tb, 0))';
  }

  /**
   * @return string
   */
  private function getAcceptedExpression()
  {
    return $this->getHitsExpression() . ' - ' . $this->getTbExpression();
  }

  /**
   * @return string
   */
  private function getRevshareOnsExpression()
  {
    return 'SUM(IFNULL(revshare_ons, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareOffsExpression()
  {
    return 'SUM(IFNULL(revshare_offs, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareRebillsExpression()
  {
    return 'SUM(IFNULL(revshare_rebills, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareProfitExpression()
  {
    return "SUM(IFNULL(revshare_partner_profit_{$this->getFormModel()->getCurrency()}, 0))";
  }

  /**
   * @return string
   */
  private function getCpaOnsExpression()
  {
    return 'SUM(IFNULL(buyout_visible_ons, 0)) + SUM(IFNULL(otp_visible_ons, 0))';
  }

  /**
   * @return string
   */
  private function getCpaProfitExpression()
  {
    return "SUM(IFNULL(buyout_partner_profit_{$this->getFormModel()->getCurrency()}, 0))"
      . ' + ' .
      "SUM(IFNULL(otp_partner_profit_{$this->getFormModel()->getCurrency()}, 0))";
  }

  /**
   * @return string
   */
  private function getTotalProfitExpression()
  {
    $revshareExpression = $this->getRevshareProfitExpression();
    $cpaExpression = $this->getCpaProfitExpression();
    if ($this->getFormModel()->isRevshare()) {
      return $revshareExpression;
    }

    if ($this->getFormModel()->isCPA()) {
      return $cpaExpression;
    }

    return "$revshareExpression + $cpaExpression";
  }

  /**
   * @return string
   */
  private function getCpaEcpmExpression()
  {
    $cpaProfit = $this->getCpaProfitExpression();
    $cpaAccepted = $this->getCpaAcceptedExpression();
    return "($cpaProfit) * 1000 / ($cpaAccepted)";
  }

  /**
   * @return string
   */
  private function getCpaAcceptedExpression()
  {
    if ($this->getFormModel()->isRatioByUniques()) {
      return $this->getCpaUniquesExpression() . ' - ' . $this->getCpaTbUniquesExpression();
    }

    return $this->getCpaHitsExpression() . ' - ' . $this->getCpaTbExpression();
  }

  /**
   * @return string
   */
  private function getCpaHitsExpression()
  {
    return 'SUM(IFNULL(to_buyout_hits, 0)) + SUM(IFNULL(otp_hits, 0))';
  }

  /**
   * @return string
   */
  private function getCpaTbExpression()
  {
    return 'SUM(IFNULL(to_buyout_tb, 0)) + SUM(IFNULL(otp_tb, 0))';
  }

  /**
   * @return string
   */
  private function getCpaRatioExpression()
  {
    return "({$this->getCpaAcceptedExpression()}) / ({$this->getCpaOnsExpression()})";
  }

  /**
   * @return string
   */
  private function getCpaCrExpression()
  {
    return "({$this->getCpaOnsExpression()})*100 / ({$this->getCpaAcceptedExpression()})";
  }

  /**
   * @return string
   */
  private function getRevshareRatioExpression()
  {
    return "({$this->getRevshareAcceptedExpression()}) / ({$this->getRevshareOnsExpression()})";
  }

  /**
   * @return string
   */
  private function getRevshareCrExpression()
  {
    return "({$this->getRevshareOnsExpression()})*100 / ({$this->getRevshareAcceptedExpression()})";
  }

  /**
   * @return string
   */
  private function getRevshareAcceptedExpression()
  {
    if ($this->getFormModel()->isRatioByUniques()) {
      return $this->getRevshareUniquesExpression() . ' - ' . $this->getRevshareTbUniquesExpression();
    }

    return $this->getRevshareHitsExpression() . ' - ' . $this->getRevshareTbExpression();
  }

  /**
   * @return string
   */
  private function getRevshareHitsExpression()
  {
    return 'SUM(IFNULL(revshare_hits, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareTbExpression()
  {
    return 'SUM(IFNULL(revshare_tb, 0))';
  }

  /**
   * @return string
   */
  private function getCpaUniquesExpression()
  {
    return 'SUM(IFNULL(to_buyout_uniques, 0)) + SUM(IFNULL(otp_uniques, 0))';
  }

  /**
   * @return string
   */
  private function getCpaTbUniquesExpression()
  {
    return 'SUM(IFNULL(to_buyout_tb_uniques, 0)) + SUM(IFNULL(otp_tb_uniques, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareUniquesExpression()
  {
    return 'SUM(IFNULL(revshare_uniques, 0))';
  }

  /**
   * @return string
   */
  private function getRevshareTbUniquesExpression()
  {
    return 'SUM(IFNULL(revshare_tb_uniques, 0))';
  }
}
