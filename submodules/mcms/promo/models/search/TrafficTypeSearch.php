<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\TrafficType;

/**
 * TrafficTypeSearch represents the model behind the search form about `mcms\promo\models\TrafficType`.
 */
class TrafficTypeSearch extends TrafficType
{
  const SCENARIO_ADMIN = 'admin';
  public $createdFrom;
  public $createdTo;

  public function rules()
  {
    return [
      [['id', 'status', 'created_at', 'updated_at'], 'integer'],
      [['name', 'createdFrom', 'createdTo'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'status', 'created_at', 'createdFrom', 'createdTo'],
    ]);
  }

  public function search($params)
  {
    $query = TrafficType::find();

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'attributes' => [
        'id',
        'created_at',
        'status',
      ],
      'defaultOrder' => [
        'id' => SORT_DESC,
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'status' => $this->status,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', 'created_at', strtotime($this->createdFrom . ' 00:00:00')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}