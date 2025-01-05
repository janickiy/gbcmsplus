<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\LandingCategory;

/**
 * LandingCategorySearch represents the model behind the search form about `mcms\promo\models\LandingCategory`.
 */
class LandingCategorySearch extends LandingCategory
{
  const SCENARIO_ADMIN = 'admin';

  /**
   * @var int
   */
  public $provider_id;

  public function rules()
  {
    return [
      [['id', 'status', 'created_by', 'created_at', 'updated_at', 'is_not_mainstream', 'provider_id'], 'integer'],
      [['name', 'code'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'code', 'status', 'is_not_mainstream'],
    ]);
  }

  public function search($params)
  {
    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = LandingCategory::find();

    $query->with(['activeLandings']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'attributes' => [
        'id',
        'code',
        'status',
        'is_not_mainstream',
      ],
      'defaultOrder' => [
        'id' => SORT_DESC,
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    if ($this->provider_id) {
      $query->joinWith(['landings']);
      $query->andFilterWhere([
        'landings.provider_id' => $this->provider_id,
      ]);
    }

    $landingCategoryTableName = LandingCategory::tableName();

    $query->andFilterWhere([
      $landingCategoryTableName . '.id' => $this->id,
      $landingCategoryTableName . '.status' => $this->status,
      $landingCategoryTableName . '.created_by' => $this->created_by,
      $landingCategoryTableName . '.is_not_mainstream' => $this->is_not_mainstream,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);
    $query->andFilterWhere(['like', 'code', $this->code]);

    return $dataProvider;
  }
}
