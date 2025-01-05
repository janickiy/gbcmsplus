<?php

namespace mcms\statistic\models\mysql;

use mcms\user\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\db\Expression;
use yii\data\ArrayDataProvider;
use mcms\common\module\api\join\Query as JoinQuery;

class UserDayGroupStatistic extends Model
{
  const TABLE_NAME = 'statistic_day_user_group';
  const SCENARIO_FIND_IN_DATE_RANGE = 'find_in_date_range';
  const SCENARIO_FIND_AGGREGATED = 'find_grouped';

  public $userId;
  public $startDate;
  public $endDate;
  public $order;

  public function rules()
  {
    return [
      ['userId', 'required'],
      ['userId', 'integer'],
      ['userId', 'safe'],
      [['startDate', 'endDate'], 'required', 'on' => self::SCENARIO_FIND_IN_DATE_RANGE],
      [['startDate', 'endDate', 'order'], 'safe'],
      [['startDate', 'endDate'], 'date', 'format' => 'php:Y-m-d'],
    ];
  }

  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_FIND_IN_DATE_RANGE => ['userId', 'startDate', 'endDate'],
      self::SCENARIO_FIND_AGGREGATED => ['userId', 'startDate', 'endDate'],
    ]);
  }

  public function getByDateRange()
  {
    $this->setScenario(self::SCENARIO_FIND_IN_DATE_RANGE);
    if (!$this->validate()) {
      \yii\helpers\VarDumper::dump($this->attributes);
      \yii\helpers\VarDumper::dump($this->errors);
      die;
      return null;
    }

    $query = (new Query())
      ->from(['st' => self::TABLE_NAME])
      ->andWhere(['user_id' => $this->userId])
      ->andWhere(['>=', 'date', $this->startDate])
      ->andWhere(['<=', 'date', $this->endDate]);

    $dataProvider = new ArrayDataProvider([
      'allModels' => $query->all(),
      'pagination' => false,
    ]);

    return $dataProvider;
  }

  public function getSum($field)
  {
    $this->setScenario(self::SCENARIO_FIND_AGGREGATED);
    if (!$this->validate()) {
      return null;
    }

    $query = (new Query());
      $query->from(['st' => self::TABLE_NAME])
      ->andWhere(['user_id' => $this->userId])
      ->andFilterWhere(['>=', 'date', $this->startDate])
      ->andFilterWhere(['<=', 'date', $this->endDate]);

    return $query->sum($field);
  }

  /**
   * Получение запроса на активных рефералов пользователя
   * @param int $userId
   * @return \yii\db\Query
   */
  protected function getActiveReferralsQuery()
  {
    $query = (new Query());
    $query->from(['st' => self::TABLE_NAME])
      ->select(['st.user_id'])
      ->andWhere(['or', ['>=', 'st.count_ons', 0], ['>=', 'st.count_rebills', 0], ['>=', 'st.count_onetimes', 0]])
      ->groupBy('st.user_id');

    if ($this->startDate) {
      $query->filterWhere(['>=', 'st.date', Yii::$app->formatter->asDate($this->startDate, 'php:Y-m-d')]);
    }
    if ($this->endDate) {
      $query->filterWhere(['<=', 'st.date', Yii::$app->formatter->asDate($this->endDate, 'php:Y-m-d')]);
    }

    Yii::$app->getModule('users')->api('referrals')->joinByUser($query, 'st', 'user_id', $this->userId);

    return $query;
  }

  /**
   * Получение запроса на топ партенеров
   * @return ActiveQuery
   */
  public function getTopPartnersQuery()
  {
    $query = (new Query());
    $query->from(['st' => self::TABLE_NAME])
      ->select([
        'u.id',
        'u.username',
        'u.email',
        'p.topname',
        'SUM(st.count_ons) as count_ons',
        'SUM(st.count_offs) as count_offs',
        'SUM(st.count_rebills) as count_rebills',
        'SUM(st.count_onetimes) as count_onetimes',
        'SUM(st.count_solds) as count_solds',
        'SUM(st.count_ons + st.count_solds + st.count_onetimes) as count_cpa_revshare_ons',
        'SUM(st.sum_rebill_profit_rub) as sum_rebill_profit_rub',
        'SUM(st.sum_rebill_profit_eur) as sum_rebill_profit_eur',
        'SUM(st.sum_rebill_profit_usd) as sum_rebill_profit_usd',
        'SUM(st.sum_onetime_profit_rub) as sum_onetime_profit_rub',
        'SUM(st.sum_onetime_profit_eur) as sum_onetime_profit_eur',
        'SUM(st.sum_onetime_profit_usd) as sum_onetime_profit_usd',
        'SUM(st.sum_sold_profit_rub) as sum_sold_profit_rub',
        'SUM(st.sum_sold_profit_eur) as sum_sold_profit_eur',
        'SUM(st.sum_sold_profit_usd) as sum_sold_profit_usd',
      ])
      ->andWhere([
        'or',
        ['>=', 'st.count_ons', 0],
        ['>=', 'st.count_rebills', 0],
        ['>=', 'st.count_onetimes', 0]
      ])
      ->groupBy('st.user_id');

    if ($this->startDate) {
      $query->andFilterWhere(['>=', 'st.date', Yii::$app->formatter->asDate($this->startDate, 'php:Y-m-d')]);
    }

    if ($this->endDate) {
      $query->andFilterWhere(['<=', 'st.date', Yii::$app->formatter->asDate($this->endDate, 'php:Y-m-d')]);
    }

    if (in_array($this->order, [
      'count_ons', 'count_rebills', 'count_onetimes', 'count_solds', 'count_cpa_revshare_ons'
    ])) {
      $query->orderBy([$this->order => SORT_DESC]);
    }

    $usersByRolesApi = Yii::$app->getModule('users')
      ->api('usersByRoles', ['partner']);

    $usersByRolesApi->join(
      new JoinQuery(
        $query,
        'assign',
        ['INNER JOIN', 'st.user_id', '=', 'assign'],
        []
      )
    );

    $query->andWhere(['in', 'assign.item_name', 'partner']);

    $resellersIds = Yii::$app->getModule('users')
      ->api('usersByRoles', ['reseller'])
      ->setMapParams(['id', 'id'])
      ->getMap()
    ;

    $notAvailableUserIds = Yii::$app->getModule('users')
      ->api('notAvailableUserIds', [
        'userId' => current($resellersIds),
      ])
      ->getResult();
    if ($notAvailableUserIds) {
      $query->andFilterWhere(['not in', 'st.user_id', $notAvailableUserIds]);
    }

    /** @var \mcms\user\components\api\UserParams $userParamsApi */
    $userParamsApi = Yii::$app->getModule('users')->api('userParams');
    $userParamsApi->join(
      new JoinQuery(
        $query,
        'p',
        ['LEFT JOIN', 'st.user_id', '=', 'p'],
        ['user_id' => 'p.user_id']
      )
    );

    /** @var \mcms\user\components\api\User $userApi */
    $userApi = Yii::$app->getModule('users')->api('user');
    $userApi->join(
      new JoinQuery(
        $query,
        'u',
        ['LEFT JOIN', 'st.user_id', '=', 'u'],
        ['user_id' => 'u.id']
      )
    );
    /** @var User $identity */
    $identity = Yii::$app->user->identity;
    $identity->filterUsersItems($query, 'st', 'user_id');

    return $query;
  }

  /**
   * Получение ид активных рефералов пользователя
   * @return array
   */
  public function getActiveReferralsIds()
  {
    return $this->getActiveReferralsQuery()->column();
  }

  /**
   * Получение количества активных рефералов
   * @return int
   */
  public function getActiveReferralsCount()
  {
    return $this->getActiveReferralsQuery()->count();
  }

  public function getActivePartnersCount()
  {
    $query = (new Query());

    $query->from(['st' => self::TABLE_NAME]);
    $query->select(['date', new Expression('COUNT(*) as `count`')]);
    $query->groupBy(['st.date']);
    $query->andWhere(['>', 'count_hits', 0]);

    if ($this->startDate) {
      $query->andFilterWhere(['>=', 'st.date', Yii::$app->formatter->asDate($this->startDate, 'php:Y-m-d')]);
    }
    if ($this->endDate) {
      $query->andFilterWhere(['<=', 'st.date', Yii::$app->formatter->asDate($this->endDate, 'php:Y-m-d')]);
    }

    $usersByRolesApi = Yii::$app->getModule('users')
      ->api('usersByRoles', ['partner']);

    $usersByRolesApi->join(
      new JoinQuery(
        $query,
        'assign',
        ['INNER JOIN', 'st.user_id', '=', 'assign'],
        []
      )
    );

    $query->andWhere(['in', 'assign.item_name', 'partner']);

    // Скрытие статистики недоступных пользователей
    User::filterUsersItemsByUser($this->userId, $query, 'st', 'user_id');

    $result = [];
    foreach ($query->each() as $item) {
      $result[$item['date']] = $item['count'];
    }
    return $result;
  }


  /**
   * Получение топа партнеров
   * @return ActiveDataProvider
   */
  public function getTopPartners()
  {
    return new ActiveDataProvider([
      'query' => $this->getTopPartnersQuery()
    ]);
  }

}