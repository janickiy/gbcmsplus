<?php

namespace mcms\promo\models\search;

use mcms\promo\models\OfferCategory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OfferCategorySearch represents the model behind the search form about `mcms\promo\models\OfferCategory`.
 */
class OfferCategorySearch extends OfferCategory
{
  const SCENARIO_ADMIN = 'admin';

  /**
   * @return array
   */
  public function rules()
  {
    return [
      [['id', 'status', 'created_at', 'updated_at'], 'integer'],
      [['name', 'code'], 'safe'],
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'code', 'status'],
    ]);
  }

  /**
   * @param $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = OfferCategory::find();

    $query->with(['activeLandings']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'attributes' => [
        'id',
        'code',
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
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);
    $query->andFilterWhere(['like', 'code', $this->code]);

    return $dataProvider;
  }
}
