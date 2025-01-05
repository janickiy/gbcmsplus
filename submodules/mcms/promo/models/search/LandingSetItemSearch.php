<?php

namespace mcms\promo\models\search;

use mcms\promo\models\LandingSetItem;
use yii\data\ActiveDataProvider;

class LandingSetItemSearch extends LandingSetItem
{
  public function scenarios()
  {
    $scenarios = parent::scenarios();

    return array_merge($scenarios, [
      static::SCENARIO_DEFAULT => array_merge($scenarios[static::SCENARIO_DEFAULT], ['id']),
    ]);
  }

  public function rules()
  {
    return [
      [['id', 'landing_id', 'operator_id'], 'integer'],
      [['is_enabled'], 'boolean'],
    ];
  }

  public function search($params)
  {
    $query = static::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'set_id' => $this->set_id,
      'landing_id' => $this->landing_id,
      'operator_id' => $this->operator_id,
      'is_disabled' => $this->is_disabled,
    ]);

    return $dataProvider;
  }
}