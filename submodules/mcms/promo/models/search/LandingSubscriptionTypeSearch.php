<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\LandingSubscriptionType;

/**
 * LandingSubscriptionTypeSearch represents the model behind the search form about `mcms\promo\models\LandingSubscriptionType`.
 */
class LandingSubscriptionTypeSearch extends LandingSubscriptionType
{
  const SCENARIO_ADMIN = 'admin';
  public $createdFrom;
  public $createdTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'created_at', 'updated_at'], 'integer'],
      [['name', 'createdFrom', 'createdTo', 'status'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id',  'name', 'status', 'created_at', 'createdFrom', 'createdTo'],
    ]);
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
    $query = LandingSubscriptionType::find();

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $dataProvider = new ActiveDataProvider(
      [
        'query' => $query,
        'sort' => [
          'defaultOrder' => [
            'id' => SORT_DESC,
          ]
        ],
      ]
    );

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere(
      [
        'id' => $this->id,
        'status' => $this->status,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
      ]
    );

    $query->andFilterWhere(['like', 'name', $this->name]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom . ' 00:00:00')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }


    return $dataProvider;
  }
}
