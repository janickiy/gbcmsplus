<?php

namespace mcms\promo\models\search;

use mcms\user\components\api\NotAvailableUserIds;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\promo\models\PrelandDefaults;

/**
 * PrelandDefaultsSearch represents the model behind the search form about `mcms\promo\models\PrelandDefaults`.
 */
class PrelandDefaultsSearch extends PrelandDefaults
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'user_id', 'created_at', 'updated_at', 'type', 'status', 'stream_id', 'source_id'], 'integer'],
      [['operators'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
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
    $query = PrelandDefaults::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['created_at' => SORT_DESC],
        'attributes' => [
          'id',
          'user_id',
          'created_at',
          'operators' => false,
        ]
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    $notAvailableUserIds = (new NotAvailableUserIds([
      'userId' => Yii::$app->user->id,
      'skipCurrentUser' => false,
    ]))->getResult();

    if (count($notAvailableUserIds) > 0) {
      $query->andFilterWhere(['not in', 'user_id', $notAvailableUserIds]);
      $query->orWhere(['is', 'user_id', null]);
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'user_id' => $this->user_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'status' => $this->status,
      'type' => $this->type,
      'stream_id' => $this->stream_id,
      'source_id' => $this->source_id,
    ]);

    if ($this->operators) {
      $query->andWhere(['like', 'operators', '"' . $this->operators . '"']);
    }

    // Скрытие элементов недоступных пользователей
    Yii::$app->user->identity->filterUsersItems($query, $this, 'user_id');

    return $dataProvider;
  }
}