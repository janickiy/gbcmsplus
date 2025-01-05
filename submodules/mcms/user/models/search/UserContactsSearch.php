<?php

namespace mcms\user\models\search;

use mcms\user\models\UserContact;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class UserContactsSearch
 * @package mcms\user\models\search
 */
class UserContactsSearch extends Model
{
  /** @var int */
  public $id;
  /** @var int */
  public $user_id;
  /** @var int */
  public $type;
  /** @var string */
  public $data;
  /** @var int */
  public $is_deleted;

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
      [['id', 'user_id', 'type', 'is_deleted',], 'integer'],
      [['data', 'createdFrom', 'createdTo'], 'string'],
    ];
  }

  /**
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search(array $params)
  {
    $query = UserContact::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $dataProvider->setSort([
      'defaultOrder' => [
        'is_deleted' =>  SORT_ASC,
      ],
      'attributes' =>
        [
          'is_deleted',
          'user_id',
          'created_at',
          'updated_at',
          'type',
          'data',
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    $query->andFilterWhere([
      'id' => $this->id,
      'user_id' => $this->user_id,
      'type' => $this->type,
      'is_deleted' => is_numeric($this->is_deleted) ? !$this->is_deleted : null,
    ]);
    $query->andFilterWhere(['like', 'data', $this->data]);

    if ($this->createdFrom) {
      $query->andWhere(['>=', 'created_at', strtotime($this->createdFrom)]);
    }

    if ($this->createdTo) {
      $query->andWhere(['<=', 'created_at', strtotime($this->createdTo . ' 23:59:59')]);
    }

    return $dataProvider;
  }
}
