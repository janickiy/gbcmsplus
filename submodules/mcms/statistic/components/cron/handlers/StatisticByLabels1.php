<?php

namespace mcms\statistic\components\cron\handlers;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\join\Query as JoinQuery;
use mcms\promo\Module as PromoModule;
use mcms\statistic\components\api\LabelStatisticEnable;
use mcms\statistic\components\cron\AbstractTableHandler;
use mcms\statistic\models\UserStatSettings;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * TODO: Выпилить после того, как полностью прекратим прием меток label1 и label2
 * Class StatisticByLabels1
 * @package mcms\statistic\components\cron\handlers
 */
class StatisticByLabels1 extends AbstractTableHandler
{

  /**
   * @var string
   */
  private $table = 'statistic_label_group_1';

  /** @var  array|null */
  private static $_ignoreSourceIds;
  /** @var  array|null */
  private static $_ignoreUserSources;
  /** @var  bool */
  private $isGloballyEnabled;

  /**
   * @inheritdoc
   */
  public function run()
  {
    $this->isGloballyEnabled = (new LabelStatisticEnable())->getIsEnabledGlobally();
    if (!$this->isGloballyEnabled) {
      echo 'Label statistic is disabled globally' . PHP_EOL;
      return;
    }

    $today = strtotime('today');
    for (
      $date = $this->params->fromDate;
      strtotime($date) <= $today;
      $date = date('Y-m-d', strtotime($date . ' +1day'))
    ) {
      $this->log('~~~~ updating ' . $date . ' ~~~~');

      $this->populateHits($date);
      $this->populateOns($date);
      $this->populateOffs($date);
      $this->populateRebills($date);
      $this->populateOnetime($date);
      $this->populateSolds($date);
      $this->assingOtherFields($date);
    }
  }

  /**
   * @param string $date
   */
  private function populateHits($date)
  {
    $this->log('hits...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateHitsValues())})
      {$this->getPopulateHitsSelect($date)}
      ON DUPLICATE KEY UPDATE
        count_hits = VALUES(count_hits),
        count_uniques = VALUES(count_uniques),
        count_tb = VALUES(count_tb)
    ")->execute();
  }

  /**
   * @param string $date
   * @return string
   */
  private function getPopulateHitsSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateHitsValues())
      ->from('hits h')
      ->innerJoin('hit_params hp', 'h.id = hp.hit_id')
      ->where($this->getDateCondition($date))
      ->groupBy([
        'hp.label1',
        'hp.label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_cpa'
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }

  /**
   * @return array
   */
  private function getPopulateHitsValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_hits' => new Expression("COUNT(id)"),
      'count_uniques' => new Expression("SUM(is_unique)"),
      'count_tb' => new Expression("SUM(is_tb)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => 'is_cpa',
    ];
  }


