<?php

namespace mcms\pages\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\pages\models\FaqCategory;

/**
 * FaqCategorySearch represents the model behind the search form about `\mcms\pages\models\FaqCategory`.
 */
class FaqCategorySearch extends FaqCategory
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'sort', 'visible'], 'integer'],
      [['name'], 'string'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
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
    $query = FaqCategory::find();
    // add conditions that should always apply here
    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);
    $this->load($params);
    if (!$this->validate()) {
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'sort' => $this->sort,
      'visible' => $this->visible,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);
    return $dataProvider;
    }
}