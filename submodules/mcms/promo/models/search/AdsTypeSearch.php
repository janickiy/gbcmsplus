<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\AdsType;

/**
 * AdsTypeSearch represents the model behind the search form about `mcms\promo\models\AdsType`.
 */
class AdsTypeSearch extends AdsType
{
  const SCENARIO_ADMIN = 'admin';

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'is_default', 'status', 'security', 'profit', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
      [['code', 'name', 'description'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'code', 'name', 'description', 'is_default', 'status', 'security', 'profit'],
    ]);
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
    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = AdsType::find();

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

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'is_default' => $this->is_default,
      'status' => $this->status,
      'security' => $this->security,
      'profit' => $this->profit,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'created_by' => $this->created_by,
      'updated_by' => $this->updated_by,
    ]);

    $query->andFilterWhere(['like', 'code', $this->code])
      ->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'description', $this->description]);

    if (!self::canViewBlocked()) {
      $query->andFilterWhere(['<>', 'status', self::STATUS_BLOCKED]);
    }

    return $dataProvider;
  }
}
