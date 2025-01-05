<?php

namespace mcms\promo\models\search;

use mcms\promo\models\LandingRequestFilter;
use yii\data\ActiveDataProvider;

/**
 * LandingRequestFiltersSearch represents the model behind the search form about `mcms\promo\models\LandingRequestFilters`.
 */
class LandingRequestFiltersSearch extends LandingRequestFilter
{
  public $createdFrom;
  public $createdTo;

  public function rules()
  {
    return [
      [['landing_id', 'created_at', 'updated_at', 'is_active'], 'integer'],
      [['code', 'is_active', 'createdFrom', 'createdTo'], 'safe'],
    ];
  }

  /**
   * @param $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = LandingRequestFilter::find();

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

    $query->andFilterWhere([
      'id' => $this->id,
      'landing_id' => $this->landing_id,
      'code' => $this->code,
      'is_active' => $this->is_active,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom . ' 00:00:00')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}