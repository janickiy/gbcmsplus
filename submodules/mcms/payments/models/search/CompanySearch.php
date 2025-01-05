<?php

namespace mcms\payments\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use mcms\payments\models\Company as CompanyModel;

/**
 * CompanySearch represents the model behind the search form about `mcms\payments\models\Company`.
 */
class CompanySearch extends CompanyModel
{
  public $createdFrom;
  public $createdTo;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['id', 'created_at', 'updated_at'], 'integer'],
      [['name', 'address', 'city', 'post_code', 'country', 'tax_code', 'logo', 'createdFrom', 'createdTo'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios()
  {
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
    $query = CompanyModel::find();

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
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'address', $this->address])
      ->andFilterWhere(['like', 'city', $this->city])
      ->andFilterWhere(['like', 'post_code', $this->post_code])
      ->andFilterWhere(['like', 'country', $this->country])
      ->andFilterWhere(['like', 'tax_code', $this->tax_code]);

    if ($this->createdFrom) {
      $query->andFilterWhere(['>=', self::tableName() . '.' . 'created_at', strtotime($this->createdFrom)]);
    }
    if ($this->createdTo) {
      $query->andFilterWhere(['<=', self::tableName() . '.' . 'created_at', strtotime($this->createdTo . ' tomorrow') - 1]);
    }

    return $dataProvider;
  }
}
