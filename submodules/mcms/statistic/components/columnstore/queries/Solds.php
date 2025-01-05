<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\promo\models\Landing;
use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем солды
 */
class Solds extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'sold.id',
      'type' => new Expression(Fact::TYPE_SOLD),
      'hit_id' => 'sold.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'sold.time',
      'date' => 'sold.date',
      'hour' => 'sold.hour',
      'operator_id' => 'sold.operator_id',
      'country_id' => 'sold.country_id',
      'landing_id' => 'sold.landing_id',
      'provider_id' => 'sold.provider_id',
      'source_id' => 'sold.source_id',
      'source_type' => 's.source_type',
      'user_id' => 'sold.user_id',
      'stream_id' => 's.stream_id',
      'platform_id' => 'sold.platform_id',
      'landing_pay_type_id' => 'sold.landing_pay_type_id',
      'traffic_type' => new Expression(Landing::TRAFFIC_TYPE_CPA),
      'ip' => 'ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => new Expression('NULL'),
      'trans_id' => new Expression('NULL'),
      'sub_is_fake' => new Expression('NULL'),
      'res_rub' => 'reseller_price_rub',
      'res_usd' => 'reseller_price_usd',
      'res_eur' => 'reseller_price_eur',
      'partner_rub' => 'profit_rub',
      'partner_usd' => 'profit_usd',
      'partner_eur' => 'profit_eur',
      'cpa_price_rub' => 'price_rub',
      'cpa_price_usd' => 'price_usd',
      'cpa_price_eur' => 'price_eur',
      'is_visible_to_partner' => 'sold.is_visible_to_partner',
      'manager_id' => 'pm.manager_id',
      'category_id' => 'l.category_id',
    ])
      ->from('sold_subscriptions sold')
      ->leftJoin('hit_params hp', 'hp.hit_id = sold.hit_id')
      ->leftJoin('sources s', 's.id = sold.source_id')
      ->leftJoin('partners_managers pm', 'sold.user_id = pm.user_id AND sold.date = pm.date')
      ->leftJoin('landings l', 'l.id = sold.landing_id')
      ->andFilterWhere(['>=', 'sold.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'sold.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'sold.id', $this->cfg->soldsFrom])
      ->andFilterWhere(['<=', 'sold.id', $this->cfg->soldsTo]);
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
      ->andWhere(['type' => Fact::TYPE_SOLD])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->soldsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->soldsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }
}
