<?php

namespace mcms\payments\models\search;

use mcms\payments\models\UserBalanceInvoice;
use mcms\payments\models\UserPayment;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class ResellerInvoiceSearch
 * @package mcms\payments\models\search
 */
class ResellerInvoiceSearch extends Model
{
  const DATE_RANGE_DELIMITER = ' - ';

  /** @var  int */
  public $id;
  /** @var  int|int[] */
  public $type;
  /** @var  string|string[] */
  public $currency;
  /** @var  float */
  public $fromAmount;
  /** @var  float */
  public $toAmount;
  /** @var string */
  public $dateDateRange;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'type', 'currency', 'fromAmount', 'toAmount', 'dateDateRange'], 'safe']
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
    $query = UserBalanceInvoice::find()->andWhere([
      'user_id' => $this->getResellerId(),
      'type' => [ // TRICKY другие типы пока не поддерживаем
        UserBalanceInvoice::TYPE_PENALTY,
        UserBalanceInvoice::TYPE_COMPENSATION,
      ]
    ]);

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => ['id' => SORT_DESC]
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      UserBalanceInvoice::tableName() . '.id' => $this->id,
      UserBalanceInvoice::tableName() . '.type' => $this->type,
      UserBalanceInvoice::tableName() . '.currency' => $this->currency,
    ]);

    if (is_numeric($this->fromAmount)) {
      $query->andFilterWhere(['>=', UserBalanceInvoice::tableName() . '.amount', $this->fromAmount]);
    }

    if (is_numeric($this->toAmount)) {
      $query->andFilterWhere(['<=', UserBalanceInvoice::tableName() . '.amount', $this->toAmount]);
    }

    if (!empty($this->dateDateRange) && strpos($this->dateDateRange, self::DATE_RANGE_DELIMITER) !== false) {
      list($startDate, $endDate) = explode(self::DATE_RANGE_DELIMITER, $this->dateDateRange);
      $query->andFilterWhere(['between', UserBalanceInvoice::tableName() . '.date', $startDate, $endDate]);
    }

    return $dataProvider;
  }

  /**
   * @return int
   */
  protected function getResellerId()
  {
    return UserPayment::getResellerId();
  }
}
