<?php

namespace mcms\payments\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\payments\models\PartnerCompany;

/**
 * PartnerCompanySearch represents the model behind the search form about `mcms\payments\models\PartnerCompany`.
 */
class PartnerCompanySearch extends PartnerCompany
{
  public $userId;
  public $createdFrom;
  public $createdTo;
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'created_at', 'updated_at', 'userId'], 'integer'],
      [['reseller_company_id', 'name', 'address', 'city', 'post_code', 'country', 'tax_code', 'bank_entity', 'bank_account', 'swift_code', 'currency',
        'createdFrom', 'createdTo'], 'safe'],
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
    $query = PartnerCompany::find()->
      leftJoin('user_payment_settings', 'partner_company_id = id');

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'reseller_company_id', $this->reseller_company_id])
      ->andFilterWhere(['like', 'address', $this->address])
      ->andFilterWhere(['like', 'city', $this->city])
      ->andFilterWhere(['like', 'post_code', $this->post_code])
      ->andFilterWhere(['like', 'country', $this->country])
      ->andFilterWhere(['like', 'tax_code', $this->tax_code])
      ->andFilterWhere(['like', 'bank_entity', $this->bank_entity])
      ->andFilterWhere(['like', 'bank_account', $this->bank_account])
      ->andFilterWhere(['like', 'swift_code', $this->swift_code])
      ->andFilterWhere(['like', 'currency', $this->currency])
      ->andFilterWhere(['=', 'user_payment_settings.user_id', $this->userId]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->createdTo . ' tomorrow') - 1]);
    }

    return $dataProvider;
  }
}
