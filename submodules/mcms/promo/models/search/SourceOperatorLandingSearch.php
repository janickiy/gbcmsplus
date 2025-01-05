<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\SourceOperatorLanding;

/**
 * OperatorSearch represents the model behind the search form about `mcms\promo\models\SourceOperatorLanding`.
 */
class SourceOperatorLandingSearch extends SourceOperatorLanding
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'source_id', 'profit_type', 'operator_id', 'landing_id', 'is_changed', 'landing_choose_type'], 'integer'],
      [['change_description'], 'safe'],
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
    $query = SourceOperatorLanding::find();

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

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'source_id' => $this->source_id,
      'profit_type' => $this->profit_type,
      'operator_id' => $this->operator_id,
      'landing_id' => $this->landing_id,
      'is_changed' => $this->is_changed,
      'landing_choose_type' => $this->landing_choose_type,
    ]);

    $query->andFilterWhere(['like', 'change_description', $this->change_description]);


    return $dataProvider;
  }
}
