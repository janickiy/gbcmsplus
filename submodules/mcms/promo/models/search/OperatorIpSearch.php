<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\OperatorIp;

/**
 * OperatorIpSearch represents the model behind the search form about `mcms\promo\models\OperatorIp`.
 */
class OperatorIpSearch extends OperatorIp
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'operator_id', 'ip', 'mask'], 'integer'],
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
    $query = OperatorIp::find();

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
      'operator_id' => $this->operator_id,
      'ip' => $this->ip,
      'mask' => $this->mask,
    ]);

    return $dataProvider;
  }
}
