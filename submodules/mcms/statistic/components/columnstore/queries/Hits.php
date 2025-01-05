<?php


namespace mcms\statistic\components\columnstore\queries;

use mcms\statistic\components\columnstore\BaseQuery;
use mcms\statistic\models\cs\Fact;
use RuntimeException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * Экспортируем хиты
 */
class Hits extends BaseQuery
{

  public function init()
  {
    parent::init();

    $existedId = $this->getExistedIdAtCs();
    if ($existedId) {
      throw new RuntimeException("В выбранном условии уже есть строки в CS. Id из ColumnStore={$existedId}" . PHP_EOL);
    }

    /** поля referer, user_agent будут выгружены только для записей, начиная с этой даты */
    $emptyTxtFieldsUntil = Yii::$app->formatter->asDate('-1month', 'php:Y-m-d');
    $this->addSelect([
      'id' => 'h.id',
      'type' => new Expression(Fact::TYPE_HIT),
      'hit_id' => 'h.id',
      'is_unique' => 'is_unique',
      'is_tb' => 'is_tb',
      'time' => 'h.time',
      'date' => 'h.date',
      'hour' => 'h.hour',
      'operator_id' => 'h.operator_id',
      'country_id' => 'country_id',
      'landing_id' => 'h.landing_id',
      'provider_id' => 'l.provider_id',
      'source_id' => 'h.source_id',
      'source_type' => 's.source_type',
      'user_id' => 's.user_id',
      'stream_id' => 's.stream_id',
      'platform_id' => 'h.platform_id',
      'landing_pay_type_id' => 'h.landing_pay_type_id',
      'traffic_type' => 'h.traffic_type',
      'ip' => 'ip',
      'referer' => new Expression('IF(h.date >= :emptyTxtFieldsUntil, referer, NULL)'),
      'user_agent' => new Expression('IF(h.date >= :emptyTxtFieldsUntil, user_agent, NULL)'),
      'subid1' => 'hp.subid1',
      'subid2' => 'hp.subid2',
      'subid1_hash' => new Expression('IF(hp.subid1 IS NULL OR hp.subid1=\'\', NULL, LEFT(MD5(hp.subid1), 8))'),
      'subid2_hash' => new Expression('IF(hp.subid2 IS NULL OR hp.subid2=\'\', NULL, LEFT(MD5(hp.subid2), 8))'),
      'description' => new Expression('NULL'),
      'trans_id' => new Expression('NULL'),
      'sub_is_fake' => new Expression('NULL'),
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
      ->from('hits h')
      ->leftJoin('hit_params hp', 'hp.hit_id = h.id')
      ->leftJoin('landings l', 'l.id = h.landing_id')
      ->leftJoin('operators o', 'o.id = h.operator_id')
      ->leftJoin('sources s', 's.id = h.source_id')
      ->leftJoin('partners_managers pm', 's.user_id = pm.user_id AND h.date = pm.date')
      ->andFilterWhere(['>=', 'h.date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'h.date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'h.id', $this->cfg->hitsFrom])
      ->andFilterWhere(['<=', 'h.id', $this->cfg->hitsTo])
      ->addParams([
        ':emptyTxtFieldsUntil' => $emptyTxtFieldsUntil,
      ]);
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
      ->andWhere(['type' => Fact::TYPE_HIT])
      ->andFilterWhere(['>=', 'date', $this->cfg->dateFrom])
      ->andFilterWhere(['<=', 'date', $this->cfg->dateTo])
      ->andFilterWhere(['>=', 'id', $this->cfg->hitsFrom])
      ->andFilterWhere(['<=', 'id', $this->cfg->hitsTo])
      ->limit(1)
      ->scalar(Yii::$app->dbCs);
  }
}
