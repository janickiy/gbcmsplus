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
 * Экспортируем onetime-подписки
 */
class Onetimes extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'onetime.id',
      'type' => new Expression(Fact::TYPE_ONETIME),
      'hit_id' => 'onetime.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'onetime.time',
      'date' => 'onetime.date',
      'hour' => 'onetime.hour',
      'operator_id' => 'onetime.operator_id',
      'country_id' => 'onetime.country_id',
      'landing_id' => 'onetime.landing_id',
      'provider_id' => 'onetime.provider_id',
      'source_id' => 'onetime.source_id',
      'source_type' => 's.source_type',
      'user_id' => 'onetime.user_id',
      'stream_id' => 'onetime.stream_id',
      'platform_id' => 'onetime.platform_id',
      'landing_pay_type_id' => 'onetime.landing_pay_type_id',
      'traffic_type' => new Expression(Landing::TRAFFIC_TYPE_ONETIME),
      'ip' => 'onetime.ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => new Expression('NULL'),
      'trans_id' => 'onetime.trans_id',
      'sub_is_fake' => new Expression('NULL'),
      'res_rub' => 'reseller_profit_rub',
      'res_usd' => 'reseller_profit_usd',
      'res_eur' => 'reseller_profit_eur',
      'partner_rub' => 'profit_rub',
      'partner_usd' => 'profit_usd',
      'partner_eur' => 'profit_eur',
      'cpa_price_rub' => 'calc_profit_rub',
      'cpa_price_usd' => 'calc_profit_usd',
      'cpa_price_eur' => 'calc_profit_eur',
      'is_visible_to_partner' => 'onetime.is_visible_to_partner',
      'manager_id' => 'pm.manager_id',
      'category_id' => 'l.category_id',
    ])
      ->from('onetime_subscriptions onetime')
      ->leftJoin('hit_params hp', 'hp.hit_id = onetime.hit_id')
      ->leftJoin('sources s', 's.id = onetime.source_id')
      ->leftJoin('partners_managers pm', 'onetime.user_id = pm.user_id AND onetime.date = pm.date')
      ->leftJoin('landings l', 'l.id = onetime.landing_id')
      ->andFilterWhere(['>=', 'onetime.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'onetime.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'onetime.id', $this->cfg->onetimesFrom])
      ->andFilterWhere(['<=', 'onetime.id', $this->cfg->onetimesTo]);
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
      ->andWhere(['type' => Fact::TYPE_ONETIME])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->onetimesFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->onetimesTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }
}
