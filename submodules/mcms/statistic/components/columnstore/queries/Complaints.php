<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем жалобы
 */
class Complaints extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'comp.id',
      'type' => new Expression(static::getTypeExpression()),
      'hit_id' => 'comp.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'comp.time',
      'date' => 'comp.date',
      'hour' => 'comp.hour',
      'operator_id' => 'comp.operator_id',
      'country_id' => 'comp.country_id',
      'landing_id' => 'comp.landing_id',
      'provider_id' => 'comp.provider_id',
      'source_id' => 'comp.source_id',
      'source_type' => 's.source_type',
      'user_id' => 'comp.user_id',
      'stream_id' => 'comp.stream_id',
      'platform_id' => 'comp.platform_id',
      'landing_pay_type_id' => 'comp.landing_pay_type_id',
      'traffic_type' => 'h.traffic_type',
      'ip' => 'ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => 'comp.description',
      'trans_id' => 'comp.trans_id',
      'sub_is_fake' => new Expression('0'),
      'res_rub' => new Expression('NULL'),
      'res_usd' => new Expression('NULL'),
      'res_eur' => new Expression('NULL'),
      'partner_rub' => new Expression('NULL'),
      'partner_usd' => new Expression('NULL'),
      'partner_eur' => new Expression('NULL'),
      'cpa_price_rub' => new Expression('NULL'),
      'cpa_price_usd' => new Expression('NULL'),
      'cpa_price_eur' => new Expression('NULL'),
      'is_visible_to_partner' => new Expression('NULL'),
      'manager_id' => 'pm.manager_id',
      'category_id' => 'l.category_id',
    ])
      ->from('complains comp')
      ->leftJoin('hit_params hp', 'hp.hit_id = comp.hit_id')
      ->leftJoin('hits h', 'h.id = comp.hit_id')
      ->leftJoin('sources s', 's.id = comp.source_id')
      ->leftJoin('partners_managers pm', 'comp.user_id = pm.user_id AND comp.date = pm.date')
      ->leftJoin('landings l', 'l.id = comp.landing_id')
      ->andFilterWhere(['>=', 'comp.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'comp.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'comp.id', $this->cfg->complaintsFrom])
      ->andFilterWhere(['<=', 'comp.id', $this->cfg->complaintsTo]);
  }

  /**
   * Есть ли в CS строки, которые уже записаны туда
   * @return false|null|string
   */
  private function getExistedIdAtCs()
  {
    return (new Query)
      ->select('id')
      ->from('facts')
      ->andWhere(['type' => array_values(Fact::getComplaintTypes())])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->complaintsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->complaintsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }

  /**
   * @return string
   */
  private static function getTypeExpression()
  {
    $str = "CASE comp.type \n";

    foreach (Fact::getComplaintTypes() as $complType => $factType) {
      $str .= "WHEN $complType THEN $factType\n";
    }

    return $str . ' END';
  }
}
