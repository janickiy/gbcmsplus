<?php

namespace mcms\notifications\models\search;


use mcms\notifications\models\UserInvitationEmail;
use mcms\notifications\models\UserInvitationEmailSent;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UsersInvitationsEmailsSentSearch
 * @package mcms\notifications\models\search
 */
class UsersInvitationsEmailsSentSearch extends Model
{
  /** @var int */
  public $id;
  /** @var int */
  public $invitation_id;
  /** @var int */
  public $invitation_email_id;
  /** @var string */
  public $from;
  /** @var string */
  public $to;
  /** @var string */
  public $header;
  /** @var string */
  public $is_sent;
  /** @var string */
  public $attempts;

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
      [['id','invitation_id', 'invitation_email_id', 'is_sent', 'attempts',], 'integer'],
      [['from', 'to', 'header', 'createdFrom', 'createdTo'], 'string'],
    ];
  }

  /**
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search(array $params)
  {
    $query = UserInvitationEmailSent::find();

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
          'is_sent',
          'invitation_email_id',
          'invitation_id',
          'from',
          'to',
          'attempts',
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
      'invitation_email_id' => $this->invitation_email_id,
      'invitation_id' => $this->invitation_id,
      'is_sent' => $this->is_sent,
      'attempts' => $this->attempts,
    ]);

    $query->andFilterWhere(['like', 'from', $this->from]);
    $query->andFilterWhere(['like', 'to', $this->to]);
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