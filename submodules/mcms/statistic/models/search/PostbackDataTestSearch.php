<?php

namespace mcms\statistic\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\statistic\models\PostbackDataTest;

/**
 * PostbackDataTestSearch represents the model behind the search form about `mcms\statistic\models\PostbackDataTest`.
 */
class PostbackDataTestSearch extends PostbackDataTest
{
  /**
   * фильтр по диапазону дат разделен этой строкой
   */
  const DATE_RANGE_DELIMITER = ' - ';

  public $dateRange;

  /** @var  string */
  private $_dateFrom;
  /** @var  string */
  private $_dateTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'provider_id', 'time', 'status'], 'integer'],
      [['dateRange'], 'safe']
    ];
  }

  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $status = parent::load($data, $formName);

    // инициализация фильтра по датам
    if (!empty($this->dateRange) && strpos($this->dateRange, '-') !== false) {
      list($this->_dateFrom, $this->_dateTo) = explode(self::DATE_RANGE_DELIMITER, $this->dateRange);
    }
    return $status;
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
    $query = PostbackDataTest::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'attributes' => [
          'id', 'provider_id', 'time', 'status'
        ],
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
      'status' => $this->status,
    ]);


    if ($this->_dateFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'time', strtotime($this->_dateFrom)]);
    }
    if ($this->_dateTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'time', strtotime($this->_dateTo . ' tomorrow') - 1]);
    }

    return $dataProvider;
  }
}
