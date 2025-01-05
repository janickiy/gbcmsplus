<?php

namespace mcms\notifications\models\search;

use mcms\notifications\models\NotificationsDelivery;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * NotificationsDeliverySearch represents the model behind the search form about `mcms\notifications\models\NotificationsDelivery`.
 */
class NotificationsDeliverySearch extends NotificationsDelivery
{
  public $dateBegin;
  public $dateEnd;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'dateBegin', 'dateEnd', 'notification_type', 'from_module_id', 'is_important', 'is_manual', 'is_news', 'event', 'user_id', 'header'], 'safe'],
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
    $query = NotificationsDelivery::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'defaultOrder' => [
        'created_at' => SORT_DESC,
      ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    if ($this->dateBegin) {
      $query->andWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->dateBegin . ' midnight')]);
    }

    if ($this->dateEnd) {
      $query->andWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->dateEnd . ' tomorrow') - 1]);
    }

    $query
      ->andFilterWhere(['from_module_id' => $this->from_module_id])
      ->andFilterWhere(['is_important' => $this->is_important])
      ->andFilterWhere(['is_manual' => $this->is_manual])
      ->andFilterWhere(['is_news' => $this->is_news])
      ->andFilterWhere(['event' => $this->event])
      ->andFilterWhere(['notification_type' => $this->notification_type])
      ->andFilterWhere(['id' => $this->id])
      ->andFilterWhere(['like', 'header', $this->header])
      ->andFilterWhere(['user_id' => $this->user_id]);

    return $dataProvider;
  }
}
