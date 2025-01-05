<?php

namespace mcms\promo\models\search;

use mcms\promo\models\Cap;
use mcms\promo\models\ExternalProvider;
use mcms\promo\models\Landing;
use mcms\promo\models\Operator;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * CapSearch represents the model behind the search form about `mcms\promo\models\Cap`.
 */
class CapSearch extends Cap
{
  public $activeFrom1;
  public $activeFrom2;
  public $country_id;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['external_provider_id', 'operator_id', 'country_id', 'is_blocked', 'landing_id', 'service_id', 'day_limit'], 'integer'],
      [['activeFrom1', 'activeFrom2'], 'safe']
    ];
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
    $query = Cap::find();
    $query->joinWith(['externalProvider', 'operator']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'attributes' => [
          'is_blocked',
          'provider_id',
          'operator_id',
          'country_id',
          'landing_id',
          'active_from',
          'day_limit',
        ]
      ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    $query->andWhere([Cap::tableName() . '.status' => Cap::STATUS_ACTIVE]);

    $query->andFilterWhere([Cap::tableName() . '.provider_id' => $this->provider_id])
      ->andFilterWhere([Cap::tableName() . '.operator_id' => $this->operator_id])
      ->andFilterWhere([Cap::tableName() . '.external_provider_id' => $this->external_provider_id])
      ->andFilterWhere([Cap::tableName() . '.is_blocked' => $this->is_blocked])
      ->andFilterWhere([Cap::tableName() . '.service_id' => $this->service_id]);

    $query->andFilterWhere([
      'or',
      [ExternalProvider::tableName() . '.country_id' => $this->country_id],
      [Operator::tableName() . '.country_id' => $this->country_id]
    ]);

    if ($this->activeFrom1) {
      $query->andFilterWhere(['>=', Cap::tableName() . '.active_from', strtotime($this->activeFrom1)]);
    }
    if ($this->activeFrom2) {
      $query->andFilterWhere(['<=', Cap::tableName() . '.active_from', strtotime($this->activeFrom2 . ' 23:59:59')]);
    }

    if ($this->landing_id) {
      $query->joinWith('landing');
      $query->andFilterWhere([Landing::tableName() . '.id' => $this->landing_id]);
    }

    return $dataProvider;
  }

}
