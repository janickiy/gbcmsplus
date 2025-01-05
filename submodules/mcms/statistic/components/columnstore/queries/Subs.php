<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем подписки
 */
class Subs extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    $this->addSelect([
      'id' => 'sub.id',
      'type' => new Expression(Fact::TYPE_SUB),
      'hit_id' => 'sub.hit_id',
      'is_unique' => new Expression('NULL'),
      'is_tb' => new Expression('NULL'),
      'time' => 'sub.time',
      'date' => 'sub.date',
      'hour' => 'sub.hour',
      'operator_id' => 'sub.operator_id',
      'country_id' => 'country_id',
      'landing_id' => 'sub.landing_id',
      'provider_id' => 'sub.provider_id',
      'source_id' => 'sub.source_id',
      'source_type' => 's.source_type',
      'user_id' => 's.user_id',
      'stream_id' => 's.stream_id',
      'platform_id' => 'sub.platform_id',
      'landing_pay_type_id' => 'sub.landing_pay_type_id',
      'traffic_type' => 'h.traffic_type',
      'ip' => 'ip',
      'referer' => new Expression('NULL'),
      'user_agent' => new Expression('NULL'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => new Expression('NULL'),
      'trans_id' => 'sub.trans_id',
      'sub_is_fake' => 'sub.is_fake',
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
      ->from('subscriptions sub')
      ->leftJoin('hit_params hp', 'hp.hit_id = sub.hit_id')
      ->leftJoin('hits h', 'h.id = sub.hit_id')
      ->leftJoin('operators o', 'o.id = sub.operator_id')
      ->leftJoin('sources s', 's.id = sub.source_id')
      ->leftJoin('partners_managers pm', 's.user_id = pm.user_id AND sub.date = pm.date')
      ->leftJoin('landings l', 'l.id = sub.landing_id')
      ->andFilterWhere(['>=', 'sub.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'sub.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'sub.id', $this->cfg->subsFrom])
      ->andFilterWhere(['<=', 'sub.id', $this->cfg->subsTo]);
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
      ->andWhere(['type' => Fact::TYPE_SUB])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->subsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->subsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }
}
