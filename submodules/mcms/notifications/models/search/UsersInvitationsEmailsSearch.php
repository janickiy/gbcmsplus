<?php

namespace mcms\notifications\models\search;


use mcms\notifications\models\UserInvitationEmail;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UsersInvitationsEmailsSearch
 * @package mcms\notifications\models\search
 */
class UsersInvitationsEmailsSearch extends Model
{
  /** @var int */
  public $id;
  /** @var string */
  public $from;
  /** @var string */
  public $header;
  /** @var string */
  public $is_complete;

  /** @var string */
  public $createdFrom;
  /** @var string */
  public $createdTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'is_complete',], 'integer'],
      [['from', 'header', 'createdFrom', 'createdTo'], 'string'],
    ];
  }

  /**
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search(array $params)
  {
    $query = UserInvitationEmail::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'defaultOrder' => [
        'id' =>  SORT_ASC,
      ],
      'attributes' =>
        [
          'id',
          'is_complete',
          'created_at',
          'updated_at',
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'is_complete' => $this->is_complete,
    ]);

    $query->andFilterWhere(['like', 'header', $this->header]);

    if ($this->createdFrom) {
      $query->andWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
    }

    if ($this->createdTo) {
      $query->andWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}