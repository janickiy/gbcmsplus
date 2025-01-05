<?php

namespace mcms\statistic\models\search;

use mcms\currency\models\Currency;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\statistic\models\PostbackData;
use yii\db\Expression;

/**
 * PostbackDataSearch represents the model behind the search form about `mcms\statistic\models\PostbackData`.
 */
class PostbackDataSearch extends PostbackData
{
  /**
   * фильтр по диапазону дат разделен этой строкой
   */
  const DATE_RANGE_DELIMITER = ' - ';

  public $hitId;
  public $transId;
  public $transType;
  public $phone;
  public $partnerId;
  public $sourceId;
  public $currency;
  public $actionDate;

  /**
   * фильтр по дате
   * @var string
   */
  public $dateRange;

  /** @var  string */
  private $_dateFrom;
  /** @var  string */
  private $_dateTo;


  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $status = parent::load($data, $formName);

    // инициализация фильтра по датам
    $this->dateRange = $this->dateRange ?: implode(self::DATE_RANGE_DELIMITER, $this->getDefaultDateRange());
    if (!empty($this->dateRange) && strpos($this->dateRange, '-') !== false) {
      list($this->_dateFrom, $this->_dateTo) = explode(self::DATE_RANGE_DELIMITER, $this->dateRange);
    }
    return $status;
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'provider_id', 'time', 'is_handled', 'hitId', 'partnerId', 'sourceId'], 'integer'],
      [['hitId', 'phone', 'partnerId', 'sourceId',  'transId', 'currency', 'data'], 'filter', 'filter' => 'trim'],
      [['handler_code', 'data', 'dateRange', 'transId', 'transType', 'phone', 'currency', 'actionDate'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = PostbackData::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['id' => SORT_DESC]
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'provider_id' => $this->provider_id,
      'time' => $this->time,
      'is_handled' => $this->is_handled,
    ]);

    $query->andFilterWhere(['like', 'handler_code', $this->handler_code])

      ->andFilterWhere(['like', 'data', $this->data]);

    if ($this->hitId) {
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].param1') LIKE :hitId"),
        new Expression("JSON_EXTRACT(data, '$[*].label1') LIKE :hitId"),
      ], [':hitId' => '%' . $this->hitId . '%']);
    }

    if ($this->transId) {
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].trans_id') LIKE :transId"),
        new Expression("JSON_EXTRACT(data, '$[*].transaction_id') LIKE :transId"),
      ], [':transId' => '%' . $this->transId . '%']);
    }

    if ($this->transType) {
      $query->andWhere(['or',
        new Expression("JSON_CONTAINS(JSON_EXTRACT(data, '$[*].status'), :transType)"),
        new Expression("JSON_CONTAINS(JSON_EXTRACT(data, '$[*].transaction_type'), :transType)"),
      ], [':transType' => '"' . $this->transType . '"']);
    }

    if ($this->phone) {
      $query->andWhere(
        new Expression("JSON_EXTRACT(data, '$[*].phone') LIKE :phone"),
        [':phone' => '%' . $this->phone . '%']
      );
    }

    if ($this->partnerId) {
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].label2') LIKE :partnerId"),
        new Expression("JSON_EXTRACT(data, '$[*].param2') LIKE :partnerId"),
      ], [':partnerId' => '%' . $this->partnerId . '-%']);
    }

    if ($this->sourceId) {
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].label2') LIKE :sourceId"),
        new Expression("JSON_EXTRACT(data, '$[*].param2') LIKE :sourceId"),
      ], [':sourceId' => '%-' . $this->sourceId . '%']);
    }

    if ($this->currency) {
      $currency = Currency::findOne(['code' => $this->currency]);
      $currencyId = $currency ? $currency->id : 0;
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].currency') LIKE :currency_id"),
        new Expression("JSON_CONTAINS(JSON_EXTRACT(data, '$[*].default_currency'), :currency_code)"),
      ], [
        ':currency_id' => '%' . $currencyId . '%',
        ':currency_code' => '"' . $this->currency . '"',
      ]);
    }

    if ($this->actionDate) {
      $query->andWhere(['or',
        new Expression("JSON_EXTRACT(data, '$[*].date') LIKE :actionDate"),
        new Expression("JSON_EXTRACT(data, '$[*].action_date') LIKE :actionDate"),
      ], [':actionDate' => '%' . $this->actionDate . '%']);
    }

    if ($this->_dateFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'time', strtotime($this->_dateFrom)]);
    }
    if ($this->_dateTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'time', strtotime($this->_dateTo . ' tomorrow') - 1]);
    }

    return $dataProvider;
  }

  /**
   * @return string[]
   */
  protected function getDefaultDateRange()
  {
    // показываем 7 дней
    return [
      Yii::$app->formatter->asDate('-7days', 'php:Y-m-d'),
      Yii::$app->formatter->asDate('today', 'php:Y-m-d')
    ];

  }


  /**
   * @return array
   */
  public function getTransTypes()
  {
    return [
      'on' => 'On',
      'off' => 'Off',
      'rebill' => 'Rebill',
      'onetime' => 'Onetime',
      'refund' => 'Refund',
      'complaint' => 'KP complaint', //В МЛ нет типа транзакций у жалоб
    ];
  }


  /**
   * @return array
   */
  public function getHandlers()
  {
    return [
      'kp_common' => 'kp_common',
      'kp_complain' => 'kp_complain',
      'ml_common' => 'ml_common',
      'ml_complain' => 'ml_complain',
      'default_common' => 'default_common',
      'default_complain' => 'default_complain',
    ];
  }

}
