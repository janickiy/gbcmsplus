<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\TrafficBlock;

/**
 * TrafficBlockSearch represents the model behind the search form about `mcms\promo\models\TrafficBlock`.
 */
class TrafficBlockSearch extends TrafficBlock
{
  public $createdFrom;
  public $createdTo;
  public $updatedFrom;
  public $updatedTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'user_id', 'operator_id', 'provider_id', 'is_blacklist'], 'integer'],
      [['createdFrom', 'createdTo', 'updatedFrom', 'updatedTo'], 'safe'],
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
    $query = TrafficBlock::find();

    $query->with(['operator', 'user']);

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
      'user_id' => $this->user_id,
      'operator_id' => $this->operator_id,
      'provider_id' => $this->provider_id,
      'is_blacklist' => $this->is_blacklist,
    ]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }
    if ($this->updatedFrom) {
      $query->andFilterWhere(['>=', 'updated_at', strtotime($this->updatedFrom)]);
    }
    if ($this->updatedTo) {
      $query->andFilterWhere(['<=', 'updated_at', strtotime($this->updatedTo . ' 23:59:59')]);
    }



    return $dataProvider;
  }
}
