<?php

namespace mcms\promo\models\search;

use mcms\promo\models\Landing;
use mcms\promo\models\LandingSet;
use yii\data\ActiveDataProvider;

class LandingSetSearch extends LandingSet
{
  public $createdFrom;
  public $createdTo;
  public $updatedFrom;
  public $updatedTo;
  public $landing_id;

  public function scenarios()
  {
    $scenarios = parent::scenarios();

    return array_merge($scenarios, [
      static::SCENARIO_DEFAULT => array_merge(
        $scenarios[static::SCENARIO_DEFAULT],
        ['id', 'createdFrom', 'createdTo', 'updatedFrom', 'updatedTo', 'landing_id']
      ),
    ]);
  }

  public function rules()
  {
    return [
      ['name', 'string'],
      [['id', 'autoupdate', 'category_id', 'created_by'], 'integer'],
      [['createdFrom', 'createdTo', 'updatedFrom', 'updatedTo', 'landing_id'], 'safe']
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

    $query->andFilterWhere(['like', 'name', $this->name]);

    $query->andFilterWhere([
      'id' => $this->id,
      'category_id' => $this->category_id,
      'autoupdate' => $this->autoupdate,
      'created_by' => $this->created_by,
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

    if ($this->landing_id) {
      $query->joinWith('landings')
        ->groupBy('id')
        ->andWhere([Landing::tableName() . '.id' => $this->landing_id]);
    }

    return $dataProvider;
  }
}