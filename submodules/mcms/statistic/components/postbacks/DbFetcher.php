<?php

namespace mcms\statistic\components\postbacks;


use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\Query;

/**
 * Class DbFetcher обрабатывает данные постбеков, получаемые из БД
 * @package mcms\statistic\components\postbacks
 *
 * @see Sender отправщик постбеков
 */
class DbFetcher extends Object implements PostbackFetcherInterface
{
  public $type;

  /** @var array Массив хитайди. В случае для ребиллов массив должен быть в виде [transId => hiId, ...] */
  public $hitIds;

  public $isDuplicatePostback;

  public $timeFrom;

  public $timeTo;

  public $maxAttempts;

  /**
   * @var Query
   */
  protected $query;

  /**
   * Инициализация объекта.
   * Проверяется тип постбеков и строится запрос на получение данных
   */
  public function init()
  {
    if (!$this->type) {
      throw new InvalidConfigException('Param $type is required');
    }

    if ($this->isDuplicatePostback === null) {
      throw new InvalidParamException('isDuplicatePostback is not set');
    }

    switch ($this->type) {
      case Sender::TYPE_SUBSCRIPTION:
        $this->query = $this->getSubscriptionsQuery();
        break;
      case Sender::TYPE_REBILL:
        $this->query = $this->getRebillsQuery();
        break;
      case Sender::TYPE_ONETIME_SUBSCRIPTION:
        $this->query = $this->getOnetimeQuery();
        break;
      case Sender::TYPE_SUBSCRIPTION_OFF:
        $this->query = $this->getOffsQuery();
        break;
      case Sender::TYPE_SUBSCRIPTION_SELL:
        $this->query = $this->getSoldsQuery();
        break;
      case Sender::TYPE_COMPLAIN:
        $this->query = $this->getComplainsQuery();
        break;
    }
  }

  /**
   * @return int
   */
  public function getCount()
  {
    return $this->query->count();
  }

  /**
   * Возвращает по одной записи для постбеков
   *
   * @param int $size
   * @return \Generator
   */
  public function each($size = 100)
  {
    $unbufferedDb = new \yii\db\Connection([
      'dsn' => Yii::$app->db->dsn,
      'username' => Yii::$app->db->username,
      'password' => Yii::$app->db->password,
      'charset' => Yii::$app->db->charset,
    ]);
    $unbufferedDb->open();
    $unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

    foreach ($this->query->each($size, $unbufferedDb) as $row) {
      yield $row;
    }

    $unbufferedDb->close();
  }

  /**
   * Возвращает пачками записи для постбеков
   *
   * @param int $size
   * @return \Generator
   */
  public function batch($size = 100)
  {
    $unbufferedDb = new \yii\db\Connection([
      'dsn' => Yii::$app->db->dsn,
      'username' => Yii::$app->db->username,
      'password' => Yii::$app->db->password,
      'charset' => Yii::$app->db->charset,
    ]);
    $unbufferedDb->open();
    $unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

    foreach ($this->query->batch($size, $unbufferedDb) as $rows) {
      yield $rows;
    }

    $unbufferedDb->close();
  }

