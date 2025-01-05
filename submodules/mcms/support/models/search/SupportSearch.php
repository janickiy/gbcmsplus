<?php

namespace mcms\support\models\search;

use Yii;
use yii\data\ActiveDataProvider;
use mcms\support\models\Support;
use mcms\support\Module;

/**
 * SupportSearch represents the model behind the search form about `mcms\support\models\Support`.
 */
class SupportSearch extends Support
{

  public $createdFrom;
  public $createdTo;
  public $nameLink;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [
        [
          'id',
          'support_category_id',
          'created_by',
          'delegated_to',
          'is_opened',
          'created_at',
          'updated_at'
        ],
        'integer'
      ],
      ['has_unread_messages', 'boolean'],
      ['nameLink', 'string'],
      [['support_category_id', 'createdFrom', 'createdTo'], 'safe']
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return parent::scenarios();
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
    $query = Support::find()
      ->joinWith(['supportCategory', 'text'])
      ->groupBy('support.id');

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'id' => SORT_DESC,
        ]
      ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere(
      [
        self::tableName() . '.' . 'id' => $this->id,
        self::tableName() . '.' . 'support_category_id' => $this->support_category_id,
        self::tableName() . '.' . 'created_by' => $this->created_by,
        self::tableName() . '.' . 'delegated_to' => $this->delegated_to,
        self::tableName() . '.' . 'is_opened' => $this->is_opened,
        self::tableName() . '.' . 'has_unread_messages' => $this->has_unread_messages,
        self::tableName() . '.' . 'owner_has_unread_messages' => $this->owner_has_unread_messages,
      ]
    );

    $query->andFilterWhere(['like', self::tableName() . '.' . 'name', $this->nameLink]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->createdTo . ' tomorrow') - 1]);
    }

    // Скрытие элементов недоступных пользователей
    Yii::$app->user->identity->filterUsersItems($query, $this);

    return $dataProvider;
  }
}
