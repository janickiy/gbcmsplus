<?php

namespace mcms\payments\models\search;

use mcms\payments\models\UserPaymentChunk;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Серч модель для поиска частичных оплат
 */
class PaymentChunksSearch extends Model
{
  /** @var  int */
  public $id;
  /** @var  int */
  public $paymentId;

  /**
   * Creates data provider instance with search query applied
   * @param array $params
   * @return ActiveDataProvider
   */
  public function search($params)
  {
    $query = UserPaymentChunk::find();

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'created_at' => SORT_DESC
        ]
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      UserPaymentChunk::tableName() . '.id' => $this->id,
      UserPaymentChunk::tableName() . '.payment_id' => $this->paymentId,
    ]);

    return $dataProvider;
  }
}
