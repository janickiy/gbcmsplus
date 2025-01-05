<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем возвраты
 */
class Refunds extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'ref.id',
      'type' => new Expression(static::getTypeExpression()),
      'hit_id' => 'ref.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'ref.time',
      'date' => 'ref.date',
      'hour' => 'ref.hour',
      'operator_id' => 'ref.operator_id',
      'country_id' => 'country_id',
      'landing_id' => 'ref.landing_id',
      'provider_id' => 'l.provider_id',
      'source_id' => 'ref.source_id',
      'source_type' => 's.source_type',
      'user_id' => 's.user_id',
      'stream_id' => 's.stream_id',
      'platform_id' => 'ref.platform_id',
      'landing_pay_type_id' => 'ref.landing_pay_type_id',
      'traffic_type' => 'h.traffic_type',
      'ip' => 'ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => 'ref.description',
      'trans_id' => 'ref.trans_id',
      'sub_is_fake' => new Expression('0'),
      'res_rub' => 'reseller_rub',
      'res_usd' => 'reseller_usd',
      'res_eur' => 'reseller_eur',
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
      ->from('refunds ref')
      ->leftJoin('hit_params hp', 'hp.hit_id = ref.hit_id')
      ->leftJoin('hits h', 'h.id = ref.hit_id')
      ->leftJoin('operators o', 'o.id = ref.operator_id')
      ->leftJoin('landings l', 'l.id = ref.landing_id')
      ->leftJoin('sources s', 's.id = ref.source_id')
      ->leftJoin('partners_managers pm', 's.user_id = pm.user_id AND ref.date = pm.date')
      ->andFilterWhere(['>=', 'ref.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'ref.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'ref.id', $this->cfg->refundsFrom])
      ->andFilterWhere(['<=', 'ref.id', $this->cfg->refundsTo]);
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
      ->andWhere(['type' => array_values(Fact::getRefundTypes())])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->refundsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->refundsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }

  /**
   * @return string
   */
  private static function getTypeExpression()
  {
    $str = "CASE ref.type \n";

    foreach (Fact::getRefundTypes() as $complType => $factType) {
      $str .= "WHEN $complType THEN $factType\n";
    }

    return $str . ' END';
  }
}
