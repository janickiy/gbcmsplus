<?php

namespace mcms\promo\models\search;

use mcms\promo\models\LandingSet;
use mcms\promo\models\PartnerProgram;
use yii\data\ActiveDataProvider;

/**
 * Поиск партнерских программ
 */
class PartnerProgramSearch extends PartnerProgram
{
  public $createdFrom;
  public $createdTo;
  public $updatedFrom;
  public $updatedTo;

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    $scenarios = parent::scenarios();

    return array_merge($scenarios, [
      static::SCENARIO_DEFAULT => array_merge(
        $scenarios[static::SCENARIO_DEFAULT],
        ['id', 'createdFrom', 'createdTo', 'updatedFrom', 'updatedTo']
      ),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return array_merge(
      parent::rules(),
      [
        [['createdFrom', 'createdTo', 'updatedFrom', 'updatedTo'], 'safe'],
      ]
    );
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
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }


    $query->andFilterWhere([
      'id' => $this->id,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);
    $query->andFilterWhere(['like', 'description', $this->description]);

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