<?php

namespace mcms\pages\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\pages\models\Faq;

/**
 * FaqSearch represents the model behind the search form about `\mcms\pages\models\Faq`.
 */
class FaqSearch extends Faq
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'sort', 'visible', 'faq_category_id'], 'integer'],
      [['question', 'answer'], 'safe'],
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
    $query = Faq::find();

    // add conditions that should always apply here
    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'attributes' => [
        'id',
        'sort',
        'visible',
      ],
      'defaultOrder' => [
        'id' => SORT_DESC,
      ]
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
      'sort' => $this->sort,
      'visible' => $this->visible,
      'faq_category_id' => $this->faq_category_id,
    ]);

    $query->andFilterWhere(['like', 'question', $this->question])
      ->andFilterWhere(['like', 'answer', $this->answer]);

    return $dataProvider;
  }
}