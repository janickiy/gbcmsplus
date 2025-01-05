<?php

namespace mcms\promo\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\Domain;
use yii\helpers\ArrayHelper;

/**
 * DomainSearch represents the model behind the search form about `mcms\promo\models\Domain`.
 */
class DomainSearch extends Domain
{
  const SCENARIO_ADMIN = 'admin';
  public $onlyPartnerVisible;

  public function rules()
  {
    return [
      [['id', 'status', 'user_id', 'type', 'created_by', 'created_at', 'updated_at', 'is_system'], 'integer'],
      [['url'], 'safe'],
    ];
  }

  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return array_merge(Model::scenarios(), [
      self::SCENARIO_ADMIN => ['id', 'url', 'status', 'type', 'is_system', 'created_by', 'user_id'],
    ]);
  }

  public function search($params)
  {
    $query = Domain::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    if ($this->scenario !== self::SCENARIO_ADMIN) {
      $this->status = self::STATUS_ACTIVE;
    }

    $this->load($params);
    if (!$this->validate()) {
      return $dataProvider;
    }

    if(ArrayHelper::getValue($params, 'onlyPartnerVisible')) {
      $this->status = [self::STATUS_ACTIVE, self::STATUS_BANNED];
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'status' => $this->status,
      'is_system' => $this->is_system,
      'type' => $this->type,
      'created_by' => $this->created_by,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'url', $this->url]);

    if ($this->user_id) {
      $query->andFilterWhere(ArrayHelper::getValue($params, 'system')
        ? ['or', ['=', 'is_system', true], ['=', 'user_id', $this->user_id]]
        : ['=', 'user_id', $this->user_id]);
    }

    return $dataProvider;
  }
}
