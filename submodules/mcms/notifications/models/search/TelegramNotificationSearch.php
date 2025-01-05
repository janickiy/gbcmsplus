<?php

namespace mcms\notifications\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\notifications\models\TelegramNotification;

/**
 * TelegramNotificationSearch represents the model behind the search form about `mcms\notifications\models\TelegramNotification`.
 */
class TelegramNotificationSearch extends TelegramNotification
{
  public $createdFrom;
  public $createdTo;
  public $updatedFrom;
  public $updatedTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'is_send', 'is_important', 'is_news', 'from_module_id', 'created_at', 'updated_at', 'user_id', 'notifications_delivery_id', 'from_user_id'], 'integer'],
      [['message', 'language', 'createdFrom', 'createdTo', 'updatedFrom', 'updatedTo'], 'safe'],
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
    $query = TelegramNotification::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);
    $query->joinWith('user');
    $dataProvider->setSort([
      'attributes' =>
        [
          'id',
          'user_id' => [
            'asc' => ['username' => SORT_ASC],
            'desc' => ['username' => SORT_DESC],
          ],
          'is_send',
          'is_important',
          'is_news',
          'from_module_id',
          'created_at',
          'updated_at',
          'message',
        ],
      'defaultOrder' => [
        'updated_at' => SORT_DESC
      ],
    ]);


    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->createdFrom) . ' midnight')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->createdTo) . ' tomorrow') - 1]);
    }
    if ($this->updatedFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedFrom) . ' midnight')]);
    }
    if ($this->updatedTo) {
      $query->andFilterWhere(['<', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedTo) . ' tomorrow') - 1]);
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'is_send' => $this->is_send,
      'is_important' => $this->is_important,
      'is_news' => $this->is_news,
      'from_module_id' => $this->from_module_id,
      'user_id' => $this->user_id,
      'notifications_delivery_id' => $this->notifications_delivery_id,
      'from_user_id' => $this->from_user_id,
      'language' => $this->language,
    ]);

    $query->andFilterWhere(['like', 'message', $this->message]);

    return $dataProvider;
  }
}
