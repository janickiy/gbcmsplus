<?php

namespace mcms\holds\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\holds\models\HoldProgramRule;

/**
 * HoldProgramRuleSearch represents the model behind the search form about `mcms\holds\models\HoldProgramRule`.
 */
class HoldProgramRuleSearch extends HoldProgramRule
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'hold_program_id', 'country_id', 'unhold_range', 'unhold_range_type', 'min_hold_range', 'min_hold_range_type', 'at_day', 'at_day_type'], 'integer'],
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
    $query = HoldProgramRule::find();

    // add conditions that should always apply here

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
      'hold_program_id' => $this->hold_program_id,
      'country_id' => $this->country_id,
      'unhold_range' => $this->unhold_range,
      'unhold_range_type' => $this->unhold_range_type,
      'min_hold_range' => $this->min_hold_range,
      'min_hold_range_type' => $this->min_hold_range_type,
      'at_day' => $this->at_day,
      'at_day_type' => $this->at_day_type,
    ]);

    return $dataProvider;
  }
}
