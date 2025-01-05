<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\BuyoutCondition;

/**
 * BuyoutConditionSearch represents the model behind the search form about `mcms\promo\models\BuyoutCondition`.
 */
class BuyoutConditionSearch extends BuyoutCondition
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'operator_id', 'user_id', 'type', 'buyout_minutes', 'is_buyout_only_after_1st_rebill', 'is_buyout_only_unique_phone'], 'integer'],
      [['name'], 'safe'],
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
    $query = BuyoutCondition::find();

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
      'type' => $this->type,
      'buyout_minutes' => $this->buyout_minutes,
      'is_buyout_only_after_1st_rebill' => $this->is_buyout_only_after_1st_rebill,
      'is_buyout_only_unique_phone' => $this->is_buyout_only_unique_phone,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    return $dataProvider;
  }
}
