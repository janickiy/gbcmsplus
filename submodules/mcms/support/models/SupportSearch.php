<?php

namespace mcms\support\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class SupportSearch extends Model
{
  public $category;
  public $isOpened = 1;
  public $delegatedTo;
  public $hasUnreadMessages;

  public function rules()
  {
    return [
      [['category', 'isOpened', 'delegatedTo', 'hasUnreadMessages'], 'safe']
    ];
  }

  public function search(array $params)
  {
    $query = Support::find();
    $query->orderBy(['created_at' => 'DESC']);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);
    if (!$this->validate()) return $dataProvider;

    $query->andFilterWhere(['=', 'is_opened', $this->isOpened]);

    $query->andFilterWhere(['=', 'has_unread_messages', $this->hasUnreadMessages]);

    if (!$this->category) {
      $query->andFilterWhere(['=', 'support_category_id', $this->category]);
    }

    if (!$this->delegatedTo) {
      $query->andFilterWhere(['=', 'delegated_to', $this->delegatedTo]);
    }

    return $dataProvider;
  }

  private function getDelegatedTo()
  {
    $delegatedUsers = Support::find()
      ->where("is_opened = :is_opened AND delegated_to IS NOT NULL", [':is_opened' => '1'])
      ->distinct(true);
    $delegatedToUsers = ["null" => Yii::_t('filters.notDelegated')];
    /** @var Support $support */
    foreach ($delegatedUsers->each() as $support) {
      $delegatedToUser = $support->getDelegatedTo()->one();
      $delegatedToUsers[$delegatedToUser->id] = $delegatedToUser->username;
    }

    return $delegatedToUsers;
  }


}
