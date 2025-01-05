<?php

namespace mcms\support\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\support\models\SupportCategory;

/**
 * SupportCategorySearch represents the model behind the search form about `mcms\support\models\SupportCategory`.
 */
class SupportCategorySearch extends SupportCategory
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'is_disabled', 'created_at', 'updated_at'], 'integer'],
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
    $query = SupportCategory::find();

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
      'is_disabled' => $this->is_disabled,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    return $dataProvider;
  }
}
