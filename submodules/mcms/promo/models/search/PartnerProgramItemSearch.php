<?php

namespace mcms\promo\models\search;

use mcms\promo\models\PartnerProgramItem;
use yii\data\ActiveDataProvider;

/**
 * Поиск условий в партнерской программе
 */
class PartnerProgramItemSearch extends PartnerProgramItem
{
  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    $scenarios = parent::scenarios();

    return array_merge($scenarios, [
      static::SCENARIO_DEFAULT => array_merge(
        $scenarios[static::SCENARIO_DEFAULT],
        ['id']
      ),
    ]);
  }

  public function rules()
  {
    return [
      [['id', 'operator_id', 'landing_id'], 'integer'],
      [['rebill_percent', 'buyout_percent'], 'number'],
    ];
  }

  /**
   * Поиск
   * @param $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = static::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['id' => SORT_ASC],
      ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'operator_id' => $this->operator_id,
      'landing_id' => $this->landing_id,
      'rebill_percent' => $this->rebill_percent,
      'buyout_percent' => $this->buyout_percent,
    ]);

    return $dataProvider;
  }
}