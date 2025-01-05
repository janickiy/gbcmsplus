<?php

namespace mcms\loyalty\models\search;

use mcms\loyalty\models\LoyaltyBonus;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Модель для поиска бонусов
 */
class LoyaltyBonusSearch extends Model
{
  const DATE_RANGE_DELIMITER = ' - ';

  /** @var  int */
  public $id;
  /** @var  int */
  public $external_id;
  /** @var  float */
  public $fromAmount;
  /** @var  float */
  public $toAmount;
  /** @var string */
  public $type;
  /** @var int */
  public $status;
  /** @var string */
  public $createdDateRange;
  /** @var string */
  public $updatedDateRange;

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'external_id', 'status'], 'integer'],
      [['type'], 'string'],
      [['fromAmount', 'toAmount'], 'number'],
      [['createdDateRange', 'updatedDateRange'], 'safe'],
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
    $query = LoyaltyBonus::find();
    $tableName = LoyaltyBonus::tableName();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'attributes' => [
          // В ожидании => одобрены => отклонены. И все это по дате
          'default' => [
            'desc' => [
              new Expression("FIELD(status,:awaiting,:approved,:declined) ASC", [
                ':awaiting' => LoyaltyBonus::STATUS_AWAITING,
                ':approved' => LoyaltyBonus::STATUS_APPROVED,
                ':declined' => LoyaltyBonus::STATUS_DECLINED,
              ]),
              new Expression($tableName . ".updated_at DESC"),
            ],
          ],
          'id',
          'external_id',
          'amount_usd',
          'type',
          'status',
          'created_at',
          'updated_at',
        ],
        'defaultOrder' => [
          'default' => SORT_DESC,
        ],
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // Синхронизируются все бонусы, а отображаются только заапрувленные
    $query->andWhere(['status' => LoyaltyBonus::STATUS_APPROVED]);

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'external_id' => $this->external_id,
      'type' => $this->type,
      'status' => $this->status,
    ]);

    if ($this->fromAmount) {
      $query->andFilterWhere(['>=', 'amount_usd', $this->fromAmount]);
    }

    if ($this->toAmount) {
      $query->andFilterWhere(['<=', 'amount_usd', $this->toAmount]);
    }

    if (!empty($this->createdDateRange) && strpos($this->createdDateRange, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->createdDateRange);
      $query->andFilterWhere([
        'between',
        $tableName . '.created_at',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1 // прибавляем 1 день и вычитаем 1 секунду, тогда получим таймштамп последней секунды нужного дня.
      ]);
    }

    if (!empty($this->updatedDateRange) && strpos($this->updatedDateRange, '-') !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->updatedDateRange);
      $query->andFilterWhere([
        'between',
        $tableName . '.updated_at',
        strtotime($startDate),
        strtotime($endDate . ' +1day') - 1 // прибавляем 1 день и вычитаем 1 секунду, тогда получим таймштамп последней секунды нужного дня.
      ]);
    }

    return $dataProvider;
  }
}