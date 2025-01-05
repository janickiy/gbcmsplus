<?php

namespace mcms\promo\models\search;

use mcms\promo\models\SubscriptionCorrectCondition;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SubscriptionCorrectConditionSearch represents the model behind the search form about `mcms\promo\models\SubscriptionCorrectCondition`.
 */
class SubscriptionCorrectConditionSearch extends SubscriptionCorrectCondition
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'operator_id', 'user_id', 'landing_id', 'is_active'], 'integer'],
      [['name'], 'string'],
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
    $query = SubscriptionCorrectCondition::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'operator_id' => $this->operator_id,
      'user_id' => $this->user_id,
      'landing_id' => $this->landing_id,
      'is_active' => $this->is_active,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    return $dataProvider;
  }
}
