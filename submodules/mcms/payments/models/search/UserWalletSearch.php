<?php

namespace mcms\payments\models\search;

use mcms\payments\models\wallet\Wallet;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\payments\models\UserWallet;

/**
 * UserWalletSearch represents the model behind the search form about `mcms\payments\models\UserWallet`.
 */
class UserWalletSearch extends UserWallet
{
  /**
   * @var bool|null Активность ПС
   * @see Wallet::find()
   */
  public $paysystemsActivity;

  /**
   * @var int
   */
  public $is_visible = 1;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'wallet_type', 'user_id', 'is_autopayments', 'is_verified', 'is_visible'], 'integer'],
      [['currency'], 'safe'],
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
    $query = UserWallet::find(false);

    if (is_bool($this->paysystemsActivity)) {
      $paysystemsIds = Wallet::find($this->paysystemsActivity)->select('id')->column();
      $query->andWhere(['wallet_type' => $paysystemsIds]);
    }

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
      'user_id' => $this->user_id,
      'wallet_type' => $this->wallet_type,
      'currency' => $this->currency,
      'is_autopayments' => $this->is_autopayments,
      'is_deleted' => is_numeric($this->is_visible) ? !$this->is_visible : null,
      'is_verified' => $this->is_verified,
    ]);

    $query->orderBy('is_deleted, wallet_type, currency');
    return $dataProvider;
  }
}
