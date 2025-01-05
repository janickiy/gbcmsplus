<?php
namespace mcms\statistic\models\mysql;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\AbstractStatistic;
use mcms\statistic\components\StatisticQuery;
use mcms\user\Module;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class Referrals
 * @package mcms\statistic\models\mysql
 */
abstract class BaseReferrals extends AbstractStatistic
{
  const TABLE = 'referral_incomes';

  public $users;
  public $user_id;
  /** @var bool Включить в результат счетчик рефералов */
  protected $includeReferralsCount = false;
  /** @var bool Включить в результат реферальный процент */
  protected $includeReferralsPercent = false;

  /** @var  array кэш для хранения Итого */
  private $_results;


  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge(parent::rules(), [
      [['user_id', 'users'], 'safe'],
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'start_date' => Yii::_t('statistic.statistic.start_date'),
      'end_date' => Yii::_t('statistic.statistic.end_date'),
      'users' => Yii::_t('statistic.statistic.users'),
      'date' => Yii::_t('statistic.statistic.date'),
    ];
  }

  /**
   * Перевод для колонок грида
   * @return array
   */
  public function gridColumnLabels()
  {
    return array_filter([
      'user_id' => Yii::_t('statistic.statistic.partner'),
      'referral_id' => Yii::_t('statistic.statistic.referral'),
      'profit_rub' => Yii::_t('statistic.statistic.profit_rub'),
      'profit_eur' => Yii::_t('statistic.statistic.profit_eur'),
      'profit_usd' => Yii::_t('statistic.statistic.profit_usd'),
      'referral_percent' => Yii::_t('statistic.statistic.referral_percent'),
      'referrals_count' => Yii::_t('statistic.statistic.referral_count'),
    ]);
  }

  /**
   * Получение сгруппированной статистики
   * @return ActiveDataProvider
   */
  public function getStatisticGroup()
  {
    $this->handleOpenCloseFilters();
    $query = $this->getQuery($this->getSelectFields());

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['user_id' => SORT_DESC],
        'attributes' => [
          'user_id',
          'referral_id',
          'profit_rub',
          'profit_eur',
          'profit_usd',
          !$this->includeReferralsCount ?: 'referrals_count',
          !$this->includeReferralsPercent ?: 'referral_percent',
        ]
      ],
    ]);

    return $dataProvider;
  }

  /**
   * @inheritdoc
   */
  public function handleFilters(Query &$query)
  {
    /** @var Module $usersModule */
    $usersModule = Yii::$app->getModule('users');

    /** @var $query StatisticQuery */
    $query
      ->andFilterWhere(['>=', 'st.date', $this->formatDateDB($this->start_date)])
      ->andFilterWhere(['<=', 'st.date', $this->formatDateDB($this->end_date)]);

    $query->andFilterWhere(['st.user_id' => $this->user_id]);
    $query->andFilterWhere(['st.user_id' => $this->users]);
    Yii::$app->user->identity->filterUsersItems($query, 'st', 'user_id');
  }

  /**
   * @inheritdoc
   */
  public function getFilterFields()
  {
    return [
      'users',
      'user_id',
    ];
  }

  /**
   * @param array $row
   * @param string $field
   * @return string
   */
  public function formatUserName(array $row, $field = 'user_id')
  {
    return sprintf('#%s %s',
      $row[$field],
      $row['userName']
    );
  }

  /**
   * @return array
   */
  public function getSelectFields()
  {
    $fields = [
      'user_id' => 'st.user_id',
      'userName' => 'u.email',
      'profit_rub' => 'SUM(profit_rub)',
      'profit_eur' => 'SUM(profit_eur)',
      'profit_usd' => 'SUM(profit_usd)',
    ];

    if ($this->includeReferralsCount) $fields['referrals_count'] = 'COUNT(DISTINCT(st.referral_id))';
    if ($this->includeReferralsPercent) $fields['referral_percent'] = 'ups.referral_percent';

    return $fields;
  }

  /**
   * @inheritdoc
   */
  protected function getQueryInternal($userField, $groupBy, array $select)
  {
    $q = (new StatisticQuery())
      ->select($select)
      ->from(static::TABLE . ' st')
      ->groupBy($groupBy)
      ->leftJoin('users u', 'u.id = ' . $userField);
    if ($this->includeReferralsPercent) $q->leftJoin('user_payment_settings ups', 'ups.user_id = st.user_id');
    $this->handleFilters($q);

    return $q;
  }

  /**
   * @return array|bool получить строку ИТОГО
   */
  private function getResults()
  {
    if ($this->_results) return $this->_results;
    $subQuery = '(' . $this->getQuery($this->getSelectFields())->createCommand()->getRawSql() . ')';
    $subQueryAlias = 'rows';
    $subQuerySelects = $this->getSelectFields();

    $querySelects = [];
    foreach ($subQuerySelects AS $fieldName => $expression) {

      switch ($fieldName) {
        case 'referrals_count':
          if ($this->includeReferralsCount) $querySelects[$fieldName] = new Expression(
            'SUM(rows.referrals_count)'
          );
          break;
        case 'profit_rub':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.profit_rub)'
          );
          break;
        case 'profit_usd':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.profit_usd)'
          );
          break;
        case 'profit_eur':
          $querySelects[$fieldName] = new Expression(
            'SUM(rows.profit_eur)'
          );
          break;
      }
    }

    $query = (new Query())
      ->select($querySelects)
      ->from([$subQueryAlias => $subQuery]);

    return $this->_results = $query->one();
  }

  /**
   * @param $field
   * @return mixed
   */
  public function getResultValue($field)
  {
    return ArrayHelper::getValue($this->getResults(), $field);
  }
}
