<?php

namespace mcms\payments\models\search;

use mcms\payments\models\search\dataproviders\UserPaymentDataProvider;
use mcms\payments\models\UserPayment;
use mcms\payments\models\UserPaymentSetting;
use mcms\payments\Module;
use Yii;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * UserPaymentSearch represents the model behind the search form about `mcms\payments\models\UserPayment`.
 */
class UserPaymentSearch extends UserPayment
{
  const SCENARIO_RESELLER_SEARCH = 'reseller_search';
  const DATE_RANGE_DELIMITER = ' - ';

  public $amount_from;
  public $invoice_amount_from;
  public $amount_to;
  public $invoice_amount_to;
  public $created_at_from;
  public $created_at_to;
  public $created_at_range;
  public $pay_period_end_date_range;
  public $pay_period_end_date_from;
  public $pay_period_end_date_to;
  public $payed_at_from;
  public $payed_at_to;
  public $payed_at_range;
  public $ignore_user_id;
  public $onlyEarlyPayment;
  public $onlyPartners;
  public $status;
  public $commission;
  public $pay_terms;
  /**
   * @var int
   */
  public $resellerCompany;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [[
        'resellerCompany', 'id', 'pay_period_end_date', 'created_at', 'updated_at', 'payed_at', 'is_hold'], 'integer'],
      [[
        'id', 'currency', 'amount_to', 'amount_from', 'invoice_amount_to', 'invoice_amount_from', 'user_id',
        'created_at_from', 'created_at_to', 'pay_period_end_date_from', 'pay_period_end_date_to', 'payed_at_from',
        'payed_at_to', 'ignore_user_id', 'wallet_type',
        'status', 'processing_type', 'pay_period_end_date_range', 'created_at_range', 'payed_at_range', 'pay_terms'
      ], 'safe'],
      [['amount', 'invoice_amount'], 'number'],
      ['wallet_type', 'each', 'rule' => ['integer']],
      [['type'], 'in', 'range' => array_keys(self::getTypes()), 'except' => self::SCENARIO_RESELLER_SEARCH],
    ];
  }

  /**
   * @param $attribute
   */
  public function checkResellerStatuses($attribute)
  {
    $value = $this->{$attribute};
    if (is_array($value)) {
      if (count(array_intersect_key(self::getStatuses(), $value)) == count($value)) return;
      $this->addError($attribute);
      return;
    } else {
      if (!in_array($value, array_keys(self::getStatuses()))) {
        $this->addError($attribute);
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    return array_merge(Model::scenarios(), [
      self::SCENARIO_RESELLER_SEARCH => [
        'id', 'created_at_from', 'created_at_to', 'amount_from', 'invoice_amount_from',
        'invoice_amount_to', 'amount_to', 'payed_at_from', 'payed_at_to', 'resellerCompany',
        'currency', 'is_hold', 'status', 'ignore_user_id', 'wallet_type', 'processing_type', 'pay_period_end_date_range', 'created_at_range', 'payed_at_range', 'pay_terms'
      ]
    ]);
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return UserPaymentDataProvider
   */
  public function search($params)
  {
    $query = UserPayment::find();
    /* @var $query ActiveQuery */
    $query->joinWith([
        'user',
        'userPaymentSetting',
      ]);

    $dataProvider = new UserPaymentDataProvider(['query' => $query]);

    if (array_key_exists('description', $dataProvider->getSort()->attributes)) {
      unset($dataProvider->getSort()->attributes['description']);
    }

    $this->load($params);
    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    $this->handleFilters($query);

    return $dataProvider;
  }

  /**
   * @inheritdoc
   */
  public function beforeValidate()
  {
    if ($this->scenario !== self::SCENARIO_RESELLER_SEARCH) {
      return parent::beforeValidate();
    }
    return true;
  }

  /**
   * @param $query ActiveQuery
   * @return mixed
   */
  public function handleFilters($query)
  {
    // grid filtering conditions
    $query->andFilterWhere([
      self::tableName() . '.id' => $this->id,
      self::tableName() . '.wallet_type' => $this->wallet_type,
      self::tableName() . '.status' => $this->status,
      self::tableName() . '.currency' => $this->currency,
      self::tableName() . '.is_hold' => $this->is_hold,
      UserPaymentSetting::tableName() . '.pay_terms' => $this->pay_terms,
    ]);

    $query->andFilterWhere([
      '!=', self::tableName() . '.user_id', $this->ignore_user_id
    ]);
    $query->andFilterWhere([self::tableName() . '.user_id' => $this->user_id]);

    if ($this->created_at_from) {
      $query->andFilterWhere(['>=', self::tableName() . '.created_at', strtotime($this->created_at_from)]);
    }
    if ($this->created_at_to) {
      $query->andFilterWhere(['<=', self::tableName() . '.created_at', strtotime($this->created_at_to .
        ' + 1day')]);
    }

    if (!empty($this->pay_period_end_date_range) && strpos($this->pay_period_end_date_range, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->pay_period_end_date_range);
      $query->andFilterWhere([
        'between',
        self::tableName() . '.pay_period_end_date',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1
      ]);
      $query->andWhere([
        self::tableName() . '.status' => UserPayment::STATUS_DELAYED,
      ]);
    }
    if (!empty($this->created_at_range) && strpos($this->created_at_range, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->created_at_range);
      $query->andFilterWhere([
        'between',
        self::tableName() . '.created_at',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1
      ]);
    }
    if (!empty($this->payed_at_range) && strpos($this->payed_at_range, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->payed_at_range);
      $query->andFilterWhere([
        'between',
        self::tableName() . '.payed_at',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1
      ]);
    }

    if ($this->pay_period_end_date_from) {
      $query->andFilterWhere(['>=', self::tableName() . '.pay_period_end_date', strtotime($this->pay_period_end_date_from)]);
    }
    if ($this->pay_period_end_date_to) {
      $query->andFilterWhere(['<=', self::tableName() . '.pay_period_end_date', strtotime($this->pay_period_end_date_to .
        ' + 1day')]);
    }

    if ($this->payed_at_from) {
      $query->andFilterWhere(['>=', self::tableName() . '.payed_at', strtotime($this->payed_at_from)]);
    }
    if ($this->payed_at_to) {
      $query->andFilterWhere(['<=', self::tableName() . '.payed_at', strtotime($this->payed_at_to . ' + 1day')]);
    }
    if ($this->onlyPartners) {
      $query->andWhere([
        'not in', self::tableName() . '.user_id',
        [self::getResellerId()]
      ]);
    }
    if ($this->resellerCompany) {
      $query
        ->joinWith(['userPaymentSetting' => function ($query) {
          /** @var ActiveQuery $query */
          $query->innerJoinWith('resellerCompany rc')
          ->andWhere(['rc.id' => $this->resellerCompany]);
        }]);
    }
    $query->andFilterWhere(['>=', self::tableName() . '.amount', $this->amount_from])
      ->andFilterWhere(['<=', self::tableName() . '.amount', $this->amount_to])
      ->andFilterWhere(['>=', self::tableName() . '.invoice_amount', $this->invoice_amount_from])
      ->andFilterWhere(['<=', self::tableName() . '.invoice_amount', $this->invoice_amount_to])
      ->andFilterWhere([self::tableName() . '.type' => $this->type])
      ->andFilterWhere([self::tableName() . '.processing_type' => $this->processing_type])
      ->andFilterWhere(['like', self::tableName() . '.description', $this->description])
      ->andFilterWhere(['like', self::tableName() . '.response', $this->response]);

    $this->onlyEarlyPayment && $query->joinWith('invoicesEarlyPayment', true, 'INNER JOIN');

    /** @var Module $module */
    $module = Yii::$app->getModule('payments');
    if ($module->isUserCanProcessAllPayments() === false) {
      $query->andWhere([
        'or',
        [self::tableName() . '.status' => [UserPayment::STATUS_AWAITING, UserPayment::STATUS_DELAYED]],
        [self::tableName() . '.processed_by' => Yii::$app->user->id]
      ]);
    }

    return $query;
  }
}