  /**
   * @param $date
   */
  private function populateOns($date)
  {
    $this->log('ons...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateOnsValues())})
        {$this->getPopulateOnsSelect($date)}
        ON DUPLICATE KEY UPDATE
          count_ons = VALUES(count_ons)
    ")
      ->execute();
  }

  /**
   * @return array
   */
  private function getPopulateOnsValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_ons' => new Expression("COUNT(id)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => 'is_cpa',
    ];
  }

  /**
   * @param string $date
   * @return string
   */
  private function getPopulateOnsSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateOnsValues())
      ->from('subscriptions s')
      ->innerJoin('hit_params hp', 's.hit_id = hp.hit_id')
      ->where($this->getDateCondition($date))
      ->andWhere(['s.is_cpa' => 0])
      ->groupBy([
        'hp.label1',
        'hp.label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_cpa'
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }

  /**
   * @param string $date
   */
  private function populateOffs($date)
  {
    $this->log('offs...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateOffsValues())})
        {$this->getPopulateOffsSelect($date)}
        ON DUPLICATE KEY UPDATE
          count_offs = VALUES(count_offs)
    ")
      ->execute();
  }

  /**
   * @return array
   */
  private function getPopulateOffsValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_offs' => new Expression("COUNT(id)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => 'is_cpa',
    ];
  }


  /**
   * @param string $date
   * @return string
   */
  private function getPopulateOffsSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateOnsValues())
      ->from('subscription_offs s')
      ->innerJoin('hit_params hp', 's.hit_id = hp.hit_id')
      ->where($this->getDateCondition($date))
      ->andWhere(['s.is_cpa' => 0])
      ->groupBy([
        'hp.label1',
        'hp.label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_cpa'
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }


  /**
   * @param $date
   */
  private function populateRebills($date)
  {
    $this->log('rebills...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateRebillsValues())})
        {$this->getPopulateRebillsSelect($date)}
        ON DUPLICATE KEY UPDATE
          count_rebills = VALUES(count_rebills),
          sum_profit_rub = VALUES(sum_profit_rub),
          sum_profit_eur = VALUES(sum_profit_eur),
          sum_profit_usd = VALUES(sum_profit_usd)
    ")
      ->execute();
  }

  /**
   * @return array
   */
  private function getPopulateRebillsValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_rebills' => new Expression("COUNT(id)"),
      'sum_profit_rub' => new Expression("SUM(profit_rub)"),
      'sum_profit_eur' => new Expression("SUM(profit_eur)"),
      'sum_profit_usd' => new Expression("SUM(profit_usd)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => 'is_cpa',
    ];
  }


  /**
   * @param $date
   * @return string
   */
  private function getPopulateRebillsSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateRebillsValues())
      ->from('subscription_rebills sr')
      ->innerJoin('hit_params hp', 'sr.hit_id = hp.hit_id')
      ->where($this->getDateCondition($date))
      ->andWhere(['sr.is_cpa' => 0])
      ->groupBy([
        'hp.label1',
        'hp.label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
        'is_cpa'
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }


  /**
   * @param $values
   * @return string
   */
  private function getInsertFields($values)
  {
    return implode(', ', array_map(function($field){
      return sprintf('`%s`', $field);
    }, array_keys($values)));
  }

  /**
   * @param $date
   */
  private function populateOnetime($date)
  {
    $this->log('onetime...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateOnetimeValues())})
        {$this->getPopulateOnetimeSelect($date)}
        ON DUPLICATE KEY UPDATE
          count_onetime = VALUES(count_onetime),
          sum_profit_rub = VALUES(sum_profit_rub),
          sum_profit_eur = VALUES(sum_profit_eur),
          sum_profit_usd = VALUES(sum_profit_usd)
    ")
      ->execute();
  }

  /**
   * @param $date
   * @return string
   */
  private function getPopulateOnetimeSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateOnetimeValues())
      ->from('onetime_subscriptions os')
      ->where($this->getDateCondition($date))
      ->andWhere(['is_visible_to_partner' => 1])
      ->groupBy([
        'label1',
        'label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }

  /**
   * @return array
   */
  private function getPopulateOnetimeValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_onetime' => new Expression("COUNT(id)"),
      'sum_profit_rub' => new Expression("SUM(profit_rub)"),
      'sum_profit_eur' => new Expression("SUM(profit_eur)"),
      'sum_profit_usd' => new Expression("SUM(profit_usd)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => new Expression("1"),
      'user_id' => 'user_id'
    ];
  }


  /**
   * @param $date
   */
  private function populateSolds($date)
  {
    $this->log('sold...');
    Yii::$app->db->createCommand("
      INSERT INTO {$this->table} ({$this->getInsertFields($this->getPopulateSoldsValues())})
        {$this->getPopulateSoldsSelect($date)}
        ON DUPLICATE KEY UPDATE
          count_sold = VALUES(count_sold),
          sum_profit_rub = VALUES(sum_profit_rub),
          sum_profit_eur = VALUES(sum_profit_eur),
          sum_profit_usd = VALUES(sum_profit_usd)
    ")
      ->execute();
  }

  /**
   * @param $date
   * @return string
   */
  private function getPopulateSoldsSelect($date)
  {
    $q = (new Query())
      ->select($this->getPopulateSoldsValues())
      ->from('sold_subscriptions ss')
      ->innerJoin('hit_params hp', 'ss.hit_id = hp.hit_id')
      ->where($this->getDateCondition($date))
      ->andWhere(['is_visible_to_partner' => 1])
      ->groupBy([
        'hp.label1',
        'hp.label2',
        'date',
        'source_id',
        'landing_id',
        'operator_id',
        'platform_id',
        'landing_pay_type_id',
      ])
      ->orderBy(new Expression('NULL'));

    if ($this->isGloballyEnabled) {
      $q->andWhere(['NOT IN', 'source_id', self::getIgnoreSourceIds()]);
    }

    return $q->createCommand()
      ->getRawSql();
  }

  /**
   * @return array
   */
  private function getPopulateSoldsValues()
  {
    return [
      'label1' => 'label1',
      'label2' => 'label2',
      'label1_hash' => new Expression("LEFT(MD5(IFNULL(label1, '')), 8)"),
      'label2_hash' => new Expression("LEFT(MD5(IFNULL(label2, '')), 8)"),
      'count_sold' => new Expression("COUNT(id)"),
      'sum_profit_rub' => new Expression("SUM(profit_rub)"),
      'sum_profit_eur' => new Expression("SUM(profit_eur)"),
      'sum_profit_usd' => new Expression("SUM(profit_usd)"),
      'date' => 'date',
      'source_id' => 'source_id',
      'landing_id' => 'landing_id',
      'operator_id' => 'operator_id',
      'platform_id' => 'platform_id',
      'landing_pay_type_id' => 'landing_pay_type_id',
      'is_cpa' => new Expression("1"),
      'user_id' => 'user_id',
    ];
  }

  /**
   * @return array
   * @throws \mcms\common\exceptions\api\ApiResultInvalidException
   * @throws \mcms\common\exceptions\api\ClassNameNotDefinedException
   */
  private static function getIgnoreUserSources()
  {
    if (!is_null(self::$_ignoreUserSources)) return self::$_ignoreUserSources;

    $query = (new Query())
      ->select(UserStatSettings::tableName() . '.user_id')
      ->where(['is_label_stat_enabled' => 0])
      ->from(UserStatSettings::tableName())
    ;

    /** @var \mcms\promo\components\api\Source $api */
    /** @var PromoModule $promoModule */
    $promoModule = Yii::$app->getModule('promo');
    $api = $promoModule->api('source');

    $join = (new JoinQuery(
      $query,
      'source',
      [
        'RIGHT JOIN',
        UserStatSettings::tableName() . '.user_id',
        '=',
        'sources'
      ],
      [
        'source_id' => 'sources.id'
      ]
    ));

    $api->join(
      $join,
      'user_id'
    );

    return self::$_ignoreUserSources = $query->all();
  }

  /**
   * @return array
   */
  private static function getIgnoreSourceIds()
  {
    if (!is_null(self::$_ignoreSourceIds)) return self::$_ignoreSourceIds;

    return self::$_ignoreSourceIds = array_unique(ArrayHelper::getColumn(self::getIgnoreUserSources(), 'source_id'));
  }

  /**
   * @param $date
   */
  private function assingOtherFields($date)
  {
    $this->log('other updates...');
    Yii::$app->db->createCommand("UPDATE {$this->table} slg
        INNER JOIN sources s ON s.id=slg.source_id
        SET slg.user_id = s.user_id
        WHERE
          (slg.date = :date)
          AND (slg.user_id = 0)")
      ->bindValue(':date', $date)
      ->execute();
  }

  /**
   * @param $date
   * @return string
   */
  private function getDateCondition($date)
  {
    return "`date` = '$date'";
  }

  /**
   * @inheritdoc
   */
  public function log($string, $params = [])
  {
    parent::log('[' . Yii::$app->formatter->asDatetime('now') . '] ' . $string . PHP_EOL);
  }
}