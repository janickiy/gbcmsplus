<?php

namespace mcms\holds\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\holds\models\HoldProgram;

/**
 * HoldProgramSearch represents the model behind the search form about `mcms\holds\models\HoldProgram`.
 */
class HoldProgramSearch extends HoldProgram
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'is_default'], 'integer'],
      [['name', 'description'], 'safe'],
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
    $query = HoldProgram::find();

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
      'is_default' => $this->is_default,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'description', $this->description]);

    return $dataProvider;
  }
}
