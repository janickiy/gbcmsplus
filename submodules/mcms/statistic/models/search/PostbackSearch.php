<?php

namespace mcms\statistic\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\statistic\models\Postback;
use yii\db\Expression;

/**
 * PostbackSearch represents the model behind the search form about `mcms\statistic\models\Postback`.
 */
class PostbackSearch extends Postback
{
  public $transId;
  public $type;
  public $userId;
  public $source_id;
  public $complainTypes;

  public $createdFrom;
  public $createdTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'hit_id', 'subscription_id', 'subscription_rebill_id', 'subscription_off_id', 'sold_subscription_id', 'onetime_subscription_id', 'complain_id', 'source_id', 'status', 'errors', 'time', 'last_time', 'transId', 'type'], 'integer'],
      [['complainTypes'], 'each', 'rule' => ['integer']],
      [['status_code', 'url', 'data', 'createdFrom', 'createdTo', 'userId'], 'safe'],
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
    $query = Postback::find();

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
    $query->andFilterWhere([
      self::tableName() . '.id' => $this->id,
      self::tableName() . '.hit_id' => $this->hit_id,
      self::tableName() . '.subscription_id' => $this->subscription_id,
      self::tableName() . '.subscription_rebill_id' => $this->subscription_rebill_id,
      self::tableName() . '.subscription_off_id' => $this->subscription_off_id,
      self::tableName() . '.sold_subscription_id' => $this->sold_subscription_id,
      self::tableName() . '.onetime_subscription_id' => $this->onetime_subscription_id,
      self::tableName() . '.complain_id' => $this->complain_id,
      self::tableName() . '.source_id' => $this->source_id,
      self::tableName() . '.status' => $this->status,
      self::tableName() . '.errors' => $this->errors,
      self::tableName() . '.last_time' => $this->last_time,
    ]);

    $query->andFilterWhere([
      'or',
      [self::tableName() . '.subscription_id' => $this->transId],
      [self::tableName() . '.subscription_rebill_id' => $this->transId],
      [self::tableName() . '.subscription_off_id' => $this->transId],
      [self::tableName() . '.sold_subscription_id' => $this->transId],
      [self::tableName() . '.onetime_subscription_id' => $this->transId],
      [self::tableName() . '.complain_id' => $this->transId],
    ]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'time', strtotime($this->createdFrom . ' 00:00:00')]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'time', strtotime($this->createdTo . ' 23:59:59')]);
    }

    $query->andFilterWhere(['like', self::tableName() . '.status_code', $this->status_code])
      ->andFilterWhere(['like', self::tableName() . '.url', $this->url])
      ->andFilterWhere(['like', self::tableName() . '.data', $this->data]);

    switch ($this->type) {
      case Postback::TYPE_SUBSCRIPTION:
        $query->andWhere(['not', [self::tableName() . '.subscription_id' => null]]);
        break;
      case Postback::TYPE_SUBSCRIPTION_REBILL:
        $query->andWhere(['not', [self::tableName() . '.subscription_rebill_id' => null]]);
        break;
      case Postback::TYPE_SUBSCRIPTION_OFF:
        $query->andWhere(['not', [self::tableName() . '.subscription_off_id' => null]]);
        break;
      case Postback::TYPE_SOLD_SUBSCRIPTION:
        $query->andWhere(['not', [self::tableName() . '.sold_subscription_id' => null]]);
        break;
      case Postback::TYPE_ONETIME_SUBSCRIPTION:
        $query->andWhere(['not', [self::tableName() . '.onetime_subscription_id' => null]]);
        break;
      case Postback::TYPE_COMPLAIN:
        $query->andWhere(['not', [self::tableName() . '.complain_id' => null]]);
        break;
    }

    if ($this->userId) {
      $query->innerJoin(
        'sources',
        self::tableName() . '.source_id = sources.id AND sources.user_id = :userId',
        [':userId' => $this->userId]
      );
    }

    if (!empty($this->complainTypes)) {
      $query->leftJoin('complains cmpl', 'cmpl.id=complain_id');
      $query->andWhere(['or',
        'cmpl.id IS NULL',
        ['cmpl.type' => $this->complainTypes]
      ]);
    }

    //не отображаем дубли постбеков
    $query->andWhere('is_duplicate = 0');


    return $dataProvider;
  }
}