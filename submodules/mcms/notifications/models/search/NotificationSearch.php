<?php

namespace mcms\notifications\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\notifications\models\Notification;

/**
 * NotificationSearch represents the model behind the search form about `mcms\notifications\models\Notification`.
 */
class NotificationSearch extends Notification
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'module_id', 'notification_type', 'is_disabled', 'use_owner', 'is_important', 'created_at', 'updated_at', 'is_system'], 'integer'],
      [['event', 'emails', 'from', 'template', 'header'], 'safe'],
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
    $query = Notification::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'module_id' => $this->module_id,
      'notification_type' => $this->notification_type,
      'is_disabled' => $this->is_disabled,
      'use_owner' => $this->use_owner,
      'is_important' => $this->is_important,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'is_system' => $this->is_system,
    ]);

    $query->andFilterWhere(['like', 'event', $this->event])
      ->andFilterWhere(['like', 'emails', $this->emails])
      ->andFilterWhere(['like', 'from', $this->from])
      ->andFilterWhere(['like', 'template', $this->template])
      ->andFilterWhere(['like', 'header', $this->header]);

    return $dataProvider;
  }
}
