<?php

namespace mcms\payments\models\wallet;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\payments\models\wallet\Wallet;

/**
 * WalletSearch represents the model behind the search form about `mcms\payments\models\wallet\Wallet`.
 */
class WalletSearch extends Wallet
{
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id'], 'integer'],
      [['name'], 'safe'],
      [['profit_percent', 'usd_min_payout_sum', 'eur_min_payout_sum', 'rub_min_payout_sum'], 'number'],
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
    $query = Wallet::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'profit_percent' => $this->profit_percent,
      'usd_min_payout_sum' => $this->usd_min_payout_sum,
      'eur_min_payout_sum' => $this->eur_min_payout_sum,
      'rub_min_payout_sum' => $this->rub_min_payout_sum,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name]);

    return $dataProvider;
  }
}
