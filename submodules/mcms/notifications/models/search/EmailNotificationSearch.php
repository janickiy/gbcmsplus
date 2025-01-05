<?php

namespace mcms\notifications\models\search;

use mcms\promo\components\UsersHelper;
use Yii;
use yii\data\ActiveDataProvider;
use mcms\notifications\models\EmailNotification;

/**
 * EmailNotificationSearch represents the model behind the search form about `mcms\notifications\models\EmailNotification`.
 */
class EmailNotificationSearch extends EmailNotification
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
      [['id', 'is_send', 'is_important', 'is_news', 'from_module_id', 'to_user_id', 'from_user_id', 'created_at', 'updated_at'], 'integer'],
      [['from', 'username', 'header', 'message', 'createdFrom', 'createdTo', 'updatedFrom', 'updatedTo', 'notifications_delivery_id'], 'safe'],
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
    $query = EmailNotification::find();


    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);
    $query->joinWith('user');
    $dataProvider->setSort([
      'attributes' =>
        [
          'id',
          'to_user_id' => [
            'asc' => ['username' => SORT_ASC],
            'desc' => ['username' => SORT_DESC],
          ],
          'is_send',
          'is_important',
          'is_news',
          'from_module_id',
          'created_at',
          'updated_at',
          'from',
          'header',
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

    // grid filtering conditions
    $query->andFilterWhere([
      'is_send' => $this->is_send,
      'is_important' => $this->is_important,
      'is_news' => $this->is_news,
      'from_module_id' => $this->from_module_id,
      'to_user_id' => $this->to_user_id,
    ]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->createdFrom) . ' midnight')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<', self::tableName() . '.' . 'created_at', strtotime(str_replace('/', '-', $this->createdTo) . ' tomorrow')-1]);
    }
    if ($this->updatedFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedFrom) . ' midnight')]);
    }
    if ($this->updatedTo) {
      $query->andFilterWhere(['<', self::tableName() . '.' . 'updated_at', strtotime(str_replace('/', '-', $this->updatedTo) . ' tomorrow')-1]);
    }

    $query->andFilterWhere(['like', 'from', $this->from])
      ->andFilterWhere(['like', 'username', $this->username])
      ->andFilterWhere(['like', 'header', $this->header])
      ->andFilterWhere(['like', 'message', $this->message])
      ->andFilterWhere(['notifications_delivery_id' => $this->notifications_delivery_id]);

    /*
     * Прячем уведомления, не доступные пользователю
     */
    if ($ignoreUserIds = UsersHelper::getCurrentUserNotAvailableUsers()) {
      $query->andFilterWhere(['not in', self::tableName() . '.' . 'to_user_id', $ignoreUserIds]);
    }

    return $dataProvider;
  }

}
