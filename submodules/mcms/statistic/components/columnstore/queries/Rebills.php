<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем ребиллы
 */
class Rebills extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'r.id',
      'type' => new Expression(Fact::TYPE_REBILL),
      'hit_id' => 'r.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'r.time',
      'date' => 'r.date',
      'hour' => 'r.hour',
      'operator_id' => 'r.operator_id',
      'country_id' => 'country_id',
      'landing_id' => 'r.landing_id',
      'provider_id' => 'r.provider_id',
      'source_id' => 'r.source_id',
      'source_type' => 's.source_type',
      'user_id' => 's.user_id',
      'stream_id' => 's.stream_id',
      'platform_id' => 'r.platform_id',
      'landing_pay_type_id' => 'r.landing_pay_type_id',
      'traffic_type' => 'h.traffic_type',
      'ip' => 'ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => new Expression('NULL'),
      'trans_id' => 'r.trans_id',
      'sub_is_fake' => new Expression('0'),
      'res_rub' => 'reseller_profit_rub',
      'res_usd' => 'reseller_profit_usd',
      'res_eur' => 'reseller_profit_eur',
      'partner_rub' => 'profit_rub',
      'partner_usd' => 'profit_usd',
      'partner_eur' => 'profit_eur',
      'cpa_price_rub' => new Expression('NULL'),
      'cpa_price_usd' => new Expression('NULL'),
      'cpa_price_eur' => new Expression('NULL'),
      'is_visible_to_partner' => new Expression('NULL'),
      'manager_id' => 'pm.manager_id',
      'category_id' => 'l.category_id',
    ])
      ->from('subscription_rebills r')
      ->leftJoin('hit_params hp', 'hp.hit_id = r.hit_id')
      ->leftJoin('hits h', 'h.id = r.hit_id')
      ->leftJoin('operators o', 'o.id = r.operator_id')
      ->leftJoin('sources s', 's.id = r.source_id')
      ->leftJoin('partners_managers pm', 's.user_id = pm.user_id AND r.date = pm.date')
      ->leftJoin('landings l', 'l.id = r.landing_id')
      ->andFilterWhere(['>=', 'r.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'r.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'r.id', $this->cfg->rebillsFrom])
      ->andFilterWhere(['<=', 'r.id', $this->cfg->rebillsTo]);
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
      ->andWhere(['type' => Fact::TYPE_REBILL])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->rebillsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->rebillsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }
}
