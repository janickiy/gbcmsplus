<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Provider;

/**
 * ProviderSearch represents the model behind the search form about `mcms\promo\models\Provider`.
 */
class ProviderSearch extends Provider
{

  public $redirectToName;
  const SCENARIO_ADMIN = 'admin';
  const REDIRECT_TABLE_ALIAS = 'redirect';


  public function rules()
  {
    return [
      [['id', 'status', 'redirect_to', 'created_by', 'created_at', 'updated_at'], 'integer'],
      [['name', 'code', 'url', 'redirectToName'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'name', 'url', 'status'],
    ]);
  }

  public function getRedirectToName()
  {
    return $this->redirectTo->name;
  }

  public function search($params)
  {
    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $query = Provider::find()->joinWith(['redirectTo' => function($q){$q->from('providers ' . self::REDIRECT_TABLE_ALIAS);}]);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);
    if (!$this->validate()) return $dataProvider;

    $query->andFilterWhere([
      self::tableName() . '.' . 'id' => $this->id,
      self::tableName() . '.' . 'status' => $this->status,
      self::tableName() . '.' . 'redirect_to' => $this->redirect_to,
      self::tableName() . '.' . 'created_by' => $this->created_by,
    ]);

    $query->andFilterWhere(['like', self::tableName() . '.' . 'name', $this->name])
      ->andFilterWhere(['like', self::tableName() . '.' . 'code', $this->code])
      ->andFilterWhere(['like', self::tableName() . '.' . 'url', $this->url])
      ->andFilterWhere(['like', self::REDIRECT_TABLE_ALIAS . '.' . 'name', $this->redirectToName]);

    return $dataProvider;
  }
}