  /**
   * @return Query
   */
  protected function getSubscriptionsQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.id AS subscription_id',
        'st.hit_id',
        'st.time',
        'st.phone',
        'st.operator_id',
        'st.landing_id',
      ])
      ->from('subscriptions AS st');

    if (!$this->isDuplicatePostback) {
      $query->andWhere('source.is_notify_subscribe = 1');
    }

    $this->addSourceQuery($query);
    $this->addPostbackQuery($query, 'subscription_id');
    $this->addQueryParams($query);
    $this->addIsCpaCondition($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @return Query
   */
  protected function getRebillsQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.hit_id',
        'st.trans_id',
        'st.time',
        'st.profit_rub',
        'st.profit_usd',
        'st.profit_eur',
      ])
      ->from('subscription_rebills AS st');

    if (!$this->isDuplicatePostback) {
      $query->andWhere('source.is_notify_rebill = 1');
    }

    $this->addSourceQuery($query);
    $this->addSubscriptionsQuery($query);
    $this->addPostbackQuery($query, 'subscription_rebill_id');
    $this->addQueryParams($query);
    $this->addIsCpaCondition($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @return Query
   */
  protected function getOnetimeQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.id AS subscription_id',
        'st.hit_id',
        'st.time',
        'st.profit_rub',
        'st.profit_usd',
        'st.profit_eur',
        'st.phone',
        'st.operator_id',
        'st.landing_id',
      ])
      ->from('onetime_subscriptions AS st')
      ->andWhere('st.is_visible_to_partner = 1');

    if (!$this->isDuplicatePostback) {
      $query->andWhere('source.is_notify_cpa = 1');
    }

    $this->addSourceQuery($query);
    $this->addPostbackQuery($query, 'onetime_subscription_id');
    $this->addQueryParams($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @return Query
   */
  protected function getOffsQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.time',
        'st.hit_id',
      ])
      ->from('subscription_offs AS st');

    if (!$this->isDuplicatePostback) {
      $query->andWhere('source.is_notify_unsubscribe = 1');
    }

    $this->addSourceQuery($query);
    $this->addPostbackQuery($query, 'subscription_off_id');
    $this->addQueryParams($query);
    $this->addSubscriptionsQuery($query);
    $this->addIsCpaCondition($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @return Query
   */
  protected function getSoldsQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.time',
        'st.hit_id',
        'st.profit_rub',
        'st.profit_usd',
        'st.profit_eur',
      ])
      ->from('sold_subscriptions AS st')
      ->andWhere('st.is_visible_to_partner = 1');

    if (!$this->isDuplicatePostback) {
      $query->andWhere('source.is_notify_cpa = 1');
    }

    $this->addSourceQuery($query);
    $this->addPostbackQuery($query, 'sold_subscription_id');
    $this->addQueryParams($query);
    $this->addSubscriptionsQuery($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @return Query
   */
  protected function getComplainsQuery()
  {
    $query = (new Query())
      ->select([
        'st.id',
        'st.time',
        'st.hit_id',
        'st.description',
      ])
      ->from('complains AS st');

    $this->addSourceQuery($query);
    $this->addPostbackQuery($query, 'complain_id');
    $this->addQueryParams($query);
    $this->addSubscriptionsQuery($query);
    $this->addQueryCondition($query);

    return $query;
  }

  /**
   * @param Query $query
   */
  protected function addIsCpaCondition(Query &$query)
  {
    $query->andWhere(['st.is_cpa' => 0]);
  }

  /**
   * @param Query $query
   */
  protected function addQueryCondition(Query &$query)
  {
    $query->andFilterWhere(['>=', 'st.time', $this->timeFrom]);
    $query->andFilterWhere(['<=', 'st.time', $this->timeTo]);

    if (empty($this->hitIds)) {
      return;
    }

    if ((int)$this->type !== Sender::TYPE_REBILL) {
      $query->andWhere(['st.hit_id' => $this->hitIds]);
      return;
    }

    $values = [];

    if (!is_array($this->hitIds)) {
      Yii::error('Для ребиллов hitIds должен быть в виде [transId => hiId, ...]. Сейчас:' . json_encode($this->hitIds));
      $query->where('0 = 1');
    }

    foreach ($this->hitIds as $transId => $hitId) {
      $values[] = [
        'st.hit_id' => $hitId,
        'st.trans_id' => $transId
      ];
    }
    // в такое условие надо подставлять в виде $values = [['st.hit_id' => 123, 'st.trans_id' => 124], [...]]
    $query->andWhere(['in', ['st.hit_id', 'st.trans_id'], $values]);
  }

  /**
   * @param Query $query
   */
  protected function addSourceQuery(Query &$query)
  {
    $query->addSelect([
      'source.id AS source_id',
      'source.user_id AS user_id',
      'source.stream_id',
      'source.name AS source_name',
      'source.hash AS source_hash',
      'source.postback_url',
      'source.use_global_postback_url',
      'source.use_complains_global_postback_url',
      'source.is_notify_subscribe',
      'source.is_notify_rebill',
      'source.is_notify_unsubscribe',
      'source.is_notify_cpa',
      'source.send_all_get_params_to_pb',
      'ups.postback_url AS global_postback_url',
      'ups.complains_postback_url'
    ]);

    $query->andWhere([
      'or',
      [
        'and',
        'source.postback_url <> \'\'',
        'source.postback_url IS NOT NULL'
      ],
      'source.use_global_postback_url = 1',
      'source.use_complains_global_postback_url = 1',
      $this->isDuplicatePostback ? '1=1' : '1=0'
    ]);
    $query->andWhere('source.status = 1');

    $query->innerJoin('sources AS source', 'st.source_id = source.id');
    $query->leftJoin('user_promo_settings ups', 'source.user_id = ups.user_id');

    if ($this->isDuplicatePostback) {
      $this->addUserPaymentSettings($query);
    }
  }

  /**
   * @param Query $query
   * @param string $field
   */
  protected function addPostbackQuery(Query &$query, $field)
  {
    $query->andWhere([
      'or',
      ['and', 'pb.status = 0', 'pb.errors < :maxAttempts', ],
      'pb.id IS NULL',
    ]);

    $query->addSelect([
      'pb.errors as fail_attempt',
      'pb.status',
      'pb.status_code',
      'pb.is_duplicate',
    ]);

    $query->leftJoin('postbacks AS pb', "pb.$field = st.id AND pb.is_duplicate = :is_duplicate", [
      ':is_duplicate' => (int)$this->isDuplicatePostback
    ]);
  }

  /**
   * @param Query $query
   */
  protected function addSubscriptionsQuery(Query &$query)
  {
    $query->addSelect([
      'sub.id AS subscription_id',
      'sub.phone',
      'sub.operator_id',
      'sub.landing_id',
    ]);
    $query->innerJoin('subscriptions AS sub', 'sub.hit_id = st.hit_id');
  }

  /**
   * @param Query $query
   */
  protected function addQueryParams(Query &$query)
  {

    $query->addParams([
      ':maxAttempts' => $this->maxAttempts
    ]);
  }

  /**
   * TRICKY: пока используется только если включен флаг [[isDuplicatePostback]]
   * @param Query $query
   */
  protected function addUserPaymentSettings(Query &$query)
  {
    $query->addSelect(['upays.currency']);
    $query->innerJoin('user_payment_settings AS upays', 'upays.user_id = source.user_id');
  }
}